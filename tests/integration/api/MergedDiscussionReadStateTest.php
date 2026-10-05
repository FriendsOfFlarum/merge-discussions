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

/**
 * Whether a merged discussion shows as unread (#20).
 *
 * The forum counts a reader's unread posts as the discussion's lastPostNumber
 * minus their lastReadPostNumber, so a merge has to leave lastPostNumber
 * covering every post it added.
 */
class MergedDiscussionReadStateTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const READER = 2;
    private const MODERATOR = 3;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-merge-discussions');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => self::MODERATOR, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'is_email_confirmed' => true],
            ],
            'group_user' => [
                ['user_id' => self::MODERATOR, 'group_id' => 4],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'discussion.merge'],
            ],
            // The target's latest reply is newer than anything in the source.
            Discussion::class => [
                ['id' => 1, 'title' => 'Target', 'user_id' => 1, 'created_at' => $this->day(1), 'first_post_id' => 101, 'comment_count' => 2, 'last_post_id' => 102, 'last_post_number' => 2, 'last_posted_at' => $this->day(4), 'last_posted_user_id' => 1],
                ['id' => 2, 'title' => 'Source', 'user_id' => 1, 'created_at' => $this->day(2), 'first_post_id' => 201, 'comment_count' => 2, 'last_post_id' => 202, 'last_post_number' => 2, 'last_posted_at' => $this->day(3), 'last_posted_user_id' => 1],
            ],
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'created_at' => $this->day(1), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>101</p></t>'],
                ['id' => 102, 'discussion_id' => 1, 'number' => 2, 'created_at' => $this->day(4), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>102</p></t>'],
                ['id' => 201, 'discussion_id' => 2, 'number' => 1, 'created_at' => $this->day(2), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>201</p></t>'],
                ['id' => 202, 'discussion_id' => 2, 'number' => 2, 'created_at' => $this->day(3), 'user_id' => 1, 'type' => 'comment', 'content' => '<t><p>202</p></t>'],
            ],
            // The reader had read the target to its end.
            'discussion_user' => [
                ['user_id' => self::READER, 'discussion_id' => 1, 'last_read_post_number' => 2, 'last_read_at' => $this->day(4)],
            ],
        ]);
    }

    /**
     * Appending puts the source's posts after the target's latest reply, though
     * they are older, so the latest reply is no longer the last post.
     */
    #[Test]
    public function posts_a_merge_appends_are_unread_for_someone_who_had_read_the_target()
    {
        $this->merge(1, [2], 'suffix');

        $this->assertSame(2, $this->unreadCount(self::READER, 1));
    }

    public static function orderings(): array
    {
        return [
            'by date'  => ['date'],
            'appended' => ['suffix'],
        ];
    }

    /**
     * What #20 reported: once caught up, a merged discussion stayed read even
     * as new replies moved it up the discussion list.
     */
    #[Test]
    #[DataProvider('orderings')]
    public function reply_after_a_merge_is_unread_for_someone_who_had_caught_up(string $ordering)
    {
        $this->merge(1, [2], $ordering);

        // Caught up: read to the end, the merge notice at #5.
        $this->read(self::READER, 1, 5);

        $this->reply(1, 1);

        $this->assertSame(1, $this->unreadCount(self::READER, 1));
    }

    private function day(int $day): Carbon
    {
        return Carbon::parse('2024-01-01 00:00:00')->addDays($day);
    }

    private function merge(int $target, array $sources, string $ordering): void
    {
        $response = $this->send($this->request('POST', "/api/discussions/$target/merge", [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => $sources, 'ordering' => $ordering],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    private function read(int $userId, int $discussionId, int $number): void
    {
        $response = $this->send($this->request('PATCH', "/api/discussions/$discussionId", [
            'authenticatedAs' => $userId,
            'json'            => ['data' => ['type' => 'discussions', 'id' => (string) $discussionId, 'attributes' => ['lastReadPostNumber' => $number]]],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    private function reply(int $discussionId, int $userId): void
    {
        $response = $this->send($this->request('POST', '/api/posts', [
            'authenticatedAs' => $userId,
            'json'            => ['data' => [
                'type'          => 'posts',
                'attributes'    => ['content' => 'A new reply'],
                'relationships' => ['discussion' => ['data' => ['type' => 'discussions', 'id' => (string) $discussionId]]],
            ]],
        ]));

        $this->assertEquals(201, $response->getStatusCode(), (string) $response->getBody());
    }

    /**
     * How the forum counts the reader's unread posts in the discussion.
     */
    private function unreadCount(int $userId, int $discussionId): int
    {
        $response = $this->send($this->request('GET', "/api/discussions/$discussionId", ['authenticatedAs' => $userId]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $attributes = json_decode((string) $response->getBody(), true)['data']['attributes'];

        return max(0, $attributes['lastPostNumber'] - ($attributes['lastReadPostNumber'] ?? 0));
    }
}
