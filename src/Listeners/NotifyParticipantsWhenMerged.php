<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Listeners;

use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Jobs;
use Illuminate\Support\Collection;

class NotifyParticipantsWhenMerged
{
    public function handle(DiscussionWasMerged $event): void
    {
        $mergedDiscussions = new Collection();

        foreach ($event->mergedDiscussions as $mergedDiscussion) {
            $mergedDiscussions->push([
                'id'      => $mergedDiscussion->id,
                'title'   => $mergedDiscussion->title,
                'user_id' => $mergedDiscussion->user_id,
            ]);
        }

        resolve('flarum.queue.connection')->push(
            new Jobs\SendNotificationWhenDiscussionIsMerged($event->discussion, $mergedDiscussions, $event->actor)
        );
    }
}
