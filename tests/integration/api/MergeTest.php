<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MergeTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-merge-discussions');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => 3, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'is_email_confirmed' => true],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Discussion 1', 'comment_count' => 5, 'user_id' => 1, 'created_at' => Carbon::now()->subDays(5), 'first_post_id' => 1],
                ['id' => 2, 'title' => 'Discussion 2', 'comment_count' => 5, 'user_id' => 2, 'created_at' => Carbon::now()->subDays(4), 'first_post_id' => 2],
                ['id' => 3, 'title' => 'Discussion 3', 'comment_count' => 5, 'user_id' => 2, 'created_at' => Carbon::now()->subDays(3), 'first_post_id' => 3],
                ['id' => 4, 'title' => 'Discussion 4', 'comment_count' => 5, 'user_id' => 3, 'created_at' => Carbon::now()->subDays(2), 'first_post_id' => 4],
            ],
            Post::class => [
                // Existing first posts for each discussion, spaced 4 hours apart
                ['id' => 1, 'user_id' => 1, 'type' => 'comment', 'content' => 'Post 1 in Discussion 1', 'discussion_id' => 1, 'number' => 1, 'created_at' => Carbon::now()->subDays(5)->subHours(4)],
                ['id' => 2, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 1 in Discussion 2', 'discussion_id' => 2, 'number' => 1, 'created_at' => Carbon::now()->subDays(4)->subHours(4)],
                ['id' => 3, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 1 in Discussion 3', 'discussion_id' => 3, 'number' => 1, 'created_at' => Carbon::now()->subDays(3)->subHours(4)],
                ['id' => 4, 'user_id' => 3, 'type' => 'comment', 'content' => 'Post 1 in Discussion 4', 'discussion_id' => 4, 'number' => 1, 'created_at' => Carbon::now()->subDays(2)->subHours(4)],
                // Additional posts for Discussion 1, interleaved with Discussion 2
                ['id' => 5, 'user_id' => 1, 'type' => 'comment', 'content' => 'Post 2 in Discussion 1', 'discussion_id' => 1, 'number' => 2, 'created_at' => Carbon::now()->subDays(4)->subHours(3)],
                ['id' => 6, 'user_id' => 1, 'type' => 'comment', 'content' => 'Post 3 in Discussion 1', 'discussion_id' => 1, 'number' => 3, 'created_at' => Carbon::now()->subDays(3)->subHours(2)],
                ['id' => 7, 'user_id' => 1, 'type' => 'comment', 'content' => 'Post 4 in Discussion 1', 'discussion_id' => 1, 'number' => 4, 'created_at' => Carbon::now()->subDays(2)->subHour()],
                ['id' => 8, 'user_id' => 1, 'type' => 'comment', 'content' => 'Post 5 in Discussion 1', 'discussion_id' => 1, 'number' => 5, 'created_at' => Carbon::now()->subDays(1)],
                // Additional posts for Discussion 2, interleaved with Discussion 1 and 3
                ['id' => 9, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 2 in Discussion 2', 'discussion_id' => 2, 'number' => 2, 'created_at' => Carbon::now()->subDays(4)->subHours(2)],
                ['id' => 10, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 3 in Discussion 2', 'discussion_id' => 2, 'number' => 3, 'created_at' => Carbon::now()->subDays(3)->subHour()],
                ['id' => 11, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 4 in Discussion 2', 'discussion_id' => 2, 'number' => 4, 'created_at' => Carbon::now()->subDays(2)],
                ['id' => 12, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 5 in Discussion 2', 'discussion_id' => 2, 'number' => 5, 'created_at' => Carbon::now()->subDays(1)->subHours(3)],
                // Additional posts for Discussion 3, interleaved with Discussion 2 and 4
                ['id' => 13, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 2 in Discussion 3', 'discussion_id' => 3, 'number' => 2, 'created_at' => Carbon::now()->subDays(3)->subHours(3)],
                ['id' => 14, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 3 in Discussion 3', 'discussion_id' => 3, 'number' => 3, 'created_at' => Carbon::now()->subDays(2)->subHours(2)],
                ['id' => 15, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 4 in Discussion 3', 'discussion_id' => 3, 'number' => 4, 'created_at' => Carbon::now()->subDay()->subHours(1)],
                ['id' => 16, 'user_id' => 2, 'type' => 'comment', 'content' => 'Post 5 in Discussion 3', 'discussion_id' => 3, 'number' => 5, 'created_at' => Carbon::now()],
                // Additional posts for Discussion 4, interleaved with Discussion 3
                ['id' => 17, 'user_id' => 3, 'type' => 'comment', 'content' => 'Post 2 in Discussion 4', 'discussion_id' => 4, 'number' => 2, 'created_at' => Carbon::now()->subDays(2)->subHours(3)],
                ['id' => 18, 'user_id' => 3, 'type' => 'comment', 'content' => 'Post 3 in Discussion 4', 'discussion_id' => 4, 'number' => 3, 'created_at' => Carbon::now()->subDay()->subHours(2)],
                ['id' => 19, 'user_id' => 3, 'type' => 'comment', 'content' => 'Post 4 in Discussion 4', 'discussion_id' => 4, 'number' => 4, 'created_at' => Carbon::now()->subHours(1)],
                ['id' => 20, 'user_id' => 3, 'type' => 'comment', 'content' => 'Post 5 in Discussion 4', 'discussion_id' => 4, 'number' => 5, 'created_at' => Carbon::now()->subHour()],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => 4],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'discussion.merge'],
            ],
        ]);
    }

    #[Test]
    public function cannot_merge_discussions_without_data()
    {
        $response = $this->send(
            $this->request('POST', '/api/discussions/1/merge', [
                'json'            => [],
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertCount(1, $data['errors']);
        $this->assertEquals('/data/attributes/merging_discussions', $data['errors'][0]['source']['pointer']);
    }

    #[Test]
    public function cannot_preview_discussion_merge_without_data()
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions/1/merge-preview', [
                'authenticatedAs' => 1,
            ])
        );

        $this->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('errors', $data);
        $this->assertCount(1, $data['errors']);
    }

    #[Test]
    public function can_preview_discussion_merge_by_date()
    {
        // Use parameter names with uppercase letters to bypass JSON:API validation
        $response = $this->send(
            $this->request('GET', '/api/discussions/1/merge-preview', [
                'authenticatedAs' => 3,
            ])->withQueryParams([
                'byIds'      => '2',
                'byOrdering' => 'date',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('1', $data['data']['id']);
        $this->assertEquals('discussions', $data['data']['type']);

        // Verify the posts relationship is present (needed for frontend preview)
        $this->assertArrayHasKey('relationships', $data['data']);
        $this->assertArrayHasKey('posts', $data['data']['relationships']);
        $this->assertArrayHasKey('data', $data['data']['relationships']['posts']);

        // The frontend needs the post IDs to fetch and display them
        $this->assertIsArray($data['data']['relationships']['posts']['data']);
        // Preview now shows all posts from both discussions (merged preview)
        // Discussion 1 has 5 posts, Discussion 2 has 5 posts = 10 total
        $this->assertEquals(10, count($data['data']['relationships']['posts']['data']));
    }

    #[Test]
    public function unauthorized_user_cannot_merge_discussions()
    {
        $response = $this->send(
            $this->request('POST', '/api/discussions/1/merge', [
                'json' => [
                    'ids' => [2],
                ],
                'authenticatedAs' => 2,
            ])
        );

        $this->assertEquals(403, $response->getStatusCode());
    }

    public static function discussionMergeData(): array
    {
        return [
            [1, 2],
            [2, 1],
        ];
    }

    #[Test]
    #[DataProvider('discussionMergeData')]
    public function can_merge_discussions_by_date(int $to, int $from)
    {
        // Expected title based on which discussion is being merged
        $fromTitle = $from === 1 ? 'Discussion 1' : 'Discussion 2';

        $response = $this->send(
            $this->request('POST', "/api/discussions/$to/merge", [
                'json' => [
                    'ids'      => [$from],
                    'ordering' => 'date',
                ],
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($to, $data['data']['id']);

        $discussion = Discussion::find($to);

        $this->assertEquals(10, $discussion->comment_count);
        $this->assertEquals(2, $discussion->participant_count);

        $posts = $discussion->posts()->orderBy('created_at', 'asc')->get()->values();

        $this->assertEquals(11, $posts->count());

        // Check the posts were ordered as expected by date/time
        $this->assertEquals('Post 1 in Discussion 1', $posts->get(0)->content);
        $this->assertEquals('comment', $posts->get(0)->type);
        $this->assertEquals(1, $posts->get(0)->number);

        $this->assertEquals('Post 1 in Discussion 2', $posts->get(1)->content);
        $this->assertEquals('comment', $posts->get(1)->type);
        $this->assertEquals(2, $posts->get(1)->number);

        $this->assertEquals('Post 2 in Discussion 1', $posts->get(2)->content);
        $this->assertEquals('comment', $posts->get(2)->type);
        $this->assertEquals(3, $posts->get(2)->number);

        $this->assertEquals('Post 2 in Discussion 2', $posts->get(3)->content);
        $this->assertEquals('comment', $posts->get(3)->type);
        $this->assertEquals(4, $posts->get(3)->number);

        $this->assertEquals('Post 3 in Discussion 1', $posts->get(4)->content);
        $this->assertEquals('comment', $posts->get(4)->type);
        $this->assertEquals(5, $posts->get(4)->number);

        $this->assertEquals('Post 3 in Discussion 2', $posts->get(5)->content);
        $this->assertEquals('comment', $posts->get(5)->type);
        $this->assertEquals(6, $posts->get(5)->number);

        $this->assertEquals('Post 4 in Discussion 1', $posts->get(6)->content);
        $this->assertEquals('comment', $posts->get(6)->type);
        $this->assertEquals(7, $posts->get(6)->number);

        $this->assertEquals('Post 4 in Discussion 2', $posts->get(7)->content);
        $this->assertEquals('comment', $posts->get(7)->type);
        $this->assertEquals(8, $posts->get(7)->number);

        $this->assertEquals('Post 5 in Discussion 2', $posts->get(8)->content);
        $this->assertEquals('comment', $posts->get(8)->type);
        $this->assertEquals(9, $posts->get(8)->number);

        $this->assertEquals('Post 5 in Discussion 1', $posts->get(9)->content);
        $this->assertEquals('comment', $posts->get(9)->type);
        $this->assertEquals(10, $posts->get(9)->number);

        $this->assertEquals('discussionMerged', $posts->get(10)->type);
        $this->assertEquals(11, $posts->get(10)->number);

        // Verify the merge post content
        $mergePost = $posts->get(10);
        $this->assertInstanceOf(\FoF\MergeDiscussions\Posts\DiscussionMergePost::class, $mergePost);
        $this->assertIsArray($mergePost->content);
        $this->assertArrayHasKey('count', $mergePost->content);
        $this->assertArrayHasKey('titles', $mergePost->content);

        // Should have merged 5 posts from the other discussion
        $this->assertEquals(5, $mergePost->content['count']);

        // Should have the title of the merged discussion
        $this->assertIsArray($mergePost->content['titles']);
        $this->assertCount(1, $mergePost->content['titles']);

        // Verify the title matches the discussion that was merged
        $this->assertEquals($fromTitle, $mergePost->content['titles'][0]);

        // Test the merged discussion has a 301 redirect to the target discussion

        $response = $this->send(
            $this->request('GET', "/d/$from", [])
        );

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/d/$to", $response->getHeader('Location')[0]);
    }

    #[Test]
    #[DataProvider('discussionMergeData')]
    public function can_merge_discussions_by_suffix(int $to, int $from)
    {
        $response = $this->send(
            $this->request('POST', "/api/discussions/$to/merge", [
                'json' => [
                    'ids'      => [$from],
                    'ordering' => 'suffix',
                ],
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($to, $data['data']['id']);

        $discussion = Discussion::find($to);

        $this->assertEquals(10, $discussion->comment_count);
        $this->assertEquals(2, $discussion->participant_count);

        $posts = $discussion->posts()->get()->values();

        $this->assertEquals(11, $posts->count());

        // check the posts were ordered as expected

        $this->assertEquals("Post 1 in Discussion $to", $posts->get(0)->content);
        $this->assertEquals('comment', $posts->get(0)->type);
        $this->assertEquals(1, $posts->get(0)->number);

        $this->assertEquals("Post 2 in Discussion $to", $posts->get(1)->content);
        $this->assertEquals('comment', $posts->get(1)->type);
        $this->assertEquals(2, $posts->get(1)->number);

        $this->assertEquals("Post 3 in Discussion $to", $posts->get(2)->content);
        $this->assertEquals('comment', $posts->get(2)->type);
        $this->assertEquals(3, $posts->get(2)->number);

        $this->assertEquals("Post 4 in Discussion $to", $posts->get(3)->content);
        $this->assertEquals('comment', $posts->get(3)->type);
        $this->assertEquals(4, $posts->get(3)->number);

        $this->assertEquals("Post 5 in Discussion $to", $posts->get(4)->content);
        $this->assertEquals('comment', $posts->get(4)->type);
        $this->assertEquals(5, $posts->get(4)->number);

        $this->assertEquals("Post 1 in Discussion $from", $posts->get(5)->content);
        $this->assertEquals('comment', $posts->get(5)->type);
        $this->assertEquals(6, $posts->get(5)->number);

        $this->assertEquals("Post 2 in Discussion $from", $posts->get(6)->content);
        $this->assertEquals('comment', $posts->get(6)->type);
        $this->assertEquals(7, $posts->get(6)->number);

        $this->assertEquals("Post 3 in Discussion $from", $posts->get(7)->content);
        $this->assertEquals('comment', $posts->get(7)->type);
        $this->assertEquals(8, $posts->get(7)->number);

        $this->assertEquals("Post 4 in Discussion $from", $posts->get(8)->content);
        $this->assertEquals('comment', $posts->get(8)->type);
        $this->assertEquals(9, $posts->get(8)->number);

        $this->assertEquals("Post 5 in Discussion $from", $posts->get(9)->content);
        $this->assertEquals('comment', $posts->get(9)->type);
        $this->assertEquals(10, $posts->get(9)->number);

        $this->assertEquals('discussionMerged', $posts->get(10)->type);
        $this->assertEquals(11, $posts->get(10)->number);

        // Test the merged discussion has a 301 redirect to the target discussion

        $response = $this->send(
            $this->request('GET', "/d/$from", [])
        );

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals("/d/$to", $response->getHeader('Location')[0]);
    }

    #[Test]
    public function can_merge_multiple_discussions_by_date()
    {
        $response = $this->send(
            $this->request('POST', '/api/discussions/1/merge', [
                'json' => [
                    'ids'      => [2, 3],
                    'ordering' => 'date',
                ],
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals(1, $data['data']['id']);

        $discussion = Discussion::find(1);

        $this->assertEquals(15, $discussion->comment_count);
        $this->assertEquals(2, $discussion->participant_count);

        $posts = $discussion->posts()->orderBy('created_at', 'asc')->get()->values();

        $this->assertEquals(16, $posts->count());

        // Check the posts were ordered as expected by date/time
        $this->assertEquals('Post 1 in Discussion 1', $posts->get(0)->content);
        $this->assertEquals('comment', $posts->get(0)->type);
        $this->assertEquals(1, $posts->get(0)->number);

        $this->assertEquals('Post 1 in Discussion 2', $posts->get(1)->content);
        $this->assertEquals('comment', $posts->get(1)->type);
        $this->assertEquals(2, $posts->get(1)->number);

        $this->assertEquals('Post 2 in Discussion 1', $posts->get(2)->content);
        $this->assertEquals('comment', $posts->get(2)->type);
        $this->assertEquals(3, $posts->get(2)->number);

        $this->assertEquals('Post 2 in Discussion 2', $posts->get(3)->content);
        $this->assertEquals('comment', $posts->get(3)->type);
        $this->assertEquals(4, $posts->get(3)->number);

        $this->assertEquals('Post 1 in Discussion 3', $posts->get(4)->content);
        $this->assertEquals('comment', $posts->get(4)->type);
        $this->assertEquals(5, $posts->get(4)->number);

        $this->assertEquals('Post 2 in Discussion 3', $posts->get(5)->content);
        $this->assertEquals('comment', $posts->get(5)->type);
        $this->assertEquals(6, $posts->get(5)->number);

        $this->assertEquals('Post 3 in Discussion 1', $posts->get(6)->content);
        $this->assertEquals('comment', $posts->get(6)->type);
        $this->assertEquals(7, $posts->get(6)->number);

        $this->assertEquals('Post 3 in Discussion 2', $posts->get(7)->content);
        $this->assertEquals('comment', $posts->get(7)->type);
        $this->assertEquals(8, $posts->get(7)->number);

        $this->assertEquals('Post 3 in Discussion 3', $posts->get(8)->content);
        $this->assertEquals('comment', $posts->get(8)->type);
        $this->assertEquals(9, $posts->get(8)->number);

        $this->assertEquals('Post 4 in Discussion 1', $posts->get(9)->content);
        $this->assertEquals('comment', $posts->get(9)->type);
        $this->assertEquals(10, $posts->get(9)->number);

        $this->assertEquals('Post 4 in Discussion 2', $posts->get(10)->content);
        $this->assertEquals('comment', $posts->get(10)->type);
        $this->assertEquals(11, $posts->get(10)->number);

        $this->assertEquals('Post 5 in Discussion 2', $posts->get(11)->content);
        $this->assertEquals('comment', $posts->get(11)->type);
        $this->assertEquals(12, $posts->get(11)->number);

        $this->assertEquals('Post 4 in Discussion 3', $posts->get(12)->content);
        $this->assertEquals('comment', $posts->get(12)->type);
        $this->assertEquals(13, $posts->get(12)->number);

        $this->assertEquals('Post 5 in Discussion 1', $posts->get(13)->content);
        $this->assertEquals('comment', $posts->get(13)->type);
        $this->assertEquals(14, $posts->get(13)->number);

        $this->assertEquals('Post 5 in Discussion 3', $posts->get(14)->content);
        $this->assertEquals('comment', $posts->get(14)->type);
        $this->assertEquals(15, $posts->get(14)->number);

        $this->assertEquals('discussionMerged', $posts->get(15)->type);
        $this->assertEquals(16, $posts->get(15)->number);

        // Test the merged discussion has a 301 redirect to the target discussion

        $response = $this->send(
            $this->request('GET', '/d/2', [])
        );

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/d/1', $response->getHeader('Location')[0]);

        $response = $this->send(
            $this->request('GET', '/d/3', [])
        );

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/d/1', $response->getHeader('Location')[0]);
    }
}
