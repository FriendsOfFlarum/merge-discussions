<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Validators;

use Flarum\Foundation\AbstractValidator;

class MergeDiscussionValidator extends AbstractValidator
{
    protected array $rules = [
        'discussion_id' => [
            'int',
            'filled',
            'exists:discussions,id',
        ],
        'merging_discussions' => [
            'filled',
            'exists:discussions,id',
        ],
        'posts' => [
            'array',
            'filled',
        ],
    ];

    protected array $messages = [
        'posts.filled' => 'The selected discussion(s) have no posts to merge. Discussions must have at least one post to be merged.',
        'merging_discussions.filled' => 'You must select at least one discussion to merge.',
        'merging_discussions.exists' => 'One or more of the selected discussions do not exist.',
    ];
}
