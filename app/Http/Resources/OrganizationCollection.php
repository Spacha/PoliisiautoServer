<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class OrganizationCollection extends ResourceCollection
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
                'id'                => $res->id,
                'name'              => $res->name,
                'street_address'    => $res->street_address,
                'city'              => $res->city,
                'zip'               => $res->zip,
                'created_at'        => $res->created_at,
            ];
        });
    }
}
