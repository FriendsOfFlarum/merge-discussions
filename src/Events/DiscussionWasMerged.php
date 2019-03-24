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