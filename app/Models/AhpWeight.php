<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhpWeight extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'ahp_model_id',
        'criteria_node_id',
        'parent_node_id',
        'level',
        'weight',
        'meta',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'meta' => 'array',
    ];

    public function model()
    {
        return $this->belongsTo(AhpModel::class, 'ahp_model_id');
    }

    public function criteriaNode()
    {
        return $this->belongsTo(CriteriaNode::class);
    }

    public function parentNode()
    {
        return $this->belongsTo(CriteriaNode::class, 'parent_node_id');
    }
}
