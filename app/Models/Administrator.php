<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
// use App\Models\Traits\HasRole;
use App\Role;

class Administrator extends Teacher
{
    //use HasFactory, HasRole;

    /**
     * User role ID.
     *
     * @var int
     */
    public const ROLE = Role::ADMINISTRATOR;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'role' => self::ROLE,
    ];
}
