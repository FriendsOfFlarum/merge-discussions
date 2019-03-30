<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) 2019 FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions;

use Flarum\Api\Event\Serializing;
use Flarum\Extend;
use Illuminate\Events\Dispatcher;

return [
    (new Extend\Frontend('forum'))
        ->js(__DIR__.'/js/dist/forum.js')
        ->css(__DIR__.'/resources/less/forum.less'),
    (new Extend\Frontend('admin'))
        ->js(__DIR__.'/js/dist/admin.js'),
    new Extend\Locales(__DIR__.'/resources/locale'),
    (new Extend\Routes('api'))
        ->get('/discussions/{id}/merge', 'fof.merge-discussions.preview', Api\Controllers\MergePreviewController::class)
        ->post('/discussions/{id}/merge', 'fof.merge-discussions.run', Api\Controllers\MergeController::class),
    function (Dispatcher $events) {
        $events->subscribe(Listeners\CreatePostWhenMerged::class);
        $events->listen(Serializing::class, Listeners\AddApiAttributes::class);
    },
];
