<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Middleware;

use FastRoute\Dispatcher\GroupCountBased;
use Flarum\Discussion\Discussion;
use Flarum\Http\RequestUtil;
use Flarum\Http\RouteCollection;
use Flarum\Http\SlugManager;
use Flarum\Http\UrlGenerator;
use FoF\MergeDiscussions\Models\MergedPost;
use FoF\MergeDiscussions\Models\Redirection as Redirect;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Redirection implements MiddlewareInterface
{
    /**
     * Merges a chain is followed through. Merged-away discussions are deleted,
     * so a chain cannot loop; this only bounds a corrupted table.
     */
    protected const MAX_HOPS = 10;

    public function __construct(protected UrlGenerator $url, protected SlugManager $slugManager)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        // In case of a valid 404 response start identifying whether we need to redirect.
        if ($response instanceof Response
            && $response->getStatusCode() === 404) {
            /** @var RouteCollection $routes */
            $routes = resolve('flarum.forum.routes');

            $dispatcher = $this->getDispatcher($routes);

            // Use the route dispatcher to identify routing information.
            $route = $dispatcher->dispatch(
                $request->getMethod(),
                $request->getUri()->getPath() ?: '/'
            );

            // Identify the requested route.
            if (Arr::get($route, '1.name') !== 'discussion') {
                return $response;
            }

            // The route parameter is "<id>" or "<id>-<slug>". Compare only the id:
            // PostgreSQL rejects the raw string against the integer column.
            if (!preg_match('/^\d+/', (string) Arr::get($route, '2.id'), $matches)) {
                return $response;
            }

            $id = (int) $matches[0];
            $redirect = Redirect::request($id);

            if (!$redirect) {
                return $response;
            }

            [$targetId, $near] = $this->locate($id, $redirect, Arr::get($route, '2.near'));

            // Only redirect to a discussion the visitor can see: the canonical URL
            // carries its title.
            $target = Discussion::whereVisibleTo(RequestUtil::getActor($request))->find($targetId);

            if (!$target) {
                return $response;
            }

            // Go straight to the target's canonical URL. The request path has
            // already lost the install's base path, so it cannot be reused.
            $parameters = ['id' => $this->slugManager->forResource(Discussion::class)->toSlug($target)];

            if ($near !== null) {
                $parameters['near'] = $near;
            }

            $location = $this->url->to('forum')->route('discussion', $parameters);

            // Send a redirect response to the client with the predefined http code.
            return new Response\RedirectResponse(
                $location,
                $redirect->http_code
            );
        }

        return $response;
    }

    /**
     * Where a merged-away discussion, or the post linked to in it, is now.
     *
     * @return array{int, int|null} the discussion, and the post number within it to link to
     */
    protected function locate(int $id, Redirect $redirect, mixed $near): array
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

        for ($hops = 0; $hops < self::MAX_HOPS && ($next = Redirect::request($targetId)); $hops++) {
            $targetId = $next->to_discussion_id;
        }

        return [$targetId, null];
    }

    protected function getDispatcher(RouteCollection $routes): GroupCountBased
    {
        return new GroupCountBased($routes->getRouteData());
    }
}
