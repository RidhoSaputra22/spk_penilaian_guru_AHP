<?php

namespace App\Models;

use App\Enums\KpiFormAssignmentStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiFormAssignment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assessment_period_id',
        'form_version_id',
        'status',
        'assigned_at',
        'locked_at',
        'assigned_by',
        'meta',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }

    public function formVersion()
    {
        return $this->belongsTo(KpiFormVersion::class, 'form_version_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function assessors()
    {
        return $this->belongsToMany(AssessorProfile::class, 'kpi_assignment_assessors', 'assignment_id', 'assessor_profile_id');
    }

    public function teacherGroups()
    {
        return $this->belongsToMany(TeacherGroup::class, 'kpi_assignment_teacher_groups', 'assignment_id', 'teacher_group_id');
    }

    public function teachers()
    {
        return $this->belongsToMany(TeacherProfile::class, 'kpi_assignment_teachers', 'assignment_id', 'teacher_profile_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class, 'assignment_id');
    }
}
