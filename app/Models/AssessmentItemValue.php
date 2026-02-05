<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentItemValue extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'assessment_id',
        'form_item_id',
        'value_number',
        'value_string',
        'value_bool',
        'notes',
        'score_value',
        'meta',
    ];

    protected $casts = [
        'value_number' => 'decimal:2',
        'value_bool' => 'boolean',
        'score_value' => 'decimal:2',
        'meta' => 'array',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function formItem()
    {
        return $this->belongsTo(KpiFormItem::class, 'form_item_id');
    }

    public function evidences()
    {
        return $this->hasMany(EvidenceUpload::class, 'assessment_item_value_id');
    }
}
