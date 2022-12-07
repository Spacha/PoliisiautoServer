<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReportResource extends JsonResource
{
    public static $wrap = null;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                        => $this->id,
            'description'               => $this->description,
            'report_case_id'            => $this->report_case_id,
            'reporter_id'               => !$this->is_anonymous ? ($this->reporter_id ?? null) : null,
            'handler_id'                => $this->handler_id,
            'bully_id'                  => $this->bully_id,
            'bullied_id'                => $this->bullied_id,
            'is_anonymous'              => $this->is_anonymous,
            'type'                      => $this->type,
            'opened_at'                 => $this->opened_at,
            'closed_at'                 => $this->closed_at,
            'created_at'                => $this->created_at,

            'reporter_name'             => !$this->is_anonymous ? ($this->reporter->name ?? null) : null,
            'bully_name'                => $this->bully->name ?? null,
            'bullied_name'             => $this->bullied->name ?? null,
        ];
        // return parent::toArray($request);
    }
}
