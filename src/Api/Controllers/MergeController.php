<?php

namespace FoF\MergeDiscussions\Api\Controllers;

use Flarum\Api\Controller\AbstractShowController;
use Flarum\Api\Serializer\DiscussionSerializer;
use FoF\MergeDiscussions\Api\Commands\MergeDiscussion;
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
     * @param Dispatcher $bus
     */
    public function __construct(Dispatcher $bus)
    {
        $this->bus = $bus;
    }

    /**
     * Get the data to be serialized and assigned to the response document.
     *
     * @param ServerRequestInterface $request
     * @param Document $document
     * @return mixed
     */
    protected function data(ServerRequestInterface $request, Document $document)
    {
        $actor = $request->getAttribute('actor');
        $title = Arr::get($request->getParsedBody(), 'title');
        $ids = Arr::get($request->getParsedBody(), 'ids');

        return $this->bus->dispatch(
            new MergeDiscussion($actor, $title, $ids)
        );
    }
}