<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Flarum\Database\Migration;
use Illuminate\Database\Schema\Blueprint;

// Not "fof_merge_discussions_…" like the redirections table: with that name the
// generated unique index name would exceed the 55 characters Flarum leaves for
// identifiers before the table prefix.
return Migration::createTable('fof_merged_posts', function (Blueprint $table) {
    $table->increments('id');
    $table->unsignedInteger('post_id');
    $table->unsignedInteger('from_discussion_id');
    $table->unsignedInteger('from_number');
    $table->timestamp('created_at');

    $table->unique(['from_discussion_id', 'from_number']);
    $table->foreign('post_id')->references('id')->on('posts')->cascadeOnDelete();
});
