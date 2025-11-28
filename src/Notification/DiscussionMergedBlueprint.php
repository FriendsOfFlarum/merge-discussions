<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Notification;

use Flarum\Notification\AlertableInterface;
use Flarum\Discussion\Discussion;
use Flarum\Notification\Blueprint\BlueprintInterface;
use Flarum\Notification\MailableInterface;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Symfony\Contracts\Translation\TranslatorInterface;

class DiscussionMergedBlueprint implements BlueprintInterface, MailableInterface, AlertableInterface
{
    public function __construct(public Discussion $discussion, public User $actor, public array $mergedDiscussion)
    {
    }

    /**
     * Get the user that sent the notification.
     */
    public function getFromUser(): ?\Flarum\User\User
    {
        return $this->actor;
    }

    /**
     * Get the model that is the subject of this activity.
     */
    public function getSubject(): ?\Flarum\Database\AbstractModel
    {
        return $this->discussion;
    }

    /**
     * Get the data to be stored in the notification.
     */
    public function getData(): mixed
    {
        return [
            'merged_title' => Arr::get($this->mergedDiscussion, 'title'),
            'merged_id'    => Arr::get($this->mergedDiscussion, 'id'),
        ];
    }

    /**
     * Get the serialized type of this activity.
     *
     * @return string
     */
    public static function getType(): string
    {
        return 'discussionMerged';
    }

    /**
     * Get the name of the model class for the subject of this activity.
     *
     * @return string
     */
    public static function getSubjectModel(): string
    {
        return Discussion::class;
    }

    /**
     * Get the name of the view to construct a notification email with.
     *
     * @return array
     */
    public function getEmailViews(): array
    {
        return ['text' => 'fof-merge-discussions::emails.discussionMerged'];
    }

    /**
     * Get the subject line for the notification email.
     *
     * @return string
     */
    public function getEmailSubject(\Flarum\Locale\TranslatorInterface $translator): string
    {
        return $translator->trans('fof-merge-discussions.email.merged.subject', [
            '{display_name}'            => $this->actor->display_name,
            '{discussion_title}'        => $this->discussion->title,
            '{merged_discussion_title}' => Arr::get($this->mergedDiscussion, 'title'),
        ]);
    }
}
