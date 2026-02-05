<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AhpComparison extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'ahp_model_id',
        'parent_node_id',
        'node_a_id',
        'node_b_id',
        'value',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
    ];

    public function model()
    {
        return $this->belongsTo(AhpModel::class, 'ahp_model_id');
    }

    public function parentNode()
    {
        return $this->belongsTo(CriteriaNode::class, 'parent_node_id');
    }

    public function nodeA()
    {
        return $this->belongsTo(CriteriaNode::class, 'node_a_id');
    }

    public function nodeB()
    {
        return $this->belongsTo(CriteriaNode::class, 'node_b_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
