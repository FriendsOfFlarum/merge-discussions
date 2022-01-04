<?php

namespace FoF\MergeDiscussions\Models;

use Carbon\Carbon;
use Flarum\Database\AbstractModel;
use Flarum\Discussion\Discussion;

/**
 * @property int $id
 * @property int $request_discussion_id
 * @property int $to_discussion_id
 * @property int $http_code
 * @property Carbon $created_at
 */
class Redirection extends AbstractModel
{
    public $timestamps = true;

    protected $table = 'fof_merge_discussions_redirections';

    public static function build(Discussion $request, Discussion $target, int $httpCode = 301): self
    {
        $redirection = new self;
        $redirection->request_discussion_id = $request->id;
        $redirection->to_discussion_id = $target->id;
        $redirection->http_code = $httpCode;

        $redirection->save();

        return $redirection;
    }

    public static function request($id): ?self
    {
        return self::query()
            ->where('request_discussion_id', $id)
            ->first();
    }

    public function getUpdatedAtColumn()
    {
        return null;
    }
}
