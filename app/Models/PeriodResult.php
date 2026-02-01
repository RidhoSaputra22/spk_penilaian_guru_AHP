<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PeriodResult extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assessment_period_id',
        'status',
        'generated_at',
        'published_at',
        'generated_by',
        'calculated_at',
        'meta',
    ];

    protected $casts = [
        'generated_at' => 'datetime',
        'published_at' => 'datetime',
        'calculated_at' => 'datetime',
        'meta' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function teacherResults()
    {
        return $this->hasMany(TeacherPeriodResult::class, 'period_result_id')->orderBy('rank');
    }
}
