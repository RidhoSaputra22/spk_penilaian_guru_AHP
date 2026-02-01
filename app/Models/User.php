<?php

namespace App\Models;

// Laravel default imports:
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasUlids, SoftDeletes;

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
