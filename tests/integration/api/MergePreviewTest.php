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
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;
use Flarum\User\User;
use Flarum\Post\Post;

class MergePreviewTest extends TestCase
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
                ['id' => 100, 'title' => 'Target Discussion', 'comment_count' => 2, 'user_id' => 1, 'created_at' => Carbon::now()->subDays(2), 'first_post_id' => 1000],
                ['id' => 101, 'title' => 'Source Discussion', 'comment_count' => 1, 'user_id' => 2, 'created_at' => Carbon::now()->subDay(), 'first_post_id' => 1001],
            ],
            Post::class => [
                // Target discussion posts
                ['id' => 1000, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Target post 1</p></t>', 'discussion_id' => 100, 'number' => 1, 'created_at' => Carbon::now()->subDays(2)],
                ['id' => 1002, 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>Target post 2</p></t>', 'discussion_id' => 100, 'number' => 2, 'created_at' => Carbon::now()->subDay()],
                // Source discussion post
                ['id' => 1001, 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>Source post 1</p></t>', 'discussion_id' => 101, 'number' => 1, 'created_at' => Carbon::now()->subHours(12)],
            ],
            'group_user' => [
                ['user_id' => 3, 'group_id' => 4], // Moderator group
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'discussion.merge'],
            ],
        ]);
    }

    #[Test]
    public function preview_endpoint_exists_and_is_accessible()
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions/100/merge-preview', [
                'authenticatedAs' => 3,
            ])->withQueryParams([
                'byIds' => '101',
                'byOrdering' => 'date',
            ])
        );

        $this->assertEquals(200, $response->getStatusCode());
    }

    #[Test]
    public function preview_returns_discussion_with_posts_relationship()
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions/100/merge-preview', [
                'authenticatedAs' => 3,
            ])->withQueryParams([
                'byIds' => '101',
                'byOrdering' => 'date',
            ])
        );

        $data = json_decode($response->getBody()->getContents(), true);

        $this->assertArrayHasKey('data', $data);
        $this->assertEquals('discussions', $data['data']['type']);
        $this->assertEquals('100', $data['data']['id']);
    }

    #[Test]
    public function can_merge_discussions_after_preview()
    {
        // First do a preview
        $previewResponse = $this->send(
            $this->request('GET', '/api/discussions/100/merge-preview', [
                'authenticatedAs' => 3,
            ])->withQueryParams([
                'byIds' => '101',
                'byOrdering' => 'date',
            ])
        );

        $this->assertEquals(200, $previewResponse->getStatusCode());

        // Then do the actual merge
        $mergeResponse = $this->send(
            $this->request('POST', '/api/discussions/100/merge', [
                'json' => [
                    'ids' => [101],
                    'ordering' => 'date',
                ],
                'authenticatedAs' => 3,
            ])
        );

        $this->assertEquals(200, $mergeResponse->getStatusCode());

        $data = json_decode($mergeResponse->getBody()->getContents(), true);

        // Verify the merge worked
        $this->assertEquals('100', $data['data']['id']);

        // Check that discussion 100 now has all posts
        $discussion = Discussion::find(100);
        $this->assertEquals(3, $discussion->comment_count); // 2 + 1 = 3
    }
}
