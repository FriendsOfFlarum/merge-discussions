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
use Flarum\Extend;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use FoF\MergeDiscussions\Events\MergingDiscussions;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * How a merge writes post positions: every merged discussion must end up with
 * a valid unique(discussion_id, number) sequence, whatever the starting
 * numbering, and the writes must not scale with the number of posts.
 */
class MergeRenumberingTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const MODERATOR = 3;

    /** What a MergingDiscussions listener saw, as [id, discussion id, number, dirty]. */
    private static array $seenByListener = [];

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
        ]);
    }

    #[Test]
    public function date_merge_closes_numbering_gaps_in_both_discussions()
    {
        // Gaps as left behind by deleted posts.
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [102, 1, 4, 20],
            [103, 1, 9, 40],
            [201, 2, 2, 10],
            [202, 2, 5, 30],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $this->assertPositions(1, [101, 201, 102, 202, 103]);
    }

    #[Test]
    public function date_merge_shifts_target_posts_up_when_merged_posts_are_older()
    {
        // Every target post must move onto a number another target post holds.
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 30],
            [102, 1, 2, 40],
            [103, 1, 3, 50],
            [201, 2, 1, 0],
            [202, 2, 2, 10],
            [203, 2, 3, 20],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $this->assertPositions(1, [201, 202, 203, 101, 102, 103]);
    }

    #[Test]
    public function date_merge_follows_created_at_when_existing_numbers_disagree()
    {
        // Imported or edited history: number order is the reverse of date order.
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 50],
            [102, 1, 2, 30],
            [103, 1, 3, 10],
            [201, 2, 1, 40],
            [202, 2, 2, 20],
            [203, 2, 3, 0],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $this->assertPositions(1, [203, 103, 202, 102, 201, 101]);
    }

    #[Test]
    public function date_merge_of_three_discussions_interleaves_all_posts_by_date()
    {
        $this->seed([1 => 'Target', 2 => 'Source A', 3 => 'Source B'], [
            [101, 1, 1, 0],
            [102, 1, 2, 30],
            [201, 2, 1, 10],
            [202, 2, 2, 40],
            [301, 3, 1, 20],
            [302, 3, 2, 50],
        ]);

        $this->assertMerged($this->merge(1, [2, 3], 'date'));

        $this->assertPositions(1, [101, 201, 301, 102, 202, 302]);
    }

    #[Test]
    public function date_merge_with_identical_timestamps_still_numbers_every_post_uniquely()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [102, 1, 2, 0],
            [201, 2, 1, 0],
            [202, 2, 2, 0],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $posts = $this->postsOf(1);

        $this->assertEqualsCanonicalizing([101, 102, 201, 202], $posts->pluck('id')->all());
        $this->assertSame([1, 2, 3, 4], $posts->pluck('number')->all());
    }

    #[Test]
    public function suffix_merge_appends_after_the_highest_number_and_leaves_target_posts_alone()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [102, 1, 2, 10],
            [103, 1, 7, 20],
            [201, 2, 1, 5],
            [202, 2, 2, 15],
            [203, 2, 3, 25],
        ]);

        $this->assertMerged($this->merge(1, [2], 'suffix'));

        $this->assertPositions(1, [101, 102, 103, 201, 202, 203], [1, 2, 7, 8, 9, 10]);
    }

    #[Test]
    public function suffix_merge_of_several_discussions_appends_their_posts_in_date_order()
    {
        $this->seed([1 => 'Target', 2 => 'Source A', 3 => 'Source B'], [
            [101, 1, 1, 0],
            [102, 1, 2, 60],
            [201, 2, 1, 10],
            [202, 2, 2, 30],
            [301, 3, 1, 20],
            [302, 3, 2, 40],
        ]);

        $this->assertMerged($this->merge(1, [2, 3], 'suffix'));

        $this->assertPositions(1, [101, 102, 201, 301, 202, 302]);
    }

    #[Test]
    public function merge_moves_hidden_posts_and_keeps_them_hidden()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [201, 2, 1, 10],
            [202, 2, 2, 20, ['hidden_at' => $this->at(25), 'hidden_user_id' => 1]],
            [203, 2, 3, 30],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $this->assertPositions(1, [101, 201, 202, 203]);
        $this->assertNotNull(Post::query()->withoutGlobalScopes()->find(202)->hidden_at);

        // Hidden posts are not comments for counting purposes.
        $this->assertEquals(3, Discussion::find(1)->comment_count);
    }

    #[Test]
    public function merge_refreshes_the_target_discussion_details()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 10, ['user_id' => 1]],
            [102, 1, 2, 30, ['user_id' => 1]],
            [201, 2, 1, 0, ['user_id' => 2]],
            [202, 2, 2, 20, ['user_id' => 2]],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $discussion = Discussion::find(1);

        $this->assertEquals(201, $discussion->first_post_id, 'The oldest merged post becomes the first post.');
        $this->assertEquals(4, $discussion->comment_count);
        $this->assertEquals(2, $discussion->participant_count);
        $this->assertNull(Discussion::find(2), 'The merged-away discussion is deleted.');
    }

    #[Test]
    public function merge_post_takes_the_number_after_the_merged_posts()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [102, 1, 5, 20],
            [201, 2, 1, 10],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $mergePost = Post::query()->where('discussion_id', 1)->where('type', 'discussionMerged')->firstOrFail();

        $this->assertEquals(4, $mergePost->number);
    }

    #[Test]
    public function a_reply_after_a_merge_gets_the_next_free_number()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [102, 1, 2, 20],
            [201, 2, 1, 10],
            [202, 2, 2, 30],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        // Someone other than the merging moderator, who has just authored the merge post and would be flood-limited.
        $response = $this->send($this->request('POST', '/api/posts', [
            'authenticatedAs' => 2,
            'json'            => [
                'data' => [
                    'type'          => 'posts',
                    'attributes'    => ['content' => 'A reply to the merged discussion'],
                    'relationships' => ['discussion' => ['data' => ['type' => 'discussions', 'id' => '1']]],
                ],
            ],
        ]));

        $body = (string) $response->getBody();
        $this->assertEquals(201, $response->getStatusCode(), $body);

        // 4 merged posts, then the merge post at 5.
        $this->assertEquals(6, json_decode($body, true)['data']['attributes']['number']);
    }

    #[Test]
    public function large_date_merge_writes_posts_in_a_fixed_number_of_queries()
    {
        // 600 interleaved posts: every post but the first moves, and the
        // renumbering spans two chunks.
        $rows = [];
        for ($i = 1; $i <= 300; $i++) {
            $rows[] = [1000 + $i, 1, $i, 2 * $i];
            $rows[] = [2000 + $i, 2, $i, 2 * $i + 1];
        }
        $this->seed([1 => 'Target', 2 => 'Source'], $rows);

        $this->database()->enableQueryLog();

        $this->assertMerged($this->merge(1, [2], 'date'));

        $postUpdates = array_values(array_filter(
            array_column($this->database()->getQueryLog(), 'query'),
            fn (string $sql) => preg_match('/^update\s+\S*posts\S*\s+set\b/i', $sql) === 1
        ));

        // Per 500-post chunk: one statement to park the posts, one to number them.
        $this->assertCount(4, $postUpdates, implode("\n", $postUpdates));

        $expected = [];
        for ($i = 1; $i <= 300; $i++) {
            $expected[] = 1000 + $i;
            $expected[] = 2000 + $i;
        }
        $this->assertPositions(1, $expected);
    }

    #[Test]
    public function merging_listeners_see_the_written_positions_on_clean_models()
    {
        $this->extend((new Extend\Event())->listen(MergingDiscussions::class, function (MergingDiscussions $event) {
            self::$seenByListener = $event->discussion->posts
                ->map(fn (Post $post) => [$post->id, $post->discussion_id, $post->number, $post->isDirty()])
                ->values()
                ->all();
        }));

        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 10],
            [102, 1, 2, 30],
            [201, 2, 1, 0],
            [202, 2, 2, 20],
        ]);

        $this->assertMerged($this->merge(1, [2], 'date'));

        $this->assertSame([
            [201, 1, 1, false],
            [101, 1, 2, false],
            [202, 1, 3, false],
            [102, 1, 4, false],
        ], self::$seenByListener);
        $this->assertPositions(1, [201, 101, 202, 102]);
    }

    #[Test]
    public function a_failure_after_the_writes_rolls_the_whole_merge_back()
    {
        $this->extend((new Extend\Event())->listen(MergingDiscussions::class, function () {
            throw new RuntimeException('Listener failure after the post positions were written');
        }));

        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 10],
            [102, 1, 2, 30],
            [201, 2, 1, 0],
            [202, 2, 2, 20],
        ]);

        $this->assertEquals(500, $this->merge(1, [2], 'date')->getStatusCode());

        $this->assertPositions(1, [101, 102]);
        $this->assertPositions(2, [201, 202]);
        $this->assertNotNull(Discussion::find(2));
        $this->assertEquals(0, $this->database()->table('fof_merge_discussions_redirections')->count());
    }

    #[Test]
    public function preview_numbers_posts_from_one_without_writing_anything()
    {
        $this->seed([1 => 'Target', 2 => 'Source'], [
            [101, 1, 1, 0],
            [102, 1, 2, 20],
            [201, 2, 1, 10],
            [202, 2, 2, 30],
        ]);

        $response = $this->send(
            $this->request('GET', '/api/discussions/1/merge-preview', [
                'authenticatedAs' => self::MODERATOR,
            ])->withQueryParams([
                'byIds'      => '2',
                'byOrdering' => 'date',
            ])
        );

        $body = (string) $response->getBody();
        $this->assertEquals(200, $response->getStatusCode(), $body);

        $numbers = collect(json_decode($body, true)['included'])
            ->where('type', 'posts')
            ->mapWithKeys(fn (array $post) => [(int) $post['id'] => $post['attributes']['number']])
            ->sortKeys()
            ->all();

        $this->assertSame([101 => 1, 102 => 3, 201 => 2, 202 => 4], $numbers);

        // Nothing was persisted.
        $this->assertPositions(1, [101, 102], [1, 2]);
        $this->assertPositions(2, [201, 202], [1, 2]);
    }

    /**
     * @param array<int, string>                         $discussions id => title
     * @param array<array{0:int,1:int,2:int,3:int,4?:array}> $posts  [id, discussion id, number, minutes after base, extra columns]
     */
    private function seed(array $discussions, array $posts): void
    {
        $postRows = array_map(fn (array $post) => array_merge([
            'id'            => $post[0],
            'discussion_id' => $post[1],
            'number'        => $post[2],
            'created_at'    => $this->at($post[3]),
            'user_id'       => 2,
            'type'          => 'comment',
            'content'       => "<t><p>Post {$post[0]}</p></t>",
        ], $post[4] ?? []), $posts);

        $discussionRows = [];
        foreach ($discussions as $id => $title) {
            $own = array_filter($postRows, fn (array $row) => $row['discussion_id'] === $id);
            usort($own, fn (array $a, array $b) => $a['number'] <=> $b['number']);

            $discussionRows[] = [
                'id'            => $id,
                'title'         => $title,
                'user_id'       => 2,
                'created_at'    => $own[0]['created_at'],
                'first_post_id' => $own[0]['id'],
                'comment_count' => count($own),
            ];
        }

        $this->prepareDatabase([
            Discussion::class => $discussionRows,
            Post::class       => $postRows,
        ]);
    }

    private function at(int $minutes): Carbon
    {
        return Carbon::parse('2024-01-01 00:00:00')->addMinutes($minutes);
    }

    private function merge(int $target, array $sources, string $ordering): ResponseInterface
    {
        return $this->send($this->request('POST', "/api/discussions/$target/merge", [
            'authenticatedAs' => self::MODERATOR,
            'json'            => [
                'ids'      => $sources,
                'ordering' => $ordering,
            ],
        ]));
    }

    private function assertMerged(ResponseInterface $response): void
    {
        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
    }

    /**
     * The discussion's posts, excluding the merge notice, in number order.
     */
    private function postsOf(int $discussionId)
    {
        return Post::query()
            ->withoutGlobalScopes()
            ->where('discussion_id', $discussionId)
            ->where('type', '!=', 'discussionMerged')
            ->orderBy('number')
            ->get();
    }

    /**
     * Assert the discussion holds exactly these posts in this order, numbered
     * 1..n unless other numbers are given.
     */
    private function assertPositions(int $discussionId, array $ids, ?array $numbers = null): void
    {
        $posts = $this->postsOf($discussionId);

        $this->assertSame($ids, $posts->pluck('id')->all(), 'Post order');
        $this->assertSame($numbers ?? range(1, count($ids)), $posts->pluck('number')->all(), 'Post numbers');
    }
}
