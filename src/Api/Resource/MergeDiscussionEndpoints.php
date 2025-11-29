<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Api\Resource;

use Flarum\Api\Context;
use Flarum\Api\Endpoint;
use Flarum\Discussion\Discussion;
use FoF\MergeDiscussions\Commands\MergeDiscussion;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;

class MergeDiscussionEndpoints
{
    public function __construct(
        protected Dispatcher $bus,
        protected MergeDiscussionValidator $validator
    ) {
    }

    public function __invoke(): array
    {
        return [
            Endpoint\Endpoint::make('merge-preview')
                ->route('GET', '/{id}/merge-preview')
                ->authenticated()
                ->can('merge')
                ->action(function (Context $context) {
                    $actor = $context->getActor();
                    $discussion = $context->model;

                    // Use parameter names with uppercase letters to bypass JSON:API validation
                    // (validation allows params matching /[^a-z]/)
                    $queryParams = $context->request->getQueryParams();
                    $ids = $queryParams['byIds'] ?? null;
                    $ordering = $queryParams['byOrdering'] ?? 'date';

                    // Convert string to array if needed (e.g., "2" -> [2])
                    if (is_string($ids)) {
                        $ids = explode(',', $ids);
                    }

                    // Ensure IDs array is not empty after processing
                    if (empty($ids)) {
                        throw new \Flarum\Foundation\ValidationException([
                            'byIds' => 'The byIds parameter is required and must contain at least one discussion ID.'
                        ]);
                    }

                    // Remove empty strings from the array
                    $ids = array_filter($ids, fn($id) => $id !== '' && $id !== null);

                    if (empty($ids)) {
                        throw new \Flarum\Foundation\ValidationException([
                            'byIds' => 'No valid discussion IDs provided.'
                        ]);
                    }

                    $this->validator->assertValid([
                        'discussion_id'       => $discussion->id,
                        'merging_discussions' => $ids,
                    ]);

                    /** @var Discussion */
                    $discussion = $this->bus->dispatch(
                        new MergeDiscussion($actor, $discussion->id, $ids, $ordering, false)
                    );

                    return $discussion;
                })
                ->response(function (Context $context, Discussion $discussion): ResponseInterface {
                    // Use Flarum's serializer to properly serialize the discussion
                    $serializer = new \Flarum\Api\Serializer($context);

                    // Get the merged posts from the relationship set by the handler
                    $mergedPosts = $discussion->getRelation('posts');

                    // Serialize the discussion with posts included
                    $resource = $context->resource(
                        $context->collection->resource($discussion, $context)
                    );

                    $serializer->addPrimary($resource, $discussion, []);

                    [$primary, $included] = $serializer->serialize();

                    // Manually serialize each merged post using a separate serializer
                    $postResource = $context->api->getResource('posts');
                    $postSerializer = new \Flarum\Api\Serializer($context);

                    foreach ($mergedPosts as $post) {
                        $postSerializer->addPrimary($postResource, $post, []);
                    }

                    [$postPrimary, $postIncluded] = $postSerializer->serialize();

                    // Update the discussion's posts relationship data to include our merged posts
                    $primary[0]['relationships']['posts'] = ['data' => []];
                    foreach ($mergedPosts as $post) {
                        $primary[0]['relationships']['posts']['data'][] = [
                            'type' => 'posts',
                            'id' => (string) $post->id
                        ];
                    }

                    // Merge the post data into included
                    $included = array_merge($included, $postPrimary, $postIncluded);

                    return new JsonResponse([
                        'data' => $primary[0],
                        'included' => $included,
                    ]);
                }),

            Endpoint\Endpoint::make('merge')
                ->route('POST', '/{id}/merge')
                ->authenticated()
                ->can('merge')
                ->action(function (Context $context) {
                    $actor = $context->getActor();
                    $discussion = $context->model;

                    // Try to get from parsed body (raw format) or from JSON:API data structure
                    $body = $context->request->getParsedBody();
                    $ids = $body['ids'] ?? Arr::get($body, 'data.attributes.ids');
                    $ordering = $body['ordering'] ?? Arr::get($body, 'data.attributes.ordering', 'date');

                    $this->validator->assertValid([
                        'discussion_id'       => $discussion->id,
                        'merging_discussions' => $ids,
                    ]);

                    return $this->bus->dispatch(
                        new MergeDiscussion($actor, $discussion->id, $ids, $ordering)
                    );
                }),
        ];
    }
}
