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
use Flarum\Discussion\Event\Deleting;
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Merging deletes the merged-away discussions. Extensions clean up their own
 * records for a discussion when core announces it is being deleted (fof/seo
 * drops its meta row, for one), so a merge has to announce it the same way.
 */
class MergedAwayDiscussionDeletionTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const MODERATOR = 3;

    /** What a Deleting listener saw, as [discussion id, actor id, still exists]. */
    private static array $announced = [];

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-merge-discussions');

        $date = Carbon::parse('2024-01-01 00:00:00');

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
            Discussion::class => [
                ['id' => 1, 'title' => 'Target', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 2, 'title' => 'Source A', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 2, 'comment_count' => 1],
                ['id' => 3, 'title' => 'Source B', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 3, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'created_at' => $date, 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>1</p></t>'],
                ['id' => 2, 'discussion_id' => 2, 'number' => 1, 'created_at' => $date->copy()->addMinute(), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>2</p></t>'],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'created_at' => $date->copy()->addMinutes(2), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>3</p></t>'],
            ],
        ]);
    }

    #[Test]
    public function merged_away_discussions_are_announced_as_deleting_before_they_go()
    {
        $this->extend((new Extend\Event())->listen(Deleting::class, function (Deleting $event) {
            self::$announced[] = [$event->discussion->id, $event->actor->id, Discussion::query()->whereKey($event->discussion->id)->exists()];
        }));

        $response = $this->send($this->request('POST', '/api/discussions/1/merge', [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => [2, 3]],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertEqualsCanonicalizing([
            [2, self::MODERATOR, true],
            [3, self::MODERATOR, true],
        ], self::$announced);
    }
}
