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
use Flarum\User\UserRepository;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
use Illuminate\Events\Dispatcher;
use Illuminate\Support\Arr;
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
        $discussions = [];
        $mergedPosts = [];

        $command->actor->assertCan('merge', $discussion);

        $posts = $discussion->posts;

        foreach ($command->ids as $id) {
            $d = Discussion::find($id);

            if ($d == null) {
                continue;
            }

            $discussions[] = $d;

            $posts = $posts->merge(
                $mergedPosts[] = $d->posts
            );
        }

        $this->validator->assertValid([
            'posts' => Arr::flatten($mergedPosts),
        ]);

        $number = 0;

        $posts->sortBy('created_at')->each(function ($post, $i) use ($discussion, &$number) {
            $number++;

            $post->number = $number;
            $post->discussion_id = $discussion->id;

            $discussion->posts[$i] = $post;
        });

        // @see https://github.com/FriendsOfFlarum/merge-discussions/issues/5
        $discussion->setRelation('posts', $discussion->posts->sortByDesc('number'));

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
                        $d->delete();
                    }
                } catch (Throwable $e) {
                    $this->catchError($e, 'deleting');
                }
            });

            $this->events->dispatch(
                new DiscussionWasMerged($command->actor, Arr::flatten($mergedPosts), $discussion, $discussions)
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
}
