<?php


namespace FoF\MergeDiscussions\Listeners;


use Flarum\Event\ConfigurePostTypes;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Posts\DiscussionMergePost;
use Illuminate\Contracts\Events\Dispatcher;

class CreatePostWhenMerged
{
    /**
     * @param Dispatcher $events
     */
    public function subscribe(Dispatcher $events)
    {
        $events->listen(ConfigurePostTypes::class, [$this, 'addPostType']);
        $events->listen(DiscussionWasMerged::class, [$this, 'whenDiscussionWasTagged']);
    }

    /**
     * @param ConfigurePostTypes $event
     */
    public function addPostType(ConfigurePostTypes $event)
    {
        $event->add(DiscussionMergePost::class);
    }

    public function whenDiscussionWasTagged(DiscussionWasMerged $event) {
        $post = DiscussionMergePost::reply(
            $event->discussion->id,
            $event->actor->id,
            $event->posts->count(),
            $event->mergedDiscussion->title
        );

        $event->discussion->mergePost($post);
    }
}