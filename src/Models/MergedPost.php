<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Models;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Post\Post;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A post a merge moved out of a discussion, with the number it had there, so
 * links to it can follow it once that discussion is gone.
 *
 * @property int       $id
 * @property int       $post_id
 * @property int       $from_discussion_id
 * @property int       $from_number
 * @property Carbon    $created_at
 * @property Post|null $post
 */
class MergedPost extends AbstractModel
{
    protected $table = 'fof_merged_posts';

    public static function at(int $discussionId, int $number): ?self
    {
        return self::query()
            ->where('from_discussion_id', $discussionId)
            ->where('from_number', $number)
            ->first();
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
