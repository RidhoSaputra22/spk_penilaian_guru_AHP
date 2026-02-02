<?php

namespace App\Models;

use App\Enums\CriteriaNodeType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaNode extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'criteria_set_id',
        'parent_id',
        'node_type',
        'code',
        'name',
        'description',
        'sort_order',
        'is_active',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function set()
    {
        return $this->belongsTo(CriteriaSet::class, 'criteria_set_id');
    }

    public function parent()
    {
        return $this->belongsTo(CriteriaNode::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(CriteriaNode::class, 'parent_id');
    }

    public function formSections()
    {
        return $this->hasMany(KpiFormSection::class);
    }

    public function formItems()
    {
        return $this->hasMany(KpiFormItem::class);
    }

    public function ahpWeights()
    {
        return $this->hasMany(AhpWeight::class);
    }
}
