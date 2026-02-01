<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Institution extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'address',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function periods()
    {
        return $this->hasMany(AssessmentPeriod::class);
    }

    public function teacherGroups()
    {
        return $this->hasMany(TeacherGroup::class);
    }

    public function criteriaSets()
    {
        return $this->hasMany(CriteriaSet::class);
    }

    public function scoringScales()
    {
        return $this->hasMany(ScoringScale::class);
    }

    public function formTemplates()
    {
        return $this->hasMany(KpiFormTemplate::class);
    }
}
