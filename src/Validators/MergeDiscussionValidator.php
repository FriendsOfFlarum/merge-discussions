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

use Closure;
use Flarum\Foundation\AbstractValidator;
use Illuminate\Support\Arr;
use Illuminate\Validation\Validator;

class MergeDiscussionValidator extends AbstractValidator
{
    public const MISSING_DISCUSSIONS = 'One or more of the selected discussions do not exist.';

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
        'posts.filled'               => 'The selected discussion(s) have no posts to merge. Discussions must have at least one post to be merged.',
        'merging_discussions.filled' => 'You must select at least one discussion to merge.',
        'merging_discussions.exists' => self::MISSING_DISCUSSIONS,
    ];

    /**
     * The ids of the discussions to merge, as given in a request or a command,
     * as integers. An id that isn't a whole number, such as "1abc", becomes
     * null rather than the number PHP or the database would read in it.
     *
     * @return array<int|null>
     */
    public static function ids(mixed $ids): array
    {
        return array_values(array_map(
            fn (mixed $id) => match (true) {
                is_int($id) && $id > 0 => $id,
                is_string($id) && ctype_digit($id) && (int) $id > 0 => (int) $id,
                default => null,
            },
            Arr::wrap($ids)
        ));
    }

    protected function getRules(): array
    {
        $rules = $this->rules;

        $rules['merging_discussions'][] = function (string $attribute, mixed $value, Closure $fail, Validator $validator) {
            $target = (int) ($validator->getData()['discussion_id'] ?? 0);

            if (in_array($target, static::ids($value), true)) {
                $fail($this->translator->trans('fof-merge-discussions.api.error.into_itself'));
            }
        };

        return $rules;
    }

    protected function getMessages(): array
    {
        return $this->messages;
    }
}
