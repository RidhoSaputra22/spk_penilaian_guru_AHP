<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assessment_period_id',
        'assignment_id',
        'teacher_profile_id',
        'assessor_profile_id',
        'status',
        'started_at',
        'submitted_at',
        'finalized_at',
        'reopened_at',
        'reopened_reason',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'finalized_at' => 'datetime',
        'reopened_at' => 'datetime',
        'meta' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }

    public function assignment()
    {
        return $this->belongsTo(KpiFormAssignment::class, 'assignment_id');
    }

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function assessor()
    {
        return $this->belongsTo(AssessorProfile::class, 'assessor_profile_id');
    }

    public function itemValues()
    {
        return $this->hasMany(AssessmentItemValue::class);
    }

    public function statusLogs()
    {
        return $this->hasMany(AssessmentStatusLog::class);
    }
}
