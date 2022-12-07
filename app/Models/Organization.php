<?php

/**
 * Copyright (c) 2022, Miika Sikala, Essi Passoja, Lauri Klemettilä
 *
 * SPDX-License-Identifier: BSD-2-Clause
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'street_address',
        'city',
        'zip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [];

    /**
     * Get the organization's address.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function address() : Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => "{$attributes['street_address']}, {$attributes['zip']} {$attributes['city']}"
        );
    }

    /**
     * Get the cases for the organization.
     */
    public function cases()
    {
        return $this->hasMany(ReportCase::class);
    }

    /**
     * Get the reports for the organization.
     */
    public function reports()
    {
        return $this->hasManyThrough(Report::class, ReportCase::class);
    }

    /**
     * Get the studetns for the organization.
     */
    public function students()
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Get the teachers for the organization.
     */
    public function teachers()
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Get the administrators for the organization.
     */
    public function administrators()
    {
        return $this->hasMany(Administrator::class);
    }
}
