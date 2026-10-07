<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Commands;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Discussion\Event\Deleting as DiscussionDeleting;
use Flarum\Foundation\ValidationException;
use Flarum\Post\Post;
use Flarum\User\UserRepository;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Events\MergingDiscussions;
use FoF\MergeDiscussions\Models\Redirection;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Throwable;

class MergeDiscussionHandler
{
    /**
     * Posts written per statement. Keeps each statement a bounded size however
     * long the merged discussion is.
     */
    protected const RENUMBER_CHUNK_SIZE = 500;

    /**
     * Post types a merge leaves behind in the merged-away discussion.
     */
    protected const UNMERGED_POST_TYPES = ['discussionTagged'];

    public function __construct(protected UserRepository $users, protected DiscussionRepository $discussions, protected Dispatcher $events, protected MergeDiscussionValidator $validator, protected ConnectionInterface $db)
    {
    }

    public function handle(MergeDiscussion $command): Discussion
    {
        $discussion = $this->discussions->findOrFail($command->discussionId, $command->actor);

        $command->actor->assertCan('merge', $discussion);

        $ids = array_unique(Arr::wrap($command->ids));

        if (in_array($discussion->id, array_map('intval', $ids), true)) {
            throw new ValidationException([
                'merging_discussions' => MergeDiscussionValidator::INTO_ITSELF,
            ]);
        }

        /** @var Collection $discussions */
        $discussions = Discussion::whereVisibleTo($command->actor)
            ->findMany($ids);

        // A discussion the actor cannot see is refused as if it did not exist,
        // so the response gives nothing away about it.
        if ($discussions->count() !== count($ids)) {
            throw new ValidationException([
                'merging_discussions' => MergeDiscussionValidator::MISSING_DISCUSSIONS,
            ]);
        }

        // Being allowed to merge into the target says nothing about the
        // discussions merged into it, e.g. when the permission is scoped by tag.
        foreach ($discussions as $source) {
            $command->actor->assertCan('merge', $source);
        }

        // Load all posts for these discussions, bypassing visibility scopes
        // We need all posts (including hidden ones) for the merge
        /** @var Collection $posts */
        $posts = Post::query()
            ->whereIn('discussion_id', $discussions->pluck('id'))
            ->withoutGlobalScopes()
            ->get()
            ->reject(function (Post $post) {
                return in_array($post->type, static::UNMERGED_POST_TYPES, true);
            });

        $this->validator->assertValid([
            'posts' => $posts->toArray(),
        ]);

        if ($command->ordering === 'suffix') {
            $discussion = $this->setRelationsAndMergeAppend($discussion, $posts);
        } else {
            $discussion = $this->setRelationsAndMergeByDate($discussion, $posts);
        }

        if ($command->merge) {
            $this->db->transaction(function () use ($discussions, $discussion, $command, $posts) {
                try {
                    $this->persistPostPositions($discussion, $discussions->modelKeys());
                } catch (Throwable $e) {
                    $this->catchError($e, 'merging');
                }

                $this->events->dispatch(
                    new MergingDiscussions($command->actor, $posts, $discussion, $discussions)
                );

                try {
                    /** @var Post $firstPost */
                    $firstPost = $discussion->posts->first();

                    // refresh() reloads every loaded relation: without this, a second
                    // copy of every post in the merged discussion.
                    $discussion
                        ->unsetRelation('posts')
                        ->refresh()
                        ->refreshCommentCount()
                        ->refreshParticipantCount()
                        ->refreshLastPost()
                        ->setFirstPost($firstPost)
                        ->save();
                } catch (Throwable $e) {
                    $this->catchError($e, 'updating');
                }

                try {
                    foreach ($discussions as $d) {
                        /** @var Discussion $d */
                        Redirection::build($d, $discussion);

                        // Announce it as core's delete does, so extensions clean up
                        // what they keep for the discussion.
                        $this->events->dispatch(new DiscussionDeleting($d, $command->actor, []));

                        $d->delete();
                    }
                } catch (Throwable $e) {
                    $this->catchError($e, 'deleting');
                }
            });

            $this->events->dispatch(
                new DiscussionWasMerged($command->actor, $posts, $discussion, $discussions)
            );
        }

        return $discussion;
    }

    /**
     * Log the failure in full, and tell the client only which step failed.
     *
     * @param 'merging'|'updating'|'deleting' $type
     */
    private function catchError(Throwable $e, string $type): never
    {
        $msg = resolve('translator')->trans("fof-merge-discussions.api.error.{$type}_failed");

        resolve('log')->error("[fof/merge-discussions] $msg");
        resolve('log')->error($e);

        throw new ValidationException([
            'fof/merge-discussions' => $msg,
        ]);
    }

    /**
     * Write the in-memory `discussion_id` / `number` of every moved post with
     * a fixed number of queries, rather than one UPDATE per post.
     *
     * @param int[] $mergedIds the discussions being merged into $discussion
     */
    private function persistPostPositions(Discussion $discussion, array $mergedIds): void
    {
        /** @var Collection<int, Post> $moved */
        $moved = $discussion->posts->filter(fn ($post) => $post->isDirty(['discussion_id', 'number']));

        if ($moved->isEmpty()) {
            return;
        }

        $this->recordWhereMovedPostsCameFrom($mergedIds);

        $chunks = $moved->chunk(static::RENUMBER_CHUNK_SIZE);

        // Park every moved post on a NULL number before numbering any of them.
        // unique(discussion_id, number) admits any number of NULLs on MySQL, MariaDB,
        // PostgreSQL and SQLite, so the final numbers below cannot collide whatever
        // order rows are written in.
        foreach ($chunks as $chunk) {
            $this->db->table('posts')
                ->whereIntegerInRaw('id', $chunk->modelKeys())
                ->update(['discussion_id' => $discussion->id, 'number' => null]);
        }

        foreach ($chunks as $chunk) {
            // Ids and numbers are integers, so they are inlined: update() cannot bind inside an expression.
            $cases = $chunk
                ->map(fn (Post $post) => sprintf('WHEN %d THEN %d', $post->id, $post->number))
                ->implode(' ');

            $this->db->table('posts')
                ->whereIntegerInRaw('id', $chunk->modelKeys())
                ->update(['number' => $this->db->raw("CASE id $cases END")]);
        }

        $moved->each->syncOriginal();
    }

    /**
     * Record the old discussion and number of each post taken from another
     * discussion, so links to it can follow it once that discussion is gone.
     *
     * Runs before the posts move, copying their positions straight from the
     * posts table: a fixed number of statements however many posts there are.
     *
     * @param int[] $mergedIds
     */
    private function recordWhereMovedPostsCameFrom(array $mergedIds): void
    {
        if (!$mergedIds) {
            return;
        }

        // A merged-away discussion's id can come back (MySQL 5.7 reuses the
        // highest id after a restart). Rows left by the earlier discussion would
        // collide, and its links now mean this one's posts anyway.
        $this->db->table('fof_merged_posts')
            ->whereIntegerInRaw('from_discussion_id', $mergedIds)
            ->delete();

        // The posts' own created_at stands in for the recording time, which is
        // set just below: a bound value in the select list has no type, and
        // PostgreSQL will not put text into a timestamp column.
        $this->db->table('fof_merged_posts')->insertUsing(
            ['post_id', 'from_discussion_id', 'from_number', 'created_at'],
            $this->db->table('posts')
                ->select(['id', 'discussion_id', 'number', 'created_at'])
                ->whereIntegerInRaw('discussion_id', $mergedIds)
                ->whereNotIn('type', static::UNMERGED_POST_TYPES)
                ->whereNotNull('number')
        );

        $this->db->table('fof_merged_posts')
            ->whereIntegerInRaw('from_discussion_id', $mergedIds)
            ->update(['created_at' => Carbon::now()]);
    }

    private function setRelationsAndMergeByDate(Discussion $discussion, SupportCollection $posts): Discussion
    {
        $number = 0;

        $discussion->setRelation(
            'posts',
            $discussion
                ->posts
                ->merge($posts)
                ->sortBy('created_at')
                /** @phpstan-ignore-next-line */
                ->map(function (Post $post) use (&$number, $discussion) {
                    $number++;

                    $post->number = $number;
                    $post->discussion_id = $discussion->id;

                    return $post;
                })
        );

        return $discussion;
    }

    private function setRelationsAndMergeAppend(Discussion $discussion, SupportCollection $posts): Discussion
    {
        $number = $discussion->posts()->max('number');

        $posts = $posts
            ->sortBy('created_at')
            ->map(function (Post $post) use (&$number, $discussion) {
                $number++;

                $post->number = $number;
                $post->discussion_id = $discussion->id;

                return $post;
            });

        $discussion->setRelation(
            'posts',
            $discussion
                ->posts
                ->merge($posts)
                ->sortBy('number')
        );

        return $discussion;
    }
}
