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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Collection as SupportCollection;
use Throwable;

class MergeDiscussionHandler
{
    public function __construct(protected UserRepository $users, protected DiscussionRepository $discussions, protected Dispatcher $events, protected MergeDiscussionValidator $validator)
    {
    }

    public function handle(MergeDiscussion $command): Discussion
    {
        $discussion = $this->discussions->findOrFail($command->discussionId);

        $command->actor->assertCan('merge', $discussion);

        if ($command->merge && $command->ordering === 'date') {
            $this->fixPostsNumber($discussion);
        }

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
            // To avoid integrity constraint violations, we set the number here out of the potential range to begin with
            $number = $discussion->posts->count() + $posts->count();

            $discussion = $this->setRelationsAndMergeByDate($discussion, $posts, $number);
        }

        if ($command->merge) {
            resolve('db.connection')->transaction(function () use ($discussions, $discussion, $command, $posts) {
                try {
                    // Set the relations using the bumped `number`, so we are sure we won't hit any integrity constraints
                    $discussion->push();
                } catch (Throwable $e) {
                    $this->catchError($e, 'merging step 1: '.$e->getMessage());
                }

                if ($command->ordering === 'date') {
                    try {
                        // Now we renumber again, this time starting at 0
                        $discussion = $this->setRelationsAndMergeByDate($discussion, new SupportCollection());
                        $discussion->push();
                    } catch (Throwable $e) {
                        $this->catchError($e, 'merging step 2: '.$e->getMessage());
                    }
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

    private function fixPostsNumber(Discussion $discussion): void
    {
        $posts = $discussion->posts;
        if ($posts->count() === $discussion->posts()->max('number')) {
            return;
        }

        $number = 0;

        $posts->sortBy('created_at')->each(function ($post, $i) use ($discussion, &$number) {
            /** @var Post $post */
            $number++;
            $post->number = $number;
            /** @phpstan-ignore-next-line */
            $discussion->posts[$i] = $post;
        });

        $discussion->setRelation('posts', $discussion->posts->sortBy('number'));

        resolve('db.connection')->transaction(function () use ($discussion) {
            try {
                $discussion->push();
            } catch (Throwable $e) {
                $this->catchError($e, 'fixing_posts_number: '.$e->getMessage());
            }

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
                $this->catchError($e, 'fixing_posts_number_meta: '.$e->getMessage());
            }
        });
    }

    private function setRelationsAndMergeByDate(Discussion $discussion, SupportCollection $posts, int $number = 0): Discussion
    {
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
