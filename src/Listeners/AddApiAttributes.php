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

use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\DiscussionSerializer;

class AddApiAttributes
{
    /**
     * @param Serializing $event
     */
    public function handle(Serializing $event)
    {
        if ($event->isSerializer(DiscussionSerializer::class)) {
            $event->attributes['canMerge'] = $event->actor->can('merge', $event->model);
        }
    }
}
