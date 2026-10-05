<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Providers;

use Flarum\Discussion\Discussion;
use Flarum\Foundation\AbstractServiceProvider;

/**
 * Keeps a discussion's last_post_number at its highest-numbered comment.
 *
 * Core takes it from the newest comment, which is the same post as long as
 * numbers follow the timeline. Appending a merged discussion breaks that: its
 * posts are numbered after the target's newest reply, though older. Readers'
 * unread count runs to last_post_number, so every recount after a post is
 * restored, hidden or deleted would otherwise drop those posts back out.
 *
 * This runs while core saves the recount, so its own read-state clean-up on a
 * deletion already sees the corrected number.
 */
class LastPostNumberServiceProvider extends AbstractServiceProvider
{
    public function boot(): void
    {
        Discussion::saving(function (Discussion $discussion) {
            if (!$discussion->exists || !$discussion->isDirty(['comment_count', 'last_post_number'])) {
                return;
            }

            $highest = $discussion->comments()->max('number');

            if ($highest !== null) {
                $discussion->last_post_number = (int) $highest;
            }
        });
    }
}
