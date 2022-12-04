<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReportMessage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['content', 'is_anonymous'];

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
     * Get the report that the message belongs to.
     */
    public function report()
    {
        return $this->belongsTo(Report::class);
    }

    /**
     * Get the user who is the author of the message.
     */
    public function author()
    {
        // FIXME: Obviously we should not do this :()
        $role = \DB::table('users')->where('id', $this->author_id)->first('role');
        return !empty($role)
            ? $this->belongsTo(\App\Role::getRoleModel($role->role))
            : null;
        //return $this->belongsTo(User::class);
    }
}
