<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherProfile extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_no',
        'subject',
        'employment_status',
        'position',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function groups()
    {
        return $this->belongsToMany(TeacherGroup::class, 'teacher_group_members');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function results()
    {
        return $this->hasMany(TeacherPeriodResult::class);
    }
}
