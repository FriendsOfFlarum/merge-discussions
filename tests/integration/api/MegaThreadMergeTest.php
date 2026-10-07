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
use PHPUnit\Framework\Attributes\DataProvider;
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

    private const MEMORY_BUDGET_MB = 100;

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

    public static function orderings(): array
    {
        return [
            'by date'  => ['date'],
            'appended' => ['suffix'],
        ];
    }

    #[Test]
    #[DataProvider('orderings')]
    public function merge_of_two_mega_threads_succeeds_and_numbers_every_post(string $ordering)
    {
        $this->seedInterleavedPosts();

        $response = $this->send($this->request('POST', '/api/discussions/1/merge', [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => [2], 'ordering' => $ordering],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), substr((string) $response->getBody(), 0, 2000));

        $numbers = $this->database()->table('posts')->where('discussion_id', 1)->where('type', 'comment')
            ->selectRaw('COUNT(*) AS posts, COUNT(DISTINCT number) AS distinct_numbers, MIN(number) AS lowest, MAX(number) AS highest')
            ->first();

        $this->assertEquals([2 * self::POSTS, 2 * self::POSTS, 1, 2 * self::POSTS], [(int) $numbers->posts, (int) $numbers->distinct_numbers, (int) $numbers->lowest, (int) $numbers->highest]);
    }

    /**
     * The merge keeps every post as a model for its events, which costs a few
     * KB each. Peaking at about 90 MB for these 20,000 posts on MySQL, whose
     * driver buffers results inside PHP's own memory accounting, the budget
     * catches a second copy of the posts or a serialized relation graph.
     */
    #[Test]
    public function date_merge_of_two_mega_threads_stays_within_a_memory_budget()
    {
        $this->seedInterleavedPosts();

        memory_reset_peak_usage();
        $before = memory_get_usage();

        $response = $this->send($this->request('POST', '/api/discussions/1/merge', [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => [2], 'ordering' => 'date'],
        ]));

        $peak = (memory_get_peak_usage() - $before) / 1048576;

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertLessThan(self::MEMORY_BUDGET_MB, $peak, sprintf('Merging %d posts peaked at %.1f MB', 2 * self::POSTS, $peak));
    }

    /**
     * The preview shows how the merged discussion starts, not all of it:
     * every post would be rendered and sent at once, then rendered again by
     * the browser.
     */
    #[Test]
    public function preview_of_two_mega_threads_shows_the_first_posts_and_counts_them_all()
    {
        $this->seedInterleavedPosts();

        $response = $this->send(
            $this->request('GET', '/api/discussions/1/merge-preview', ['authenticatedAs' => self::MODERATOR])
                ->withQueryParams(['byIds' => '2', 'byOrdering' => 'date'])
        );

        $this->assertEquals(200, $response->getStatusCode(), substr((string) $response->getBody(), 0, 2000));

        $document = json_decode((string) $response->getBody(), true);

        // Interleaved by date: the target's posts 1 to 25, alternating with the source's.
        $expected = [];
        for ($i = 1; $i <= 25; $i++) {
            $expected[] = (string) $i;
            $expected[] = (string) (self::POSTS + $i);
        }

        $this->assertSame($expected, array_column($document['data']['relationships']['posts']['data'], 'id'));

        $numbers = array_column(array_column(array_filter($document['included'], fn (array $resource) => $resource['type'] === 'posts'), 'attributes'), 'number');
        sort($numbers);
        $this->assertSame(range(1, 50), $numbers);

        $this->assertSame(2 * self::POSTS, $document['meta']['fof-merge-discussions']['totalPosts']);
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

        // As the test case does for its own fixtures: PostgreSQL doesn't move
        // the id sequence past ids inserted by hand, so the next post the
        // merge creates would reuse one of them.
        if ($this->database()->getDriverName() === 'pgsql') {
            $grammar = $this->database()->getSchemaGrammar();

            $this->database()->statement(sprintf(
                "SELECT setval('%s', (SELECT MAX(id) FROM %s))",
                $grammar->wrapTable('posts_id_seq'),
                $grammar->wrapTable('posts')
            ));
        }
    }

    private function at(int $minutes): Carbon
    {
        return Carbon::parse('2024-01-01 00:00:00')->addMinutes($minutes);
    }
}
