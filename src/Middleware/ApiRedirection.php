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
use Flarum\Http\RequestUtil;
use Flarum\Http\RouteCollection;
use FoF\MergeDiscussions\Redirects\RedirectResolver;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * The API counterpart of the forum's Redirection middleware.
 *
 * Following a link inside the forum loads the discussion through the API, so
 * it never meets the forum's redirect. The 404 stays, since the discussion is
 * gone, but its meta carries the URL the forum would have redirected to.
 */
class ApiRedirection implements MiddlewareInterface
{
    public function __construct(protected RedirectResolver $redirects)
    {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if (!$response instanceof JsonResponse || $response->getStatusCode() !== 404) {
            return $response;
        }

        /** @var RouteCollection $routes */
        $routes = resolve('flarum.api.routes');

        $route = (new GroupCountBased($routes->getRouteData()))->dispatch(
            $request->getMethod(),
            $request->getUri()->getPath() ?: '/'
        );

        if (Arr::get($route, '1.name') !== 'discussions.show') {
            return $response;
        }

        $redirect = $this->redirects->resolve(
            (string) Arr::get($route, '2.id'),
            Arr::get($request->getQueryParams(), 'page.near'),
            RequestUtil::getActor($request)
        );

        if (!$redirect) {
            return $response;
        }

        $document = $response->getPayload();

        if (!is_array($document)) {
            return $response;
        }

        $document['meta']['fof-merge-discussions']['redirect'] = $redirect->url;

        return $response->withPayload($document);
    }
}
