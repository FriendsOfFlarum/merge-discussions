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
use Psr\Http\Message\ResponseInterface;

/**
 * The discussions merged into a target are as much a part of the request as
 * the target: being allowed to merge into one discussion must not give access
 * to any other.
 */
class MergeSourceAccessTest extends TestCase
{
    use RetrievesAuthorizedUsers;

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
            ],
            'group_user' => [
                ['user_id' => self::MODERATOR, 'group_id' => 4],
            ],
            'group_permission' => [
                ['group_id' => 4, 'permission' => 'discussion.merge'],
            ],
            Discussion::class => [
                ['id' => 1, 'title' => 'Target', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 101, 'comment_count' => 1],
                // Private: no one sees it without an extension that grants it.
                ['id' => 3, 'title' => 'Private conversation', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 301, 'comment_count' => 1, 'is_private' => true],
            ],
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Target post</p></t>'],
                ['id' => 301, 'discussion_id' => 3, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Private words</p></t>'],
            ],
        ]);
    }

    /**
     * Refused exactly as a discussion that does not exist, so the response
     * gives nothing away about it.
     */
    #[Test]
    public function merging_a_discussion_the_moderator_cannot_see_is_refused_as_if_it_did_not_exist()
    {
        $unknown = $this->merge(1, [999]);
        $invisible = $this->merge(1, [3]);

        $this->assertEquals(422, $invisible->getStatusCode());
        $this->assertSame((string) $unknown->getBody(), (string) $invisible->getBody());

        $this->assertEquals(3, Post::query()->find(301)->discussion_id);
    }

    private function merge(int $target, array $sources): ResponseInterface
    {
        return $this->send($this->request('POST', "/api/discussions/$target/merge", [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => $sources, 'ordering' => 'date'],
        ]));
    }
}
