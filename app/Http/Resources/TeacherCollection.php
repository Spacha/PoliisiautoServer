<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class TeacherCollection extends ResourceCollection
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
                'first_name'            => $res->first_name,
                'last_name'             => $res->last_name,
                'email'                 => $res->email,
                'organization_id'       => $res->organization_id,
                'role'                  => $res->role,
                'phone'                 => $res->phone,
                'email_verified_at'     => $res->email_verified_at,
                'created_at'            => $res->created_at,
            ];
        });
    }
}
