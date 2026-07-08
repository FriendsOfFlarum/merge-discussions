<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Tests\integration;

use Carbon\Carbon;
use Flarum\Audit\AuditLog;
use Flarum\Audit\AuditLogger;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AuditTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    protected function setUp(): void
    {
        parent::setUp();

        // Lifecycle events fired outside the test transaction shouldn't create stray entries.
        AuditLogger::$testMode = true;

        $this->extension('flarum-audit', 'fof-merge-discussions');

        $date = Carbon::parse('2021-01-01T12:00:00+00:00');

        $this->prepareDatabase([
            'audit_log'       => [],
            Discussion::class => [
                ['id' => 10, 'title' => 'A', 'created_at' => $date, 'last_posted_at' => $date, 'first_post_id' => 1, 'comment_count' => 1],
                ['id' => 11, 'title' => 'B', 'created_at' => $date, 'last_posted_at' => $date, 'first_post_id' => 2, 'comment_count' => 1],
                ['id' => 12, 'title' => 'C', 'created_at' => $date, 'last_posted_at' => $date, 'first_post_id' => 3, 'comment_count' => 2],
            ],
            Post::class => [
                ['id' => 1, 'number' => 1, 'discussion_id' => 10, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>A</p></t>'],
                ['id' => 2, 'number' => 1, 'discussion_id' => 11, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>B</p></t>'],
                ['id' => 3, 'number' => 1, 'discussion_id' => 12, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>C1</p></t>'],
                ['id' => 4, 'number' => 2, 'discussion_id' => 12, 'created_at' => $date, 'type' => 'comment', 'content' => '<t><p>C2</p></t>'],
            ],
        ]);
    }

    #[Test]
    public function mergeSingle()
    {
        $response = $this->send($this->request('POST', '/api/discussions/10/merge', [
            'authenticatedAs' => 1,
            'json'            => [
                'ids' => '11',
            ],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody()->getContents());

        $log = AuditLog::query()->where('action', 'discussion.merged_into')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'discussion_id'           => 10,
            'original_discussion_ids' => [11],
            'post_count'              => 1,
        ], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);

        $log = AuditLog::query()->where('action', 'discussion.merged_away')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'discussion_id'     => 11,
            'new_discussion_id' => 10,
        ], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);
    }

    #[Test]
    public function mergeMultiple()
    {
        $response = $this->send($this->request('POST', '/api/discussions/10/merge', [
            'authenticatedAs' => 1,
            'json'            => [
                'ids' => ['11', '12'],
            ],
        ]));

        $this->assertEquals(200, $response->getStatusCode(), $response->getBody()->getContents());

        $log = AuditLog::query()->where('action', 'discussion.merged_into')->first();
        $this->assertNotNull($log);
        $this->assertEquals(1, $log->actor_id);
        $this->assertEquals([
            'discussion_id'           => 10,
            'original_discussion_ids' => [11, 12],
            'post_count'              => 3,
        ], $log->payload);
        $this->assertEquals('127.0.0.1', $log->ip_address);

        // Each merged-away discussion is logged; assert on the set of payloads rather than order.
        $awayPayloads = AuditLog::query()->where('action', 'discussion.merged_away')->get()->pluck('payload')->all();
        $this->assertContains(['discussion_id' => 11, 'new_discussion_id' => 10], $awayPayloads);
        $this->assertContains(['discussion_id' => 12, 'new_discussion_id' => 10], $awayPayloads);
    }
}
