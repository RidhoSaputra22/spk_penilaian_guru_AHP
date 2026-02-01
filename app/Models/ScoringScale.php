<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringScale extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'institution_id',
        'name',
        'scale_type',
        'min_value',
        'max_value',
        'step',
        'meta',
    ];

    protected $casts = [
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'step' => 'decimal:4',
        'meta' => 'array',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function options()
    {
        return $this->hasMany(ScoringScaleOption::class);
    }
}
