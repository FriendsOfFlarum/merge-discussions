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
            ],
            Post::class => [
                ['id' => 1, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Target</p></t>'],
                ['id' => 3, 'discussion_id' => 3, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Hidden</p></t>'],
                ['id' => 4, 'discussion_id' => 4, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Private</p></t>'],
            ],
            // Discussions 2 and 5 were merged into 1 and no longer exist.
            self::REDIRECTIONS => [
                ['id' => 1, 'request_discussion_id' => 2, 'to_discussion_id' => 1, 'http_code' => 301, 'created_at' => $date],
                ['id' => 2, 'request_discussion_id' => 5, 'to_discussion_id' => 1, 'http_code' => 302, 'created_at' => $date],
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
        ];
    }

    #[Test]
    #[DataProvider('mergedDiscussionPaths')]
    public function merged_discussion_redirects_to_its_target(string $path)
    {
        $response = $this->get($path);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('/d/1', $response->getHeaderLine('Location'));
    }

    #[Test]
    public function redirect_uses_the_stored_http_code()
    {
        $response = $this->get('/d/5-old-title');

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertEquals('/d/1', $response->getHeaderLine('Location'));
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

        $this->assertCount(1, $lookups);
        $this->assertSame([$expectedId], $lookups[0]['bindings']);
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

    private function get(string $path): ResponseInterface
    {
        return $this->send($this->request('GET', $path));
    }

    private function redirectLookups(): array
    {
        return array_values(array_filter(
            $this->database()->getQueryLog(),
            fn (array $query) => str_contains($query['query'], self::REDIRECTIONS)
        ));
    }
}
