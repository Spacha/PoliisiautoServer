<?php

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
     * Get the reports that are assigned to the teacher.
     */
    public function assignedReports()
    {
        return $this->hasMany(Report::class, 'assignee_id');
    }
}
