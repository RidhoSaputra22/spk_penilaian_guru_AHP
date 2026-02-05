<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiFormItemOption extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'item_id',
        'value',
        'label',
        'score_value',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'score_value' => 'decimal:2',
        'meta' => 'array',
    ];

    public function item()
    {
        return $this->belongsTo(KpiFormItem::class, 'item_id');
    }
}
