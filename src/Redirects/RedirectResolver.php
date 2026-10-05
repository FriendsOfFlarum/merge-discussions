<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Redirects;

use Flarum\Discussion\Discussion;
use Flarum\Http\SlugManager;
use Flarum\Http\UrlGenerator;
use Flarum\User\User;
use FoF\MergeDiscussions\Models\MergedPost;
use FoF\MergeDiscussions\Models\Redirection;

/**
 * Where a link into a merged-away discussion, or to a post in it, goes now.
 */
class RedirectResolver
{
    /**
     * Merges a chain is followed through. Merged-away discussions are deleted,
     * so a chain cannot loop; this only bounds a corrupted table.
     */
    protected const MAX_HOPS = 10;

    public function __construct(protected UrlGenerator $url, protected SlugManager $slugManager)
    {
    }

    /**
     * @param string $discussion the requested discussion, as "<id>" or "<id>-<slug>"
     * @param mixed  $near       the post number linked to, if any
     */
    public function resolve(string $discussion, mixed $near, User $actor): ?ResolvedRedirect
    {
        // Compare only the id: PostgreSQL rejects the raw string against the
        // integer column.
        if (!preg_match('/^\d+/', $discussion, $matches)) {
            return null;
        }

        $id = (int) $matches[0];
        $redirect = Redirection::request($id);

        if (!$redirect) {
            return null;
        }

        [$targetId, $number] = $this->locate($id, $redirect, $near);

        // Only redirect to a discussion the actor can see: the canonical URL
        // carries its title.
        $target = Discussion::whereVisibleTo($actor)->find($targetId);

        if (!$target) {
            return null;
        }

        // The target's canonical URL, from the configured base URL rather than
        // the request, whose path has already lost the install's base path.
        $parameters = ['id' => $this->slugManager->forResource(Discussion::class)->toSlug($target)];

        if ($number !== null) {
            $parameters['near'] = $number;
        }

        return new ResolvedRedirect($this->url->to('forum')->route('discussion', $parameters), $redirect->http_code);
    }

    /**
     * Where a merged-away discussion, or the post linked to in it, is now.
     *
     * @return array{int, int|null} the discussion, and the post number within it to link to
     */
    protected function locate(int $id, Redirection $redirect, mixed $near): array
    {
        // A link to one post follows that post, wherever it has been moved since.
        if (is_string($near) && preg_match('/^\d+$/', $near)) {
            $post = MergedPost::at($id, (int) $near)?->post;

            if ($post) {
                return [$post->discussion_id, $post->number];
            }
        }

        // The target may have been merged away since. Follow the chain to the
        // discussion that still exists, so the old URL takes one hop.
        $targetId = $redirect->to_discussion_id;

        for ($hops = 0; $hops < self::MAX_HOPS && ($next = Redirection::request($targetId)); $hops++) {
            $targetId = $next->to_discussion_id;
        }

        return [$targetId, null];
    }
}
