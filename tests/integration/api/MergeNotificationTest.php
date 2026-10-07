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
use PHPUnit\Framework\Attributes\Test;

/**
 * The author of a merged discussion is told where it went.
 */
class MergeNotificationTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const AUTHOR = 2;
    private const OTHER_AUTHOR = 4;
    private const MODERATOR = 3;

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-merge-discussions');

        $date = Carbon::parse('2024-01-01 00:00:00');

        $this->prepareDatabase([
            User::class => [
                $this->normalUser(),
                ['id' => self::MODERATOR, 'username' => 'moderator', 'email' => 'moderator@machine.local', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'is_email_confirmed' => true],
                ['id' => self::OTHER_AUTHOR, 'username' => 'other', 'email' => 'other@machine.local', 'password' => '$2y$10$LO59tiT7uggl6Oe23o/O6.utnF6ipngYjvMvaxo1TciKqBttDNKim', 'is_email_confirmed' => true],
            ],
            'group_user' => [
                ['user_id' => self::MODERATOR, 'group_id' => 4],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'discussion.merge'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Target', 'user_id' => self::MODERATOR, 'created_at' => $date, 'first_post_id' => 101, 'comment_count' => 1],
                ['id' => 2, 'title' => 'By the author', 'user_id' => self::AUTHOR, 'created_at' => $date, 'first_post_id' => 201, 'comment_count' => 1],
                ['id' => 3, 'title' => 'By someone else', 'user_id' => self::OTHER_AUTHOR, 'created_at' => $date, 'first_post_id' => 301, 'comment_count' => 1],
                ['id' => 4, 'title' => 'By the moderator', 'user_id' => self::MODERATOR, 'created_at' => $date, 'first_post_id' => 401, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'user_id' => self::MODERATOR, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>101</p></t>'],
                ['id' => 201, 'discussion_id' => 2, 'number' => 1, 'user_id' => self::AUTHOR, 'created_at' => $date->copy()->addMinute(), 'type' => 'comment', 'content' => '<t><p>201</p></t>'],
                ['id' => 301, 'discussion_id' => 3, 'number' => 1, 'user_id' => self::OTHER_AUTHOR, 'created_at' => $date->copy()->addMinutes(2), 'type' => 'comment', 'content' => '<t><p>301</p></t>'],
                ['id' => 401, 'discussion_id' => 4, 'number' => 1, 'user_id' => self::MODERATOR, 'created_at' => $date->copy()->addMinutes(3), 'type' => 'comment', 'content' => '<t><p>401</p></t>'],
            ],
        ]);
    }

    /**
     * The content is compared without key order: MySQL's JSON columns reorder keys.
     */
    #[Test]
    public function each_merged_discussions_author_is_told_where_it_went()
    {
        $this->merge([2, 3]);

        $this->assertEquals([['discussionMerged', '1', ['merged_title' => 'By the author', 'merged_id' => 2]]], $this->notificationsOf(self::AUTHOR));
        $this->assertEquals([['discussionMerged', '1', ['merged_title' => 'By someone else', 'merged_id' => 3]]], $this->notificationsOf(self::OTHER_AUTHOR));
    }

    #[Test]
    public function a_moderator_merging_their_own_discussion_is_not_notified()
    {
        $this->merge([4]);

        $this->assertSame([], $this->notificationsOf(self::MODERATOR));
    }

    private function merge(array $sources): void
    {
        $response = $this->send($this->request('POST', '/api/discussions/1/merge', [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => $sources, 'ordering' => 'date'],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    /**
     * The user's notifications as [type, subject id, content].
     */
    private function notificationsOf(int $userId): array
    {
        $response = $this->send($this->request('GET', '/api/notifications', ['authenticatedAs' => $userId]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        return array_map(
            fn (array $notification) => [
                $notification['attributes']['contentType'],
                $notification['relationships']['subject']['data']['id'],
                $notification['attributes']['content'],
            ],
            json_decode((string) $response->getBody(), true)['data']
        );
    }
}
