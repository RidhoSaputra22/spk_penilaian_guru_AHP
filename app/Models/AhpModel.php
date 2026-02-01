<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhpModel extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'ahp_models';

    protected $fillable = [
        'assessment_period_id',
        'criteria_set_id',
        'status',
        'consistency_ratio',
        'finalized_at',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'consistency_ratio' => 'decimal:8',
        'finalized_at' => 'datetime',
        'meta' => 'array',
    ];

    public function period()
    {
        return $this->belongsTo(AssessmentPeriod::class, 'assessment_period_id');
    }

    public function criteriaSet()
    {
        return $this->belongsTo(CriteriaSet::class, 'criteria_set_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comparisons()
    {
        return $this->hasMany(AhpComparison::class, 'ahp_model_id');
    }

    public function weights()
    {
        return $this->hasMany(AhpWeight::class, 'ahp_model_id');
    }
}
