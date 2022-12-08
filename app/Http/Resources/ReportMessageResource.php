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
            'id'                    => $this->id,
            'content'               => $this->content,
            'report_id'             => $this->report_id,
            'author_id'             => !$this->is_anonymous ? ($this->author_id ?? null) : null,
            'is_anonymous'          => $this->is_anonymous,
            'created_at'            => $this->created_at,

            'author_name'           => !$this->is_anonymous ? ($this->author->name ?? null) : null,
        ];
    }
}
