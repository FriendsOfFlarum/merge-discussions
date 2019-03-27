<?php


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