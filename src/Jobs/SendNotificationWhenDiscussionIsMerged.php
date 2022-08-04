<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Jobs;

use Flarum\Discussion\Discussion;
use Flarum\Notification\NotificationSyncer;
use Flarum\User\User;
use FoF\MergeDiscussions\Notification\DiscussionMergedBlueprint;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SendNotificationWhenDiscussionIsMerged implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    /**
     * @var Discussion
     */
    protected $discussion;

    /**
     * @var User
     */
    protected $actor;

    /**
     * @var Collection
     */
    protected $mergedDiscussions;

    public function __construct(Discussion $discussion, Collection $mergedDiscussions, User $actor)
    {
        $this->discussion = $discussion;
        $this->mergedDiscussions = $mergedDiscussions;
        $this->actor = $actor;
    }

    public function handle(NotificationSyncer $notifications): void
    {
        /** @var \Psr\Log\LoggerInterface $log */
        $log = resolve('log');
        $log->info("Notifying that discussion {$this->discussion->id} has recieved a merge from ".$this->mergedDiscussions->pluck('id')->implode(',')." - triggered by {$this->actor->id}");

        foreach ($this->mergedDiscussions as $mergedDiscussion) {
            /** @var array $mergedDiscussion */
            $user = User::find($mergedDiscussion['user_id']);

            $log->info("Notifying {$user->username} about merged discussion {$mergedDiscussion['id']}");

            if ($user) {
                $notifications->sync(new DiscussionMergedBlueprint($this->discussion, $this->actor, $mergedDiscussion), [$user]);
            }
        }
    }
}
