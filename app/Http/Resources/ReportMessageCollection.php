<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportMessageCollection extends ResourceCollection
{
    public static $wrap = null;

    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return $this->collection->map(function($res) {
            return [
                'id'                    => $res->id,
                'content'               => $res->content,
                'report_id'             => $res->report_id,
                'author_id'             => !$res->is_anonymous ? ($res->author_id ?? null) : null,
                'is_anonymous'          => $res->is_anonymous,
                'created_at'            => $res->created_at,

                'author_name'           => !$res->is_anonymous ? ($res->author->name ?? null) : null,
            ];
        });
        // return parent::toArray($request);
    }
}
