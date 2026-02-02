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
        'assessment_item_value_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'url',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function assessment()
    {
        return $this->itemValue->assessment();
    }

    public function formItem()
    {
        return $this->itemValue->formItem();
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
