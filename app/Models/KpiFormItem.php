<?php

namespace App\Models;

use App\Enums\KpiFormFieldType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiFormItem extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'section_id',
        'criteria_node_id',
        'label',
        'help_text',
        'field_type',
        'is_required',
        'min_value',
        'max_value',
        'scoring_scale_id',
        'default_value',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'meta' => 'array',
    ];

    public function section()
    {
        return $this->belongsTo(KpiFormSection::class, 'section_id');
    }

    public function criteriaNode()
    {
        return $this->belongsTo(CriteriaNode::class);
    }

    public function scale()
    {
        return $this->belongsTo(ScoringScale::class, 'scoring_scale_id');
    }

    public function options()
    {
        return $this->hasMany(KpiFormItemOption::class, 'item_id')->orderBy('sort_order');
    }

    public function values()
    {
        return $this->hasMany(AssessmentItemValue::class, 'form_item_id');
    }
}
