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
use Flarum\Extend;
use Flarum\Http\Middleware\HandleErrors;
use FoF\MergeDiscussions\Events\DiscussionWasMerged;
use FoF\MergeDiscussions\Posts\DiscussionMergePost;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
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
];
