<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiFormSection extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'form_version_id',
        'criteria_node_id',
        'title',
        'description',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function version()
    {
        return $this->belongsTo(KpiFormVersion::class, 'form_version_id');
    }

    public function criteriaNode()
    {
        return $this->belongsTo(CriteriaNode::class);
    }

    public function items()
    {
        return $this->hasMany(KpiFormItem::class, 'section_id')->orderBy('sort_order');
    }
}
