<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory, HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'institution_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'properties',
        'ip_address',
        'user_agent',
        'created_at',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($log) {
            // Auto-fill institution_id from authenticated user if not set
            if (!$log->institution_id && auth()->check()) {
                $log->institution_id = auth()->user()->institution_id;
            }

            // Set created_at if not set
            if (!$log->created_at) {
                $log->created_at = now();
            }
        });
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
