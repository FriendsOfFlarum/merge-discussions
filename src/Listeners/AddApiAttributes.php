<?php


namespace FoF\MergeDiscussions\Listeners;


use Flarum\Api\Event\Serializing;
use Flarum\Api\Serializer\DiscussionSerializer;

class AddApiAttributes
{
    /**
     * @param Serializing $event
     */
    public function handle(Serializing $event)
    {
        if ($event->isSerializer(DiscussionSerializer::class)) {
            $event->attributes['canMerge'] = $event->actor->can('merge', $event->model);
        }
    }
}