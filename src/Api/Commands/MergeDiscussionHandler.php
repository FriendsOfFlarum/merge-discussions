<?php


namespace FoF\MergeDiscussions\Api\Commands;

use Flarum\Discussion\DiscussionRepository;
use Flarum\Post\PostRepository;
use Flarum\User\AssertPermissionTrait;
use Flarum\User\UserRepository;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use Illuminate\Events\Dispatcher;

class MergeDiscussionHandler
{
    use AssertPermissionTrait;

    /**
     * @var UserRepository
     */
    protected $users;

    /**
     * @var DiscussionRepository
     */
    protected $discussions;

    /**
     * @var Dispatcher
     */
    protected $events;

    /**
     * @param UserRepository $users
     * @param DiscussionRepository $discussions
     * @param Dispatcher $events
     */
    public function __construct(
        UserRepository $users,
        DiscussionRepository $discussions,
        Dispatcher $events
    ) {
        $this->users = $users;
        $this->discussions = $discussions;
        $this->events = $events;
    }

    public function handle(MergeDiscussion $command)
    {
        $from = $this->discussions->findOrFail($command->ids[0]);
        $discussion = $this->discussions->findOrFail($command->ids[1]);
//        $title = $command->title ?? $first_discussion->title;

//        $this->assertCan($command->actor, 'merge', $first_discussion);

        $mergedPosts = $from->posts;
        $posts = $discussion->posts->merge($mergedPosts);

        $number = 0;

        $posts->each(function ($post, $i) use ($discussion, &$number) {
            $number++;

            $post->number = $number;
            $post->discussion_id = $discussion->id;

            $discussion->posts[$i] = $post;
        });

        $discussion->post_number_index = $number;

        $discussion->refreshCommentCount();
        $discussion->refreshParticipantCount();
        $discussion->refreshLastPost();

        $discussion->push();

        $from->delete();

        $this->events->dispatch(
            new DiscussionWasMerged($command->actor, $mergedPosts, $discussion, $from)
        );

        return $discussion;
    }
}