<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use \Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'is_anonymous',
        'type',
        'bully_id',
        'bullied_id',
        'handler_id',
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
    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'type'          => 1,
        'is_anonymous'  => true,
    ];

    /**
     * Get if the report has been opened.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isOpened() : Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => !empty($attributes['opened_at']),
        );
    }

    /**
     * Get if the report has been closed.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function isClosed() : Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => !empty($attributes['closed_at']),
        );
    }

    /**
     * Get the current status of the report.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function status() : Attribute
    {
        return Attribute::make(
            get: function ($value, $attributes) {
                $opened = !empty($attributes['opened_at']);
                $closed = !empty($attributes['closed_at']);

                if (! $opened && ! $closed)
                    return 'PENDING';
                if ($opened && ! $closed)
                    return 'OPENED';
                if ($closed)
                    return 'CLOSED';
            }
        );
    }

    /**
     * Open the report.
     *
     * @return \App\Models\Report
     */
    public function open() : Report
    {
        $this->opened_at = now();
        return $this;
    }

    /**
     * Close the report.
     *
     * @return \App\Models\Report
     */
    public function close() : Report
    {
        $this->closed_at = now();
        return $this;
    }

    /**
     * Get the organization that owns the case.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the case that the report belongs to.
     */
    public function case()
    {
        return $this->belongsTo(ReportCase::class);
    }

    /**
     * Get the user that owns the case.
     * 
     * Since App\Models\User is abstract, we cannot initialize it.
     * This forces us to make an ugly hack like this. This makes one extra
     * SQL query, which is not good.
     * 
     * FIXME: Read above. Perhaps we'd have to make User non-abstract after all :(.
     */
    public function reporter()
    {
        // FIXME: Obviously we should not do this :()
        $role = \DB::table('users')->where('id', $this->reporter_id)->first('role');
        return !empty($role)
            ? $this->belongsTo(\App\Role::getRoleModel($role->role))
            : null;
        //return $this->belongsTo(User::class);
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
     * Get the teacher who is the handler of the case.
     */
    public function handler()
    {
        return $this->belongsTo(Teacher::class);
    }

    /**
     * Get the messages for the report.
     */
    public function messages()
    {
        return $this->hasMany(ReportMessage::class);
    }
}
