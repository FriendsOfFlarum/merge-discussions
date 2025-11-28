<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Events;

use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\User\User;
use Illuminate\Support\Collection;

class MergingDiscussions
{
    public function __construct(public User $actor, public Collection $posts, public Discussion $discussion, public Collection $mergedDiscussions)
    {
    }
}
