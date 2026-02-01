<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TeacherGroup extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'name',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function teachers()
    {
        return $this->belongsToMany(TeacherProfile::class, 'teacher_group_members', 'teacher_group_id', 'teacher_profile_id');
    }

    public function assignments()
    {
        return $this->belongsToMany(KpiFormAssignment::class, 'kpi_assignment_teacher_groups', 'teacher_group_id', 'assignment_id');
    }
}
