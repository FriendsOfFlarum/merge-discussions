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
use FoF\MergeDiscussions\Models\Redirection as Redirect;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Redirection implements MiddlewareInterface
{
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

            $redirect = Redirect::request((int) $matches[0]);

            if (!$redirect) {
                return $response;
            }

            // Only redirect to a discussion the visitor can see: the canonical URL
            // carries its title.
            $target = Discussion::whereVisibleTo(RequestUtil::getActor($request))->find($redirect->to_discussion_id);

            if (!$target) {
                return $response;
            }

            // Go straight to the target's canonical URL. The request path has
            // already lost the install's base path, so it cannot be reused.
            $location = $this->url->to('forum')->route('discussion', [
                'id' => $this->slugManager->forResource(Discussion::class)->toSlug($target),
            ]);

            // Send a redirect response to the client with the predefined http code.
            return new Response\RedirectResponse(
                $location,
                $redirect->http_code
            );
        }

        return $response;
    }

    protected function getDispatcher(RouteCollection $routes): GroupCountBased
    {
        return new GroupCountBased($routes->getRouteData());
    }
}
