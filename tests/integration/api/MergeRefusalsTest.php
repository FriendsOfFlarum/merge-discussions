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
use Flarum\Foundation\ValidationException;
use Flarum\Locale\LocaleManager;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\Exception\PermissionDeniedException;
use Flarum\User\User;
use FoF\MergeDiscussions\Models\Redirection;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
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
        $this->loadTranslations();

        $response = $this->merge(1, [1]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('/data/attributes/merging_discussions', $this->error($response)['source']['pointer']);
        $this->assertSame('A discussion cannot be merged into itself.', $this->error($response)['detail']);

        $this->assertSame([1, 2], Post::query()->where('discussion_id', 1)->orderBy('number')->pluck('number')->all());
        $this->assertSame(0, $this->database()->table('fof_merged_posts')->count());
    }

    #[Test]
    public function previewing_a_discussion_merged_into_itself_is_refused()
    {
        $this->loadTranslations();

        $response = $this->send(
            $this->request('GET', '/api/discussions/1/merge-preview', ['authenticatedAs' => self::MODERATOR])
                ->withQueryParams(['byIds' => '1', 'byOrdering' => 'date'])
        );

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('/data/attributes/merging_discussions', $this->error($response)['source']['pointer']);
        $this->assertSame('A discussion cannot be merged into itself.', $this->error($response)['detail']);
    }

    /**
     * An id that isn't a whole number names no discussion, even one that PHP or
     * the database would read as a number, such as "1abc" for this discussion.
     */
    #[Test]
    public function an_id_that_is_not_a_number_is_refused_as_missing()
    {
        $response = $this->merge(1, ['1abc']);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame(MergeDiscussionValidator::MISSING_DISCUSSIONS, $this->error($response)['detail']);
        $this->assertNothingChanged();
    }

    #[Test]
    public function a_discussion_named_twice_is_merged_once()
    {
        $response = $this->merge(1, ['2', '02']);

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());
        $this->assertSame(1, Post::query()->findOrFail(201)->discussion_id);
        $this->assertNull(Discussion::find(2));
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

        $this->loadTranslations();

        $response = $this->merge(1, [2]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('Failed to delete empty discussions.', $this->error($response)['detail']);
        $this->assertNothingChanged();
    }

    /**
     * Another extension refusing to let a discussion go, with its reason: the
     * client gets that refusal as it was, not a generic failure.
     */
    #[Test]
    public function a_refusal_while_deleting_the_merged_discussions_reaches_the_client_with_its_reason()
    {
        $this->extend((new Extend\Event())->listen(Deleting::class, function () {
            throw new ValidationException(['discussion' => 'Archived discussions cannot be deleted.']);
        }));

        $response = $this->merge(1, [2]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('Archived discussions cannot be deleted.', $this->error($response)['detail']);
        $this->assertNothingChanged();
    }

    #[Test]
    public function a_permission_refusal_while_deleting_the_merged_discussions_stays_a_403()
    {
        $this->extend((new Extend\Event())->listen(Deleting::class, function () {
            throw new PermissionDeniedException();
        }));

        $response = $this->merge(1, [2]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertNothingChanged();
    }

    #[Test]
    public function a_failure_while_redirecting_the_merged_discussions_is_reported_as_such()
    {
        $this->extend((new Extend\Event())->listen('eloquent.saving: '.Redirection::class, function () {
            throw new RuntimeException('Internal detail of the failure');
        }));

        $this->loadTranslations();

        $response = $this->merge(1, [2]);

        $this->assertEquals(422, $response->getStatusCode());
        $this->assertSame('Failed to redirect the merged discussions.', $this->error($response)['detail']);
        $this->assertNothingChanged();
    }

    /**
     * The merge runs in one transaction: a failure part-way leaves no trace.
     */
    private function assertNothingChanged(): void
    {
        $this->assertNotNull(Discussion::find(2));
        $this->assertSame(2, Post::query()->findOrFail(201)->discussion_id);
        $this->assertSame([1, 2], Post::query()->where('discussion_id', 1)->orderBy('number')->pluck('number')->all());
        $this->assertSame(0, $this->database()->table('fof_merged_posts')->count());
        $this->assertSame(0, $this->database()->table('fof_merge_discussions_redirections')->count());
    }

    /**
     * flarum/testing 2.0 enables extensions before their extenders run, so
     * Extend\Locales never adds the extension's translations in tests (fixed in
     * flarum/testing 2.1). Without this the messages come back as raw keys.
     * Call it after any extend(): it boots the app.
     */
    private function loadTranslations(): void
    {
        $this->app()->getContainer()->make(LocaleManager::class)
            ->addTranslations('en', __DIR__.'/../../../resources/locale/en.yml');
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
