<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Tests\integration\forum;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

/**
 * Links to individual posts of a merged-away discussion (#34).
 *
 * A post link is "/d/<discussion>/<number>". Merging moves the post into
 * another discussion under a new number, so its old link must follow it there
 * rather than land on the top of the target discussion.
 */
class MergedPostLinksTest extends TestCase
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
                ['id' => 3, 'title' => 'Final', 'slug' => 'final', 'user_id' => 2, 'created_at' => $this->at(5), 'first_post_id' => 301, 'comment_count' => 1],
            ],
            // A date merge of 2 into 1 interleaves these as 101 #1, 201 #2, 102 #3,
            // 202 #4. Merging 1 into 3 after that gives 101 #1, 301 #2, 201 #3,
            // 102 #4, 202 #5, then the first merge's notice post.
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'created_at' => $this->at(0), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>101</p></t>'],
                ['id' => 102, 'discussion_id' => 1, 'number' => 2, 'created_at' => $this->at(20), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>102</p></t>'],
                ['id' => 201, 'discussion_id' => 2, 'number' => 1, 'created_at' => $this->at(10), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>201</p></t>'],
                ['id' => 202, 'discussion_id' => 2, 'number' => 2, 'created_at' => $this->at(30), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>202</p></t>'],
                ['id' => 301, 'discussion_id' => 3, 'number' => 1, 'created_at' => $this->at(5), 'user_id' => 2, 'type' => 'comment', 'content' => '<t><p>301</p></t>'],
            ],
        ]);
    }

    public static function movedPostLinks(): array
    {
        return [
            'first post, slugged'  => ['/d/2-source/1', 'http://localhost/d/1-target/2'],
            'second post, slugged' => ['/d/2-source/2', 'http://localhost/d/1-target/4'],
            'second post, bare id' => ['/d/2/2', 'http://localhost/d/1-target/4'],
        ];
    }

    #[Test]
    #[DataProvider('movedPostLinks')]
    public function link_to_a_merged_post_redirects_to_where_the_post_is_now(string $oldLink, string $newLink)
    {
        $this->merge(1, [2], 'date');

        $response = $this->get($oldLink);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals($newLink, $response->getHeaderLine('Location'));
    }

    public static function twiceMovedPostLinks(): array
    {
        return [
            'link from before both merges'  => ['/d/2-source/2'],
            'link from between the merges'  => ['/d/1-target/4'],
        ];
    }

    /**
     * The post is looked up where it is now, not through the discussions it
     * passed through, so every old link to it takes a single hop.
     */
    #[Test]
    #[DataProvider('twiceMovedPostLinks')]
    public function link_to_a_post_merged_twice_goes_to_its_final_position_in_one_hop(string $oldLink)
    {
        $this->merge(1, [2], 'date');
        $this->merge(3, [1], 'date');

        $response = $this->get($oldLink);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/3-final/5', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function link_to_a_merged_post_deleted_since_goes_to_the_discussion_it_was_merged_into()
    {
        $this->merge(1, [2], 'date');

        Post::query()->findOrFail(202)->delete();

        $response = $this->get('/d/2-source/2');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target', $response->getHeaderLine('Location'));
    }

    /**
     * The canonical URL carries the title of the discussion the post is in now,
     * so it is only given to someone who can see that discussion.
     */
    #[Test]
    public function link_to_a_merged_post_now_in_a_discussion_the_visitor_cannot_see_stays_a_404()
    {
        $this->merge(1, [2], 'date');

        Discussion::query()->whereKey(1)->update(['hidden_at' => Carbon::now(), 'hidden_user_id' => 1]);

        $response = $this->get('/d/2-source/2');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertSame('', $response->getHeaderLine('Location'));
    }

    /**
     * MySQL 5.7 hands out the highest id again after a restart, so merging away
     * the newest discussion can leave rows behind for an id a later discussion
     * reuses. The link /d/2/2 can only mean one post: the one merged last.
     */
    #[Test]
    public function merging_a_discussion_whose_id_was_used_before_takes_over_its_post_links()
    {
        $this->prepareDatabase([
            'fof_merged_posts' => [
                ['id' => 1, 'post_id' => 101, 'from_discussion_id' => 2, 'from_number' => 2, 'created_at' => $this->at(-60)],
            ],
        ]);

        $this->merge(1, [2], 'date');

        $response = $this->get('/d/2-source/2');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target/4', $response->getHeaderLine('Location'));
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

    private function get(string $path): ResponseInterface
    {
        return $this->send($this->request('GET', $path));
    }
}
