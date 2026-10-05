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
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;
use FoF\MergeDiscussions\Commands\MergeDiscussion;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\Attributes\Test;
use Psr\Http\Message\ResponseInterface;

/**
 * The discussions merged into a target are as much a part of the request as
 * the target: being allowed to merge into one discussion must not give access
 * to any other.
 */
class MergeSourceAccessTest extends TestCase
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
                ['id' => 1, 'title' => 'Target', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 101, 'comment_count' => 1],
                ['id' => 2, 'title' => 'Source', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 201, 'comment_count' => 1],
                // Private: no one sees it without an extension that grants it.
                ['id' => 3, 'title' => 'Private conversation', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 301, 'comment_count' => 1, 'is_private' => true],
                // Visible, but the moderator may not merge it (DenyMergingDiscussionFour).
                ['id' => 4, 'title' => 'Elsewhere', 'user_id' => 2, 'created_at' => $date, 'first_post_id' => 401, 'comment_count' => 1],
            ],
            Post::class => [
                ['id' => 101, 'discussion_id' => 1, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Target post</p></t>'],
                ['id' => 201, 'discussion_id' => 2, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Visible reply</p></t>'],
                // Private, e.g. awaiting approval: the moderator cannot see it.
                ['id' => 202, 'discussion_id' => 2, 'number' => 2, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Unapproved words</p></t>', 'is_private' => true],
                ['id' => 301, 'discussion_id' => 3, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Private words</p></t>'],
                ['id' => 401, 'discussion_id' => 4, 'number' => 1, 'user_id' => 2, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>Elsewhere</p></t>'],
            ],
        ]);
    }

    /**
     * Refused exactly as a discussion that does not exist, so the response
     * gives nothing away about it.
     */
    #[Test]
    public function merging_a_discussion_the_moderator_cannot_see_is_refused_as_if_it_did_not_exist()
    {
        $unknown = $this->merge(1, [999]);
        $invisible = $this->merge(1, [3]);

        $this->assertEquals(422, $invisible->getStatusCode());
        $this->assertSame((string) $unknown->getBody(), (string) $invisible->getBody());

        $this->assertEquals(3, Post::query()->find(301)->discussion_id);
    }

    #[Test]
    public function previewing_a_discussion_the_moderator_cannot_see_is_refused_as_if_it_did_not_exist()
    {
        $unknown = $this->preview(1, [999]);
        $invisible = $this->preview(1, [3]);

        $this->assertEquals(422, $invisible->getStatusCode());
        $this->assertSame((string) $unknown->getBody(), (string) $invisible->getBody());
        $this->assertStringNotContainsString('Private words', (string) $invisible->getBody());
    }

    /**
     * Allowed to merge into the target says nothing about the discussions
     * merged into it, e.g. when the permission is scoped to some tags.
     */
    #[Test]
    public function merging_a_discussion_the_moderator_may_not_merge_is_refused()
    {
        $this->extend((new Extend\Policy())->modelPolicy(Discussion::class, DenyMergingDiscussionFour::class));

        $response = $this->merge(1, [4]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals(4, Post::query()->find(401)->discussion_id);
    }

    #[Test]
    public function previewing_a_discussion_the_moderator_may_not_merge_is_refused()
    {
        $this->extend((new Extend\Policy())->modelPolicy(Discussion::class, DenyMergingDiscussionFour::class));

        $response = $this->preview(1, [4]);

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertStringNotContainsString('Elsewhere', (string) $response->getBody());
    }

    #[Test]
    public function preview_shows_only_posts_the_moderator_can_see()
    {
        $response = $this->preview(1, [2]);

        $this->assertEquals(200, $response->getStatusCode(), (string) $response->getBody());

        $document = json_decode((string) $response->getBody(), true);
        $listed = array_column($document['data']['relationships']['posts']['data'], 'id');
        $included = array_column(array_filter($document['included'], fn (array $resource) => $resource['type'] === 'posts'), 'id');

        $this->assertEqualsCanonicalizing(['101', '201'], $listed);
        $this->assertEqualsCanonicalizing(['101', '201'], $included);
        $this->assertStringNotContainsString('Unapproved words', (string) $response->getBody());
    }

    /**
     * The endpoints only reach a target the actor can see, but the command can
     * be dispatched from anywhere.
     */
    #[Test]
    public function the_merge_command_refuses_a_target_the_actor_cannot_see()
    {
        $bus = $this->app()->getContainer()->make(Dispatcher::class);
        $moderator = User::query()->findOrFail(self::MODERATOR);

        $this->expectException(ModelNotFoundException::class);

        $bus->dispatch(new MergeDiscussion($moderator, 3, [2], 'date'));
    }

    /**
     * Hiding them from the preview must not lose them: the merged-away
     * discussion is deleted.
     */
    #[Test]
    public function merge_still_moves_posts_the_moderator_cannot_see()
    {
        $this->assertEquals(200, $this->merge(1, [2])->getStatusCode());

        $this->assertEquals(1, Post::query()->find(202)->discussion_id);
    }

    private function merge(int $target, array $sources): ResponseInterface
    {
        return $this->send($this->request('POST', "/api/discussions/$target/merge", [
            'authenticatedAs' => self::MODERATOR,
            'json'            => ['ids' => $sources, 'ordering' => 'date'],
        ]));
    }

    private function preview(int $target, array $sources): ResponseInterface
    {
        return $this->send(
            $this->request('GET', "/api/discussions/$target/merge-preview", ['authenticatedAs' => self::MODERATOR])
                ->withQueryParams(['byIds' => implode(',', $sources), 'byOrdering' => 'date'])
        );
    }
}

/**
 * Stands in for a merge permission scoped to some discussions, e.g. by tag.
 */
class DenyMergingDiscussionFour extends AbstractPolicy
{
    public function merge(User $actor, Discussion $discussion): ?string
    {
        return $discussion->id === 4 ? $this->deny() : null;
    }
}
