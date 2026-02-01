<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherPeriodResult extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'period_result_id',
        'teacher_profile_id',
        'final_score',
        'rank',
        'details',
    ];

    protected $casts = [
        'final_score' => 'decimal:4',
        'details' => 'array',
    ];

    public function periodResult()
    {
        return $this->belongsTo(PeriodResult::class, 'period_result_id');
    }

    public function period()
    {
        return $this->hasOneThrough(
            AssessmentPeriod::class,
            PeriodResult::class,
            'id', // Foreign key on period_results
            'id', // Foreign key on assessment_periods
            'period_result_id', // Local key on teacher_period_results
            'assessment_period_id' // Local key on period_results
        );
    }

    public function teacher()
    {
        return $this->belongsTo(TeacherProfile::class, 'teacher_profile_id');
    }

    public function criteriaScores()
    {
        return $this->hasMany(TeacherCriteriaScore::class, 'teacher_period_result_id');
    }
}
