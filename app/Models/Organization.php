<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        return $this->hasMany(Report::class);
    }

    /**
     * Get the studetns for the organization.
     */
    public function studetns()
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
     * Get the administrator for the organization.
     */
    public function administrator()
    {
        return $this->hasMany(Administrator::class);
    }
}
