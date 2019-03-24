<?php


namespace FoF\MergeDiscussions\Api\Commands;


use Flarum\User\User;

class MergeDiscussion
{
    /**
     * The user performing the action.
     *
     * @var User
     */
    public $actor;

    /**
     * Discussion id to merge other discussions into
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
     * @param User $actor
     * @param $discussionId
     * @param int[] $ids
     */
    public function __construct(User $actor, $discussionId, $ids)
    {
        $this->actor = $actor;
        $this->discussionId = (int) $discussionId;
        $this->ids = $ids;
    }
}