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

class Student extends User
{
    use HasFactory, HasRole;

    /**
     * User role ID.
     *
     * @var int
     */
    public const ROLE = Role::STUDENT;

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
     * Get the reports that the student is involved in as a bully.
     */
    public function bullyReports()
    {
        return $this->hasMany(Report::class, 'bully_id');
    }

    /**
     * Get the reports that the student is involved in as bullied.
     */
    public function bulliedReports()
    {
        return $this->hasMany(Report::class, 'bullied_id');
    }
}
