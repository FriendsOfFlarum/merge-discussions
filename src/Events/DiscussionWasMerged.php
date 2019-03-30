<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Events;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\User\User;

class DiscussionWasMerged
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var Post[]
     */
    public $posts;

    /**
     * @var Discussion
     */
    public $discussion;

    /**
     * @var Discussion[] Discussion
     */
    public $mergedDiscussions;

    public function __construct(User $actor, $posts, $discussion, $mergedDiscussions)
    {
        $this->actor = $actor;
        $this->posts = $posts;
        $this->discussion = $discussion;
        $this->mergedDiscussions = $mergedDiscussions;
    }
}
