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

use Flarum\User\User;
use Illuminate\Support\Arr;

class MergeDiscussion
{
    /**
     * Discussion id to merge other discussions into.
     *
     * @var int
     */
    public $discussionId;

    /**
     * The discussion ids to merge.
     *
     * @var int[]
     */
    public $ids;

    /**
     * MergeDiscussion constructor.
     *
     * @param int[] $ids
     */
    public function __construct(public User $actor, $discussionId, $ids, public $ordering = 'date', public $merge = true)
    {
        $this->discussionId = (int) $discussionId;
        $this->ids = Arr::wrap($ids);
    }
}
