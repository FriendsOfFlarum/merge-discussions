<?php

namespace FoF\MergeDiscussions\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Flarum\Post\Post;

trait CreatesLargeDiscussions
{
    /**
     * Create multiple discussions with a specified number of posts each.
     *
     * @param int $discussionCount The number of discussions to create
     * @param int $postsPerDiscussion The number of posts per discussion
     * @return array The created discussions
     */
    protected function createDiscussionsWithPosts(int $discussionCount, int $postsPerDiscussion): array
    {
        $discussions = [];
        for ($i = 1; $i <= $discussionCount; $i++) {
            $discussionData = [
                'title' => "Discussion $i",
                'comment_count' => $postsPerDiscussion,
                'user_id' => 2,
                'created_at' => Carbon::now()->subDays($discussionCount - $i),
            ];

            Discussion::unguard();
            $discussion = Discussion::create($discussionData);
            Discussion::reguard();
            $discussion->save();

            for ($j = 1; $j <= $postsPerDiscussion; $j++) {
                Post::unguard();
                $post = Post::create([
                    'discussion_id' => $discussion->id,
                    'user_id' => 2,
                    'type' => 'comment',
                    'content' => "Post $j in Discussion $i",
                    'created_at' => Carbon::now()->subDays($postsPerDiscussion - $j),
                ]);
                $post->save();
                Post::reguard();
            }

            $discussions[] = $discussion;
        }

        return $discussions;
    }

    protected function createPostsInDiscussion(int $discussionId, int $numberOfPosts)
    {
        $discussion = Discussion::find($discussionId);

        for ($i = 2; $i <= $numberOfPosts; $i++) {
            // Post::unguard();
            // $post = Post::create([
            //     'discussion_id' => $discussion->id,
            //     'user_id' => 2,
            //     'type' => 'comment',
            //     'content' => "Post $i in Discussion $discussionId",
            //     'created_at' => Carbon::now()->subDays($numberOfPosts - $i),
            // ]);
            // $post->save();
            // Post::reguard();
            $post = CommentPost::reply(
                $discussionId,
                "Post $i in Discussion $discussionId",
                2,
                '127.0.0.1',
            );
            $post->save();
        }

        $discussion->refreshCommentCount();
        $discussion->refreshLastPost();
        $discussion->refreshParticipantCount();
    }
}
