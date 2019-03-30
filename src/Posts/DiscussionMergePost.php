<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Posts;

use Flarum\Discussion\Discussion;
use Flarum\Post\AbstractEventPost;
use Flarum\Post\MergeableInterface;
use Flarum\Post\Post;

class DiscussionMergePost extends AbstractEventPost implements MergeableInterface
{
    /**
     * {@inheritdoc}
     */
    public static $type = 'discussionMerged';

    /**
     * Save the model, given that it is going to appear immediately after the
     * passed model.
     *
     * @param Post|null $previous
     *
     * @return Post The model resulting after the merge. If the merge is
     *              unsuccessful, this should be the current model instance. Otherwise,
     *              it should be the model that was merged into.
     */
    public function saveAfter(Post $previous = null)
    {
        $this->save();

        return $this;
    }

    /**
     * Create a new instance in reply to a discussion.
     *
     * @param int          $discussionId
     * @param int          $userId
     * @param int          $postsCount
     * @param Discussion[] $mergedDiscussions
     *
     * @return static
     */
    public static function reply($discussionId, $userId, $postsCount, $mergedDiscussions)
    {
        $post = new static();

        $post->content = static::buildContent($postsCount, $mergedDiscussions);
        $post->created_at = time();
        $post->discussion_id = $discussionId;
        $post->user_id = $userId;

        return $post;
    }

    /**
     * Build the content attribute.
     *
     * @param int          $postsCount  Number of posts merged
     * @param Discussion[] $discussions Merged discussions
     *
     * @return array
     */
    public static function buildContent($postsCount, $discussions)
    {
        return [
            'count'  => (int) $postsCount,
            'titles' => collect($discussions)->map(function ($d) {
                return $d->title;
            }),
        ];
    }
}
