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
use Flarum\Foundation\KnownError;
use Flarum\Foundation\ValidationException;
use Flarum\Post\Post;
use Flarum\User\UserRepository;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Events\MergingDiscussions;
use FoF\MergeDiscussions\Models\Redirection;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Query\Grammars\PostgresGrammar;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Validation\ValidationException as IlluminateValidationException;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;
use Tobyz\JsonApiServer\Exception\ErrorProvider;

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

    public function __construct(
        protected UserRepository $users,
        protected DiscussionRepository $discussions,
        protected Dispatcher $events,
        protected MergeDiscussionValidator $validator,
        protected ConnectionInterface $db,
        protected TranslatorInterface $translator,
        protected LoggerInterface $log
    ) {
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

        // Only the ids: the posts as arrays would render every post's content again.
        $this->validator->assertValid([
            'posts' => $posts->modelKeys(),
        ]);

        if ($command->ordering === 'suffix') {
            $discussion = $this->setRelationsAndMergeAppend($discussion, $posts);
        } else {
            $discussion = $this->setRelationsAndMergeByDate($discussion, $posts);
        }

        if ($command->merge) {
            $this->db->transaction(function () use ($discussions, $discussion, $command, $posts) {
                try {
                    // Read from the posts table, so before the posts move.
                    $this->recordWhereMovedPostsCameFrom($discussions->modelKeys());

                    $this->persistPostPositions($discussion);
                } catch (Throwable $e) {
                    $this->catchError($e, 'merging');
                }

                $this->events->dispatch(
                    new MergingDiscussions($command->actor, $posts, $discussion, $discussions)
                );

                try {
                    $mergedPosts = $discussion->posts;

                    /** @var Post $firstPost */
                    $firstPost = $mergedPosts->first();

                    // refresh() reloads every loaded relation, which would read a
                    // second copy of every post. The posts are set back afterwards
                    // from memory, so listeners still get them.
                    $discussion
                        ->unsetRelation('posts')
                        ->refresh()
                        ->refreshCommentCount()
                        ->refreshParticipantCount()
                        ->refreshLastPost()
                        ->setFirstPost($firstPost)
                        ->save();

                    $discussion->setRelation('posts', $mergedPosts);
                } catch (Throwable $e) {
                    $this->catchError($e, 'updating');
                }

                foreach ($discussions as $d) {
                    /** @var Discussion $d */
                    try {
                        Redirection::build($d, $discussion);
                    } catch (Throwable $e) {
                        $this->catchError($e, 'redirecting');
                    }

                    try {
                        // Announce it as core's delete does, so extensions clean up
                        // what they keep for the discussion.
                        $this->events->dispatch(new DiscussionDeleting($d, $command->actor, []));

                        $d->delete();
                    } catch (Throwable $e) {
                        $this->catchError($e, 'deleting');
                    }
                }
            });

            $this->events->dispatch(
                new DiscussionWasMerged($command->actor, $posts, $discussion, $discussions)
            );
        }

        return $discussion;
    }

    /**
     * Pass on a refusal the client should see as it is, such as another
     * extension's validation error or a permission refusal. Log anything else
     * in full, and tell the client only which step failed.
     *
     * @param 'merging'|'updating'|'redirecting'|'deleting' $type
     */
    private function catchError(Throwable $e, string $type): never
    {
        if ($e instanceof KnownError || $e instanceof ValidationException || $e instanceof IlluminateValidationException || $e instanceof ErrorProvider) {
            throw $e;
        }

        $this->log->error("[fof/merge-discussions] A merge failed while {$type}.");
        $this->log->error($e);

        throw new ValidationException([
            'fof/merge-discussions' => $this->translator->trans("fof-merge-discussions.api.error.{$type}_failed"),
        ]);
    }

    /**
     * Write the in-memory `discussion_id` / `number` of every moved post with
     * a fixed number of queries, rather than one UPDATE per post.
     */
    private function persistPostPositions(Discussion $discussion): void
    {
        /** @var Collection<int, Post> $moved */
        $moved = $discussion->posts->filter(fn ($post) => $post->isDirty(['discussion_id', 'number']));

        if ($moved->isEmpty()) {
            return;
        }

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
     * @param int[] $mergedIds the discussions being merged away
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

        $posts = $this->db->table('posts');
        $grammar = $posts->getGrammar();

        // The recording time is bound in the select list. A bound value there has
        // no type, and PostgreSQL will not put text into a timestamp column, so it
        // is cast there. Copying a post's own date instead would fail on MySQL in
        // strict mode for any post dated outside TIMESTAMP's 1970-2038 range.
        $recordedAt = $grammar instanceof PostgresGrammar ? 'CAST(? AS TIMESTAMP)' : '?';

        $this->db->table('fof_merged_posts')->insertUsing(
            ['post_id', 'from_discussion_id', 'from_number', 'created_at'],
            $posts
                ->select(['id', 'discussion_id', 'number'])
                ->selectRaw($recordedAt, [Carbon::now()->format($grammar->getDateFormat())])
                ->whereIntegerInRaw('discussion_id', $mergedIds)
                ->whereNotIn('type', static::UNMERGED_POST_TYPES)
                ->whereNotNull('number')
        );
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
