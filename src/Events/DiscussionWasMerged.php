<?php


namespace FoF\MergeDiscussions\Events;


use Flarum\Discussion\Discussion;
use Flarum\User\User;
use Illuminate\Database\Eloquent\Collection;

class DiscussionWasMerged
{
    /**
     * @var User
     */
    public $actor;

    /**
     * @var Collection
     */
    public $posts;

    /**
     * @var Discussion
     */
    public $discussion;

    /**
     * @var Discussion
     */
    public $mergedDiscussion;

    public function __construct(User $actor, $posts, $discussion, $mergedDiscussion)
    {
        $this->actor = $actor;
        $this->posts = $posts;
        $this->discussion = $discussion;
        $this->mergedDiscussion = $mergedDiscussion;
    }
}