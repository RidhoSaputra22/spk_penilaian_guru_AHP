<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AssessorProfile extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function assignments()
    {
        return $this->belongsToMany(KpiFormAssignment::class, 'kpi_assignment_assessors', 'assessor_profile_id', 'assignment_id');
    }

    public function assessments()
    {
        return $this->hasMany(Assessment::class);
    }

    /**
     * Get the assessor type based on user's roles or meta data
     */
    public function getAssessorTypeAttribute()
    {
        // Check meta data first
        if (isset($this->meta['type'])) {
            return $this->meta['type'];
        }

        // For now, default to 'peer' since we only have basic roles
        // This can be extended when principal/supervisor roles are created
        return 'peer';
    }
}
