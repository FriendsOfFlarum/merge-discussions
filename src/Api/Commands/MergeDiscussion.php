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
     * @var string
     */
    public $title;

    /**
     * The discussion ids to merge.
     *
     * @var int[]
     */
    public $ids;

    /**
     * MergeDiscussion constructor.
     * @param User $actor
     * @param string $title
     * @param int[] $ids
     */
    public function __construct(User $actor, $title, $ids)
    {
        $this->actor = $actor;
        $this->title = $title;
        $this->ids = $ids;
    }
}