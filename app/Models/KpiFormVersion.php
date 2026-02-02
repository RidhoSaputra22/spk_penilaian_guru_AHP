<?php

namespace App\Models;

use App\Enums\KpiFormVersionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KpiFormVersion extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'template_id',
        'version',
        'status',
        'published_at',
        'locked_at',
        'created_by',
        'meta',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'locked_at' => 'datetime',
        'meta' => 'array',
    ];

    public function template()
    {
        return $this->belongsTo(KpiFormTemplate::class, 'template_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sections()
    {
        return $this->hasMany(KpiFormSection::class, 'form_version_id')->orderBy('sort_order');
    }

    public function assignments()
    {
        return $this->hasMany(KpiFormAssignment::class, 'form_version_id');
    }
}
