<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportCase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

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
     * Get the organization that owns the case.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user that owns the case.
     */
    public function reporter()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the student who is a bully in the case.
     */
    public function bully()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the student who is a bullied in the case.
     */
    public function bullied()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get the teacher who is a bullied in the case.
     */
    public function assignee()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the report messages for the case.
     */
    public function reportMessages()
    {
        return $this->hasMany(ReportMessage::class);
    }
}