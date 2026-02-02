<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, HasUlids, Notifiable, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'name',
        'email',
        'password',
        'status',
        'last_login_at',
        'deactivated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function teacherProfile()
    {
        return $this->hasOne(TeacherProfile::class);
    }

    public function assessorProfile()
    {
        return $this->hasOne(AssessorProfile::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function hasRole(string $key): bool
    {
        return $this->roles()->where('key', $key)->exists();
    }
}
