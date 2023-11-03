<?php

namespace FoF\MergeDiscussions\Jobs;

use Flarum\Queue\AbstractJob;
use Flarum\User\User;
use FoF\MergeDiscussions\Commands\MergeDiscussions;
use Illuminate\Bus\Dispatcher;

class MergeDiscussionsJob extends AbstractJob
{
    protected $actor;

    protected $discussionId;

    protected $ids;

    protected $ordering;

    protected $merge;
    
    public function __construct(User $actor, int $discussionId, array $ids, string $ordering = 'date', bool $merge = true)
    {
        $this->actor = $actor;
        $this->discussionId = (int) $discussionId;
        $this->ids = $ids;
        $this->ordering = $ordering;
        $this->merge = $merge;
    }
    
    public function handle(Dispatcher $bus)
    {
        $bus->dispatch(new MergeDiscussions($this->actor, $this->discussionId, $this->ids, $this->ordering, $this->merge));
    }
}
