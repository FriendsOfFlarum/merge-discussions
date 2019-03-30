<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Listeners;

use Flarum\Event\ConfigurePostTypes;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Posts\DiscussionMergePost;
use Illuminate\Contracts\Events\Dispatcher;

class CreatePostWhenMerged
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigurePostTypes::class, [$this, 'addPostType']);
        $events->listen(DiscussionWasMerged::class, [$this, 'whenDiscussionWasTagged']);
    }

    /**
     * @param ConfigurePostTypes $event
     */
    public function addPostType(ConfigurePostTypes $event)
    {
        $event->add(DiscussionMergePost::class);
    }

    public function whenDiscussionWasTagged(DiscussionWasMerged $event)
    {
        $post = DiscussionMergePost::reply(
            $event->discussion->id,
            $event->actor->id,
            count($event->posts),
            $event->mergedDiscussions
        );

        $event->discussion->mergePost($post);
    }
}
