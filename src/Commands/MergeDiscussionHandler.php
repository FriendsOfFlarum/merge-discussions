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

use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
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
use Illuminate\Support\Collection as SupportCollection;
use Throwable;

class MergeDiscussionHandler
{
    /**
     * Posts written per UPDATE statement. Keeps each statement a bounded size
     * however long the merged discussion is.
     */
    protected const RENUMBER_CHUNK_SIZE = 500;

    public function __construct(protected UserRepository $users, protected DiscussionRepository $discussions, protected Dispatcher $events, protected MergeDiscussionValidator $validator, protected ConnectionInterface $db)
    {
    }

    public function handle(MergeDiscussion $command): Discussion
    {
        $discussion = $this->discussions->findOrFail($command->discussionId);

        $command->actor->assertCan('merge', $discussion);

        /** @var Collection $discussions */
        $discussions = Discussion::query()
            ->findMany($command->ids);

        // Load all posts for these discussions, bypassing visibility scopes
        // We need all posts (including hidden ones) for the merge
        /** @var Collection $posts */
        $posts = Post::query()
            ->whereIn('discussion_id', $discussions->pluck('id'))
            ->withoutGlobalScopes()
            ->get()
            ->reject(function (Post $post) {
                return $post->type === 'discussionTagged';
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
                    $this->persistPostPositions($discussion);
                } catch (Throwable $e) {
                    $this->catchError($e, 'merging');
                }

                $this->events->dispatch(
                    new MergingDiscussions($command->actor, $posts, $discussion, $discussions)
                );

                try {
                    /** @var Post $firstPost */
                    $firstPost = $discussion->posts->first();

                    $discussion
                        ->refresh()
                        ->refreshCommentCount()
                        ->refreshParticipantCount()
                        ->refreshLastPost()
                        ->setFirstPost($firstPost)
                        ->save();
                } catch (Throwable $e) {
                    $this->catchError($e, 'updating: '.$e->getMessage());
                }

                try {
                    foreach ($discussions as $d) {
                        /** @var Discussion $d */
                        Redirection::build($d, $discussion);

                        $d->delete();
                    }
                } catch (Throwable $e) {
                    $this->catchError($e, 'redirection + deleting: '.$e->getMessage());
                }
            });

            $this->events->dispatch(
                new DiscussionWasMerged($command->actor, $posts, $discussion, $discussions)
            );
        }

        return $discussion;
    }

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
