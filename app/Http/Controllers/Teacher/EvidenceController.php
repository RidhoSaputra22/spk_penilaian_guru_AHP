<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\EvidenceUpload;
use App\Models\KpiFormItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EvidenceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return view('teacher.evidence.index', [
                'assessments' => collect(),
                'evidenceUploads' => collect(),
            ]);
        }

        // Get assessments that require evidence
        $assessments = Assessment::with([
            'period',
            'assignment.formVersion.template',
            'assignment.formVersion.sections.items',
            'itemValues',
        ])
            ->where('teacher_profile_id', $teacherProfile->id)
            ->whereIn('status', ['pending', 'draft', 'in_progress'])
            ->whereHas('period', function ($q) {
                $q->where('status', 'open');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get assessment item value IDs
        $assessmentItemValueIds = $assessments->flatMap(function($assessment) {
            return $assessment->itemValues->pluck('id');
        });

        // Get existing evidence uploads
        $evidenceUploads = EvidenceUpload::where('uploaded_by', $user->id)
            ->whereIn('assessment_item_value_id', $assessmentItemValueIds)
            ->with('itemValue')
            ->get()
            ->keyBy(function ($item) {
                $meta = $item->meta ?? [];
                return ($meta['assessment_id'] ?? '') . '-' . ($meta['form_item_id'] ?? '');
            });

        return view('teacher.evidence.index', compact('assessments', 'evidenceUploads'));
    }

    public function upload(Request $request, Assessment $assessment, KpiFormItem $item)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($assessment->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Verify assessment is not submitted/finalized
        if (in_array($assessment->status, ['submitted', 'finalized'])) {
            abort(403, 'Tidak dapat mengunggah bukti untuk penilaian yang sudah disubmit.');
        }

        // Verify period is active
        if ($assessment->period->status !== 'open') {
            return back()->with('error', 'Periode penilaian sudah ditutup.');
        }

        // Validation rules
        $rules = [
            'description' => ['nullable', 'string', 'max:500'],
        ];

        // Check if it's a link or file upload
        if ($request->input('type') === 'link') {
            $rules['url'] = ['required', 'url', 'max:500'];
        } else {
            $rules['file'] = ['required', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp'];
        }

        $request->validate($rules);

        // Find assessment item value to link the evidence
        $assessmentItemValue = AssessmentItemValue::where('assessment_id', $assessment->id)
            ->where('form_item_id', $item->id)
            ->first();

        if (!$assessmentItemValue) {
            return back()->with('error', 'Item nilai tidak ditemukan.');
        }

        // Delete existing evidence if any
        $existing = EvidenceUpload::where('assessment_item_value_id', $assessmentItemValue->id)
            ->where('uploaded_by', $user->id)
            ->first();

        if ($existing) {
            if ($existing->path) {
                Storage::disk('public')->delete($existing->path);
            }
            $existing->delete();
        }

        // Upload new file
        $filePath = '';  // Default to empty string since column is not nullable
        $fileName = null;
        $fileSize = null;
        $fileType = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileSize = $file->getSize();
            $fileType = $file->getMimeType();

            $filePath = $file->store('evidence/' . $assessment->id, 'public');
        }

        // Create evidence record
        EvidenceUpload::create([
            'assessment_item_value_id' => $assessmentItemValue->id,
            'uploaded_by' => $user->id,
            'path' => $filePath,
            'original_name' => $fileName,
            'size' => $fileSize,
            'mime_type' => $fileType,
            'url' => $request->url,
            'meta' => [
                'assessment_id' => $assessment->id,
                'form_item_id' => $item->id,
                'description' => $request->description,
            ],
        ]);

        return back()->with('success', 'Bukti berhasil diunggah.');
    }

    public function destroy(EvidenceUpload $evidence)
    {
        $user = auth()->user();

        // Verify ownership
        if ($evidence->uploaded_by !== $user->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Verify period is still active
        $assessmentItemValue = $evidence->itemValue;
        if ($assessmentItemValue->assessment->period->status !== 'open') {
            return back()->with('error', 'Periode penilaian sudah ditutup.');
        }

        // Delete file
        $disk = $evidence->disk ?? 'public';
        if ($evidence->path && $evidence->path !== '') {
            Storage::disk($disk)->delete($evidence->path);
        }

        $evidence->delete();

        return back()->with('success', 'Bukti berhasil dihapus.');
    }

    public function download(EvidenceUpload $evidence)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        $assessmentItemValue = $evidence->itemValue;
        if ($evidence->uploaded_by !== $user->id && $assessmentItemValue->assessment->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        $disk = $evidence->disk ?? 'public';
        if (!$evidence->path || !Storage::disk($disk)->exists($evidence->path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk($disk)->download($evidence->path, $evidence->original_name);
    }

    private function getAllowedTypes(KpiFormItem $item): array
    {
        $meta = $item->meta ?? [];
        return $meta['allowed_evidence_types'] ?? [];
    }

    private function isAllowedType(string $mimeType, string $extension, array $allowedTypes): bool
    {
        $typeMap = [
            'document' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'spreadsheet' => ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];

        foreach ($allowedTypes as $type) {
            if (isset($typeMap[$type]) && in_array($mimeType, $typeMap[$type])) {
                return true;
            }
        }

        return false;
    }
}
