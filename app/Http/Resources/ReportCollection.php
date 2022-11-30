<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ReportCollection extends ResourceCollection
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
                'id'                        => $res->id,
                'description'               => $res->description,
                'report_case_id'            => $res->report_case_id,
                'reporter_id'               => $res->reporter_id,
                'handler_id'                => $res->handler_id,
                'bully_id'                  => $res->bully_id,
                'bullied_id'                => $res->bullied_id,
                'is_anonymous'              => $res->is_anonymous,
                'type'                      => $res->type,
                'opened_at'                 => $res->opened_at,
                'closed_at'                 => $res->closed_at,
                'created_at'                => $res->created_at,

                'reporter_name'             => $res->reporter->name ?? null,
            ];
        });
        // return parent::toArray($request);
    }
}
