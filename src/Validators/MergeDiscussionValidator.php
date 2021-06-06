<?php


namespace FoF\MergeDiscussions\Validators;


use Flarum\Foundation\AbstractValidator;

class MergeDiscussionValidator extends AbstractValidator
{
    protected $rules = [
        'discussion_id' => [
            'int',
            'filled',
            'exists:discussions,id'
        ],
        'merging_discussions' => [
            'filled',
            'exists:discussions,id'
        ],
        'posts' => [
            'array',
            'filled'
        ],
    ];
}
