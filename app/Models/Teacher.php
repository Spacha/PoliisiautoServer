<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Traits\HasRole;
use App\Role;

class Teacher extends User
{
    use HasFactory, HasRole;

    /**
     * User role ID.
     *
     * @var int
     */
    public const ROLE = Role::TEACHER;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'role' => self::ROLE,
    ];

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * Get the reports in which the teacher is a handler.
     */
    public function assignedReports()
    {
        return $this->hasMany(Report::class, 'handler_id');
    }
}
