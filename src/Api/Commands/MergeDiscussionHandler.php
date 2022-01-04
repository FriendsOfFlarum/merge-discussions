<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Api\Commands;

use Flarum\Discussion\Discussion;
use Flarum\Discussion\DiscussionRepository;
use Flarum\Foundation\ValidationException;
use Flarum\Post\Post;
use Flarum\User\UserRepository;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Models\Redirection;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
use Illuminate\Events\Dispatcher;
use Throwable;

class MergeDiscussionHandler
{
    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @var DiscussionRepository
     */
    protected $discussions;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @var MergeDiscussionValidator
     */
    protected $validator;

    public function __construct(
        UserRepository $users,
        DiscussionRepository $discussions,
        Dispatcher $events,
        MergeDiscussionValidator $validator
    ) {
        $this->users = $users;
        $this->discussions = $discussions;
        $this->events = $events;
        $this->validator = $validator;
    }

    public function handle(MergeDiscussion $command)
    {
        $discussion = $this->discussions->findOrFail($command->discussionId);

        $command->actor->assertCan('merge', $discussion);

        if ($command->merge) {
            $this->fixPostsNumber($discussion);
        }

        $discussions = Discussion::query()
            ->with('posts')
            ->findMany($command->ids);

        $posts = $discussions->pluck('posts')->flatten(1);

        $this->validator->assertValid([
            'posts' => $posts->toArray(),
        ]);

        $number = 0;

        $discussion->setRelation(
            'posts',
            $discussion
                ->posts
                ->merge($posts)
                ->sortBy('created_at')
                ->map(function (Post $post) use (&$number, $discussion) {
                    $number++;

                    $post->number = $number;
                    $post->discussion_id = $discussion->id;

                    return $post;
                })
        );

        $discussion->post_number_index = $number;

        if ($command->merge) {
            resolve('db.connection')->transaction(function () use ($discussions, $discussion) {
                try {
                    $discussion->push();
                } catch (Throwable $e) {
                    $this->catchError($e, 'merging');
                }

                try {
                    $discussion
                        ->refresh()
                        ->refreshCommentCount()
                        ->refreshParticipantCount()
                        ->refreshLastPost()
                        ->setFirstPost($discussion->posts->first())
                        ->save();
                } catch (Throwable $e) {
                    $this->catchError($e, 'updating');
                }

                try {
                    foreach ($discussions as $d) {
                        Redirection::build($d, $discussion);

                        $d->delete();
                    }
                } catch (Throwable $e) {
                    $this->catchError($e, 'redirection + deleting');
                }
            });

            $this->events->dispatch(
                new DiscussionWasMerged($command->actor, $posts, $discussion, $discussions)
            );
        }

        return $discussion;
    }

    private function catchError(Throwable $e, string $type)
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
        if ($posts->count() === $discussion->post_number_index) {
            return;
        }

        $number = 0;

        $posts->sortBy('created_at')->each(function ($post, $i) use ($discussion, &$number) {
            $number++;
            $post->number = $number;
            $discussion->posts[$i] = $post;
        });

        $discussion->setRelation('posts', $discussion->posts->sortBy('number'));
        $discussion->post_number_index = $number;

        resolve('db.connection')->transaction(function () use ($discussion) {
            try {
                $discussion->push();
            } catch (Throwable $e) {
                $this->catchError($e, 'fixing_posts_number');
            }

            try {
                $discussion
                    ->refresh()
                    ->refreshCommentCount()
                    ->refreshParticipantCount()
                    ->refreshLastPost()
                    ->setFirstPost($discussion->posts->first())
                    ->save();
            } catch (Throwable $e) {
                $this->catchError($e, 'fixing_posts_number');
            }
        });
    }
}
