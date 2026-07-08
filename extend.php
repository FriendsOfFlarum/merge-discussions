<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions;

use Flarum\Api\Resource\DiscussionResource;
use Flarum\Api\Schema;
use Flarum\Audit\AuditLogger;
use Flarum\Extend;
use Flarum\Http\Middleware\HandleErrors;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Posts\DiscussionMergePost;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->jsDirectory(__DIR__.'/js/dist/forum')
        ->css(__DIR__.'/resources/less/forum.less'),

    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    new Extend\Locales(__DIR__.'/resources/locale'),

    (new Extend\ApiResource(DiscussionResource::class))
        ->endpoints(Api\Resource\MergeDiscussionEndpoints::class),

    (new Extend\Post())
        ->type(DiscussionMergePost::class),

    (new Extend\Event())
        ->listen(DiscussionWasMerged::class, Listeners\CreatePostWhenMerged::class)
        ->listen(DiscussionWasMerged::class, Listeners\NotifyParticipantsWhenMerged::class),

    (new Extend\ApiResource(DiscussionResource::class))
        ->fields(fn () => [
            Schema\Boolean::make('canMerge')
                ->get(fn ($discussion, $context) => $context->getActor()->can('merge', $discussion)),
        ]),

    (new Extend\Settings())
        ->default('fof-merge-discussions.search_limit', 4)
        ->serializeToForum('fof-merge-discussions.search_limit', 'fof-merge-discussions.search_limit', 'intVal'),

    (new Extend\View())
        ->namespace('fof-merge-discussions', __DIR__.'/resources/views'),

    (new Extend\Notification())
        ->type(Notification\DiscussionMergedBlueprint::class, ['alert', 'email']),

    (new Extend\Middleware('forum'))
        ->insertBefore(HandleErrors::class, Middleware\Redirection::class),

    (new Extend\Conditional())
        ->whenExtensionEnabled('flarum-audit', fn () => [
            (new \Flarum\Audit\Extend\Audit())
                ->group('fof-merge-discussions')
                ->register('discussion.merged_away', 'discussion.merged_into')
                ->using(function () {
                    // Merge dispatches multiple logs per event, so it uses a raw listener.
                    resolve('events')->listen(DiscussionWasMerged::class, function (DiscussionWasMerged $event) {
                        foreach ($event->mergedDiscussions as $discussion) {
                            AuditLogger::log('discussion.merged_away', [
                                'discussion_id'     => $discussion->id,
                                'new_discussion_id' => $event->discussion->id,
                            ]);
                        }

                        AuditLogger::log('discussion.merged_into', [
                            'discussion_id'           => $event->discussion->id,
                            'original_discussion_ids' => $event->mergedDiscussions->pluck('id')->all(),
                            'post_count'              => $event->posts->count(),
                        ]);
                    });
                }),
        ]),
];
