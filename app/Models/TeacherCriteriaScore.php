<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeacherCriteriaScore extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'teacher_period_result_id',
        'criteria_node_id',
        'raw_score',
        'weight',
        'weighted_score',
        'meta',
    ];

    protected $casts = [
        'raw_score' => 'decimal:4',
        'weight' => 'decimal:12',
        'weighted_score' => 'decimal:4',
        'meta' => 'array',
    ];

    public function teacherResult()
    {
        return $this->belongsTo(TeacherPeriodResult::class, 'teacher_period_result_id');
    }

    public function teacherPeriodResult()
    {
        return $this->belongsTo(TeacherPeriodResult::class, 'teacher_period_result_id');
    }

    public function criteriaNode()
    {
        return $this->belongsTo(CriteriaNode::class, 'criteria_node_id');
    }
}
