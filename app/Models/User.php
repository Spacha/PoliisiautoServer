<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use \Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Role;

abstract class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     * Attribute 'role' MUST be re-defined by all heirs.
     *
     * @var array
     */
    protected $attributes = [
        'role' => null,
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::addGlobalScope(new Scopes\Role);
    }

    /**
     * Get the user's role.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function role() : Attribute
    {
        return Attribute::make(
            get: fn ($value) => Role::forHumans($value)
        );
    }

    /**
     * Get the user's whole name.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function name() : Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name']
        );
    }

    /**
     * Get if the user is student.
     *
     * @return bool
     */
    public function isStudent() : bool
    {
        return $this->role == Role::STUDENT;
    }

    /**
     * Get if the user is teacher.
     *
     * @return bool
     */
    public function isTeacher() : bool
    {
        return $this->role == Role::TEACHER;
    }

    /**
     * Get if the user is administrator.
     *
     * @return bool
     */
    public function isAdministrator() : bool
    {
        return $this->role == Role::ADMINISTRATOR;
    }

    /**
     * Get the organization that the user belongs to.
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the reports that the user owns.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    /**
     * Get the report messages that the user owns.
     */
    public function reportMessages()
    {
        return $this->hasMany(ReportMessage::class, 'author_id');
    }
}
