<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvidenceUpload extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    protected $fillable = [
        'assessment_id',
        'form_item_id',
        'assessment_item_value_id',
        'uploaded_by',
        'disk',
        'path',
        'file_path',
        'file_name',
        'file_size',
        'file_type',
        'original_name',
        'mime_type',
        'size',
        'url',
        'link',
        'description',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function assessment()
    {
        return $this->belongsTo(Assessment::class);
    }

    public function formItem()
    {
        return $this->belongsTo(KpiFormItem::class, 'form_item_id');
    }

    public function itemValue()
    {
        return $this->belongsTo(AssessmentItemValue::class, 'assessment_item_value_id');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
