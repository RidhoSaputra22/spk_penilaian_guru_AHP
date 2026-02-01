<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessmentPeriod extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'name',
        'academic_year',
        'semester',
        'scoring_open_at',
        'scoring_close_at',
        'status',
        'meta',
    ];

    protected $casts = [
        'scoring_open_at' => 'datetime',
        'scoring_close_at' => 'datetime',
        'meta' => 'array',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function assignments()
    {
        return $this->hasMany(KpiFormAssignment::class);
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    public function ahpModel()
    {
        return $this->hasOne(AhpModel::class);
    }

    public function result()
    {
        return $this->hasOne(PeriodResult::class);
    }

    public function teacherResults()
    {
        return $this->hasMany(TeacherPeriodResult::class, 'period_id');
    }
}
