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
 * Following a link inside the forum never reaches the forum's redirect: the
 * frontend asks the API for the discussion instead. For a merged-away
 * discussion that is still a 404, but one that says where to go (#34).
 */
class MergedDiscussionNotFoundTest extends TestCase
{
    use RetrievesAuthorizedUsers;

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
            Discussion::class => [
                ['id' => 1, 'title' => 'Target', 'slug' => 'target', 'user_id' => 2, 'created_at' => $this->at(0), 'first_post_id' => 101, 'comment_count' => 2],
                ['id' => 2, 'title' => 'Source', 'slug' => 'source', 'user_id' => 2, 'created_at' => $this->at(10), 'first_post_id' => 201, 'comment_count' => 2],
            ],
            // A date merge interleaves these as 101 #1, 201 #2, 102 #3, 202 #4.
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'created_at' => $this->at(0), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>101</p></t>'],
                ['id' => 102, 'discussion_id' => 1, 'number' => 2, 'created_at' => $this->at(20), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>102</p></t>'],
                ['id' => 201, 'discussion_id' => 2, 'number' => 1, 'created_at' => $this->at(10), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>201</p></t>'],
                ['id' => 202, 'discussion_id' => 2, 'number' => 2, 'created_at' => $this->at(30), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>202</p></t>'],
            ],
        ]);
    }

    /**
     * The request the forum frontend makes when a link to /d/2-source/2 is followed.
     */
    #[Test]
    public function not_found_for_a_merged_post_link_says_where_the_post_is_now()
    {
        $this->merge(1, [2], 'date');

        $response = $this->showDiscussion('2-source', ['bySlug' => '1', 'page' => ['near' => '2']]);

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertSame('http://localhost/d/1-target/4', $this->redirectIn($response));
    }

    private function at(int $minutes): Carbon
    {
        return Carbon::parse('2024-01-01 00:00:00')->addMinutes($minutes);
    }

    private function merge(int $target, array $sources, string $ordering): void
    {
        $response = $this->send($this->request('POST', "/api/discussions/$target/merge", [
            'authenticatedAs' => self::MODERATOR,
            'json'            => [
                'ids'      => $sources,
                'ordering' => $ordering,
            ],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    private function showDiscussion(string $id, array $query = [], ?int $userId = null): ResponseInterface
    {
        return $this->send(
            $this->request('GET', "/api/discussions/$id", $userId ? ['authenticatedAs' => $userId] : [])
                ->withQueryParams($query)
        );
    }

    private function redirectIn(ResponseInterface $response): ?string
    {
        $body = json_decode((string) $response->getBody(), true);

        return $body['meta']['fof-merge-discussions']['redirect'] ?? null;
    }
}
