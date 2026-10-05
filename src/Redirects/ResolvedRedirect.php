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

final class ResolvedRedirect
{
    /**
     * @param string $url    the canonical URL to send the link to
     * @param int    $status the HTTP status the redirect was stored with
     */
    public function __construct(public readonly string $url, public readonly int $status)
    {
    }
}
