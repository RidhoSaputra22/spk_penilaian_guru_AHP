<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KpiFormTemplate extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'institution_id',
        'name',
        'description',
        'default_scoring_scale_id',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function defaultScale()
    {
        return $this->belongsTo(ScoringScale::class, 'default_scoring_scale_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function versions()
    {
        return $this->hasMany(KpiFormVersion::class, 'template_id');
    }

    public function latestVersion()
    {
        return $this->hasOne(KpiFormVersion::class, 'template_id')->latestOfMany('version');
    }
}
