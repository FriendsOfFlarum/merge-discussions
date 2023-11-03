<?php

/*
 * This file is part of fof/merge-discussions.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FoF\MergeDiscussions\Api\Controllers;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Api\Serializer\DiscussionSerializer;
use Flarum\Http\RequestUtil;
use FoF\MergeDiscussions\Commands\MergeDiscussion;
use FoF\MergeDiscussions\Commands\MergeDiscussions;
use FoF\MergeDiscussions\Validators\MergeDiscussionValidator;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Support\Arr;
use Psr\Http\Message\ServerRequestInterface;
use Tobscure\JsonApi\Document;

class MergeController extends AbstractShowController
{
    /**
     * The serializer instance for this request.
     *
     * @var string
     */
    public $serializer = DiscussionSerializer::class;

    /**
     * @var Dispatcher
     */
    protected $bus;

    /**
     * @var MergeDiscussionValidator
     */
    protected $validator;

    public function __construct(Dispatcher $bus, MergeDiscussionValidator $validator)
    {
        $this->bus = $bus;
        $this->validator = $validator;
    }

    /**
     * Get the data to be serialized and assigned to the response document.
     *
     * @param ServerRequestInterface $request
     * @param Document               $document
     *
     * @return mixed
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = RequestUtil::getActor($request);
        $discussion = Arr::get($request->getQueryParams(), 'id');
        $ids = Arr::get($request->getParsedBody(), 'ids');
        $ordering = Arr::get($request->getParsedBody(), 'ordering');

        $this->validator->assertValid([
            'discussion_id'       => $discussion,
            'merging_discussions' => $ids,
        ]);

        return $this->bus->dispatch(
            new MergeDiscussions($actor, $discussion, $ids, $ordering)
        );
    }
}
