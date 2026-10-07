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
 * What the forum frontend is told about merging: whether a discussion can be
 * merged, and how many discussions the merge modal's search lists.
 */
class MergeAttributesTest extends TestCase
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
                ['id' => 1, 'title' => 'Discussion', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 101, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>101</p></t>'],
            ],
        ]);
    }

    #[Test]
    public function a_moderator_with_the_permission_can_merge_a_discussion()
    {
        $this->assertTrue($this->discussionAttributes(self::MODERATOR)['canMerge']);
    }

    #[Test]
    public function a_member_cannot()
    {
        $this->assertFalse($this->discussionAttributes(2)['canMerge']);
    }

    #[Test]
    public function the_merge_search_lists_four_discussions_by_default()
    {
        $this->assertSame(4, $this->forumAttributes()['fof-merge-discussions.search_limit']);
    }

    #[Test]
    public function the_merge_search_lists_as_many_discussions_as_the_admin_sets()
    {
        $this->setting('fof-merge-discussions.search_limit', '7');

        $this->assertSame(7, $this->forumAttributes()['fof-merge-discussions.search_limit']);
    }

    private function discussionAttributes(int $userId): array
    {
        $response = $this->send($this->request('GET', '/api/discussions/1', ['authenticatedAs' => $userId]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        return json_decode((string) $response->getBody(), true)['data']['attributes'];
    }

    private function forumAttributes(): array
    {
        $response = $this->send($this->request('GET', '/api'));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        return json_decode((string) $response->getBody(), true)['data']['attributes'];
    }
}
