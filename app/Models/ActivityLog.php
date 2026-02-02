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
        // Aliases for compatibility
        'entity_type',
        'entity_id',
        'description',
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

            // Map entity_type/entity_id to subject_type/subject_id
            if ($log->entity_type && !$log->subject_type) {
                $log->subject_type = $log->entity_type;
                unset($log->entity_type);
            }
            if ($log->entity_id && !$log->subject_id) {
                $log->subject_id = $log->entity_id;
                unset($log->entity_id);
            }

            // Map description to properties
            if ($log->description && !$log->properties) {
                $log->properties = ['description' => $log->description];
                unset($log->description);
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
