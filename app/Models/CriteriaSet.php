<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CriteriaSet extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'institution_id',
        'name',
        'version',
        'is_active',
        'locked_at',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'locked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function nodes()
    {
        return $this->hasMany(CriteriaNode::class);
    }
}
