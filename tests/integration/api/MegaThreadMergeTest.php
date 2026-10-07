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
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

/**
 * Merging discussions with tens of thousands of posts, which used to exhaust
 * memory or time.
 */
class MegaThreadMergeTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const MODERATOR = 3;

    /** Posts in each of the two discussions. */
    private const POSTS = 10000;

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
                ['id' => 1, 'title' => 'Target', 'user_id' => 2, 'created_at' => $this->at(0), 'first_post_id' => 1, 'comment_count' => self::POSTS],
                ['id' => 2, 'title' => 'Source', 'user_id' => 2, 'created_at' => $this->at(1), 'first_post_id' => self::POSTS + 1, 'comment_count' => self::POSTS],
            ],
        ]);
    }

    #[Test]
    public function date_merge_of_two_mega_threads_succeeds_and_numbers_every_post()
    {
        $this->seedInterleavedPosts();

        $response = $this->send($this->request('POST', '/api/discussions/1/merge', [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => [2], 'ordering' => 'date'],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), substr((string) $response->getBody(), 0, 2000));

        $numbers = $this->database()->table('posts')->where('discussion_id', 1)->where('type', 'comment')
            ->selectRaw('COUNT(*) AS posts, COUNT(DISTINCT number) AS distinct_numbers, MIN(number) AS lowest, MAX(number) AS highest')
            ->first();

        $this->assertEquals([2 * self::POSTS, 2 * self::POSTS, 1, 2 * self::POSTS], [(int) $numbers->posts, (int) $numbers->distinct_numbers, (int) $numbers->lowest, (int) $numbers->highest]);
    }

    /**
     * Interleaved by date, so a merge by date renumbers every post.
     */
    private function seedInterleavedPosts(): void
    {
        $body = '<t><p>'.str_repeat('A reply long enough to look like a real one. ', 22).'</p></t>';
        $rows = [];

        for ($i = 1; $i <= self::POSTS; $i++) {
            foreach ([1, 2] as $discussion) {
                $rows[] = [
                    'id'            => ($discussion - 1) * self::POSTS + $i,
                    'discussion_id' => $discussion,
                    'number'        => $i,
                    'user_id'       => 2,
                    'type'          => 'comment',
                    'content'       => $body,
                    'created_at'    => $this->at(2 * $i + $discussion),
                    'is_private'    => false,
                ];
            }
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            $this->database()->table('posts')->insert($chunk);
        }
    }

    private function at(int $minutes): Carbon
    {
        return Carbon::parse('2024-01-01 00:00:00')->addMinutes($minutes);
    }
}
