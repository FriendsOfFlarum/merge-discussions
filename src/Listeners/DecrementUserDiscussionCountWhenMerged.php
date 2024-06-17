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
use FoF\MergeDiscussions\Posts\DiscussionMergePost;

class DecrementUserDiscussionCountWhenMerged
{
    public function handle(DiscussionWasMerged $event)
    {
        $event->actor->decrement('discussion_count');
    }
}
