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
 * The forum middleware that forwards merged-away discussion URLs.
 *
 * Every request here is a guest, the visitor that issue #84 broke: any 404
 * on a discussion route looks up a redirect, and the lookup must only ever
 * compare the integer id, never the raw "<id>-<slug>" route parameter.
 */
class RedirectionTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const REDIRECTIONS = 'fof_merge_discussions_redirections';

    public function setUp(): void
    {
        parent::setUp();

        $this->extension('fof-merge-discussions');

        $date = Carbon::parse('2024-01-01 00:00:00');

        $this->prepareDatabase([
            User::class       => [$this->normalUser()],
            Discussion::class => [
                ['id' => 1, 'title' => 'Target', 'slug' => 'target', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 3, 'title' => 'Hidden', 'slug' => 'hidden', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 3, 'comment_count' => 1, 'hidden_at' => $date, 'hidden_user_id' => 1],
                ['id' => 4, 'title' => 'Private', 'slug' => 'private', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 4, 'comment_count' => 1, 'is_private' => true],
                ['id' => 11, 'title' => 'Earlier target', 'slug' => 'earlier-target', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 11, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Target</p></t>'],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Hidden</p></t>'],
                ['id' => 4, 'discussion_id' => 4, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Private</p></t>'],
                ['id' => 11, 'discussion_id' => 11, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Earlier target</p></t>'],
            ],
            // Discussions 2 and 5 were merged into 1, and 6 into the hidden 3.
            // 8 was merged into 9, and 9 later into 1. 10 was merged into 11, then
            // the id was reused and merged into 1. None of them exist any more.
            self::REDIRECTIONS => [
                ['id' => 1, 'request_discussion_id' => 2, 'to_discussion_id' => 1, 'http_code' => 301, 'created_at' => $date],
                ['id' => 2, 'request_discussion_id' => 5, 'to_discussion_id' => 1, 'http_code' => 302, 'created_at' => $date],
                ['id' => 3, 'request_discussion_id' => 6, 'to_discussion_id' => 3, 'http_code' => 301, 'created_at' => $date],
                ['id' => 4, 'request_discussion_id' => 8, 'to_discussion_id' => 9, 'http_code' => 301, 'created_at' => $date],
                ['id' => 5, 'request_discussion_id' => 9, 'to_discussion_id' => 1, 'http_code' => 301, 'created_at' => $date],
                ['id' => 6, 'request_discussion_id' => 10, 'to_discussion_id' => 11, 'http_code' => 301, 'created_at' => $date],
                ['id' => 7, 'request_discussion_id' => 10, 'to_discussion_id' => 1, 'http_code' => 301, 'created_at' => $date],
            ],
        ]);
    }

    public static function mergedDiscussionPaths(): array
    {
        return [
            'bare id'           => ['/d/2'],
            'id and slug'       => ['/d/2-old-title'],
            'id and empty slug' => ['/d/2-'],
            'id and near post'  => ['/d/2/5'],
            'slug and near'     => ['/d/2-old-title/5'],
            'non-numeric near'  => ['/d/2-old-title/abc'],
        ];
    }

    /**
     * One permanent hop to the target's canonical URL, the same URL fof/seo
     * declares canonical: search engines transfer the old URL's standing to
     * it, and nothing lands on a second redirect.
     */
    #[Test]
    #[DataProvider('mergedDiscussionPaths')]
    public function merged_discussion_redirects_to_its_target(string $path)
    {
        $response = $this->get($path);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target', $response->getHeaderLine('Location'));
    }

    /**
     * Inside the forum, the request path has already lost the install's base
     * path, so a Location built from it pointed outside a forum installed in a
     * subdirectory.
     */
    #[Test]
    public function redirect_stays_inside_a_forum_installed_in_a_subdirectory()
    {
        $this->config('url', 'http://localhost/forum');

        $response = $this->get('/forum/d/2-old-title');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/forum/d/1-target', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function redirect_uses_the_stored_http_code()
    {
        $response = $this->get('/d/5-old-title');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target', $response->getHeaderLine('Location'));
    }

    /**
     * A target merged away in turn would otherwise cost the old URL a second
     * hop, or a 404 once the intermediate discussion is gone.
     */
    #[Test]
    public function discussion_merged_into_one_merged_away_since_redirects_in_one_hop()
    {
        $response = $this->get('/d/8-first-title');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target', $response->getHeaderLine('Location'));
    }

    /**
     * MySQL 5.7 hands out the highest id again after a restart, so the same id
     * can be merged away twice. Its links mean the discussion merged last.
     */
    #[Test]
    public function reused_discussion_id_redirects_to_the_latest_merge_target()
    {
        $response = $this->get('/d/10');

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target', $response->getHeaderLine('Location'));
    }

    /**
     * The canonical URL carries the target's title, so redirecting someone who
     * cannot see the target would leak it, and send search engines to a page
     * they cannot read. They get the 404 they would have had anyway.
     */
    #[Test]
    public function discussion_merged_into_one_the_visitor_cannot_see_stays_a_404()
    {
        $response = $this->get('/d/6-old-title');

        $this->assertEquals(404, $response->getStatusCode());
        $this->assertSame('', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function discussion_merged_into_a_hidden_one_redirects_those_who_can_see_it()
    {
        $response = $this->get('/d/6-old-title', 1);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/3-hidden', $response->getHeaderLine('Location'));
    }

    public static function unknownDiscussionPaths(): array
    {
        return [
            'bare id'          => ['/d/999999'],
            'id and slug'      => ['/d/999999-made-up'],
            'slug and near'    => ['/d/999999-made-up/3'],
            'slug with digits' => ['/d/999999-2024-recap'],
        ];
    }

    #[Test]
    #[DataProvider('unknownDiscussionPaths')]
    public function unknown_discussion_stays_a_404(string $path)
    {
        $this->assertEquals(404, $this->get($path)->getStatusCode());
    }

    public static function restrictedDiscussionPaths(): array
    {
        return [
            'hidden, bare id'         => ['/d/3'],
            'hidden, canonical slug'  => ['/d/3-hidden'],
            'private, canonical slug' => ['/d/4-private'],
        ];
    }

    #[Test]
    #[DataProvider('restrictedDiscussionPaths')]
    public function discussion_hidden_from_guests_stays_a_404(string $path)
    {
        $this->assertEquals(404, $this->get($path)->getStatusCode());
    }

    public static function lookupIds(): array
    {
        return [
            'unknown, with slug'          => ['/d/999999-made-up', 999999],
            'unknown, slug with digits'   => ['/d/999999-2024-recap', 999999],
            'hidden, with canonical slug' => ['/d/3-hidden', 3],
            'merged, with slug'           => ['/d/2-old-title', 2],
        ];
    }

    /**
     * MySQL and MariaDB truncate "999999-made-up" to 999999 and carry on, so
     * the behavioural tests above pass there even with the bug. Asserting the
     * bound value catches it on every database.
     */
    #[Test]
    #[DataProvider('lookupIds')]
    public function lookup_compares_only_the_integer_id(string $path, int $expectedId)
    {
        $this->database()->enableQueryLog();

        $this->get($path);

        $lookups = $this->redirectLookups();

        // The requested id comes first; following a merge chain adds lookups
        // for its targets, which must be integers too.
        $this->assertNotEmpty($lookups);
        $this->assertSame([$expectedId], $lookups[0]['bindings']);

        foreach ($lookups as $lookup) {
            $this->assertContainsOnlyInt($lookup['bindings']);
        }
    }

    public static function oversizedPostNumbers(): array
    {
        return [
            'past a 4-byte integer' => ['/d/2-old-title/2147483648'],
            'past a 64-bit integer' => ['/d/2-old-title/99999999999999999999'],
        ];
    }

    /**
     * No post has such a number, and PostgreSQL rejects comparing one against
     * the 4-byte column, so it must not reach the post lookup at all; MySQL
     * compares it without complaint, hence the bound values are checked too.
     */
    #[Test]
    #[DataProvider('oversizedPostNumbers')]
    public function oversized_post_number_falls_back_to_the_discussion(string $path)
    {
        $this->database()->enableQueryLog();

        $response = $this->get($path);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('http://localhost/d/1-target', $response->getHeaderLine('Location'));

        foreach ($this->queriesOn('fof_merged_posts') as $query) {
            foreach ($query['bindings'] as $binding) {
                $this->assertLessThanOrEqual(2147483647, $binding);
            }
        }
    }

    #[Test]
    public function visible_discussion_renders_without_a_redirect_lookup()
    {
        $this->database()->enableQueryLog();

        $this->assertEquals(200, $this->get('/d/1-target')->getStatusCode());
        $this->assertCount(0, $this->redirectLookups());
    }

    #[Test]
    public function a_404_outside_discussion_routes_does_not_look_up_redirects()
    {
        $this->database()->enableQueryLog();

        $this->assertEquals(404, $this->get('/u/nobody')->getStatusCode());
        $this->assertCount(0, $this->redirectLookups());
    }

    private function get(string $path, ?int $userId = null): ResponseInterface
    {
        return $this->send($this->request('GET', $path, $userId ? ['authenticatedAs' => $userId] : []));
    }

    private function redirectLookups(): array
    {
        return $this->queriesOn(self::REDIRECTIONS);
    }

    private function queriesOn(string $table): array
    {
        return array_values(array_filter(
            $this->database()->getQueryLog(),
            fn (array $query) => str_contains($query['query'], $table)
        ));
    }
}
