<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringScaleOption extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'scoring_scale_id',
        'value',
        'label',
        'description',
        'score_value',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'score_value' => 'decimal:4',
        'meta' => 'array',
    ];

    public function scale()
    {
        return $this->belongsTo(ScoringScale::class, 'scoring_scale_id');
    }
}
