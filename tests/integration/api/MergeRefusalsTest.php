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
use Flarum\Locale\LocaleManager;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;
use RuntimeException;

/**
 * Merges that cannot go ahead are refused with a message that says why, and
 * leave everything as it was.
 */
class MergeRefusalsTest extends TestCase
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
                ['id' => 1, 'title' => 'Target', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 101, 'comment_count' => 2],
                ['id' => 2, 'title' => 'Source', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 201, 'comment_count' => 1],
                // No posts at all.
                ['id' => 5, 'title' => 'Empty', 'user_id' => 2, 'created_at' => $date, 'comment_count' => 0],
            ],
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>101</p></t>'],
                ['id' => 102, 'discussion_id' => 1, 'number' => 2, 'user_id' => 2, 'created_at' => $date->copy()->addMinute(), 'type' => 'comment', 'content' => '<t><p>102</p></t>'],
                ['id' => 201, 'discussion_id' => 2, 'number' => 1, 'user_id' => 2, 'created_at' => $date->copy()->addMinutes(2), 'type' => 'comment', 'content' => '<t><p>201</p></t>'],
            ],
        ]);
    }

    #[Test]
    public function merging_a_discussion_into_itself_is_refused_before_anything_changes()
    {
        $response = $this->merge(1, [1]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('/data/attributes/merging_discussions', $this->error($response)['source']['pointer']);
        $this->assertSame('A discussion cannot be merged into itself.', $this->error($response)['detail']);

        $this->assertSame([1, 2], Post::query()->where('discussion_id', 1)->orderBy('number')->pluck('number')->all());
        $this->assertSame(0, $this->database()->table('fof_merged_posts')->count());
    }

    #[Test]
    public function merging_a_discussion_without_posts_is_refused()
    {
        $response = $this->merge(1, [5]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('/data/attributes/posts', $this->error($response)['source']['pointer']);
        $this->assertNotNull(Discussion::find(5));
    }

    /**
     * The client is told what failed, not the internal error behind it.
     */
    #[Test]
    public function a_failure_while_deleting_the_merged_discussions_is_reported_without_its_internals()
    {
        $this->extend((new Extend\Event())->listen(Deleting::class, function () {
            throw new RuntimeException('Internal detail of the failure');
        }));

        $this->app()->getContainer()->make(LocaleManager::class)
            ->addTranslations('en', __DIR__.'/../../../resources/locale/en.yml');

        $response = $this->merge(1, [2]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('Failed to delete empty discussions.', $this->error($response)['detail']);
        $this->assertNotNull(Discussion::find(2));
    }

    private function merge(int $target, array $sources): ResponseInterface
    {
        return $this->send($this->request('POST', "/api/discussions/$target/merge", [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => $sources, 'ordering' => 'date'],
        ]));
    }

    private function error(ResponseInterface $response): array
    {
        $response->getBody()->rewind();

        return json_decode((string) $response->getBody(), true)['errors'][0];
    }
}
