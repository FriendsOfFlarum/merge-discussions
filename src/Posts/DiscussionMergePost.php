<?php

namespace FoF\MergeDiscussions\Posts;

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
     * @return Post The model resulting after the merge. If the merge is
     *     unsuccessful, this should be the current model instance. Otherwise,
     *     it should be the model that was merged into.
     */
    public function saveAfter(Post $previous = null)
    {
        $this->save();

        return $this;
    }

    /**
     * Create a new instance in reply to a discussion.
     *
     * @param int $discussionId
     * @param int $userId
     * @param int $postsCount
     * @param string $oldTitle
     * @return static
     */
    public static function reply($discussionId, $userId, $postsCount, $oldTitle)
    {
        $post = new static;

        $post->content = static::buildContent($postsCount, $oldTitle);
        $post->created_at = time();
        $post->discussion_id = $discussionId;
        $post->user_id = $userId;

        return $post;
    }

    /**
     * Build the content attribute.
     *
     * @param int $postsCount Number of posts merged
     * @param string $oldTitle Merged discussion title
     * @return array
     */
    public static function buildContent($postsCount, $oldTitle)
    {
        return ['count' => (int) $postsCount, 'title' => $oldTitle];
    }
}