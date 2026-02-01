<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
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
            ]);
        }

        // Get assessments that require evidence
        $assessments = Assessment::with([
            'period',
            'assignment.formVersion.template',
            'assignment.formVersion.sections.items' => function ($query) {
                $query->where('requires_evidence', true);
            },
        ])
            ->where('teacher_id', $teacherProfile->id)
            ->whereIn('status', ['pending', 'draft', 'in_progress'])
            ->whereHas('period', function ($q) {
                $q->where('status', 'active');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Get existing evidence uploads
        $evidenceUploads = EvidenceUpload::where('uploaded_by', $user->id)
            ->whereIn('assessment_id', $assessments->pluck('id'))
            ->get()
            ->keyBy(function ($item) {
                return $item->assessment_id . '-' . $item->form_item_id;
            });

        return view('teacher.evidence.index', compact('assessments', 'evidenceUploads'));
    }

    public function upload(Request $request, Assessment $assessment, KpiFormItem $item)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($assessment->teacher_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        // Verify period is active
        if ($assessment->period->status !== 'active') {
            return back()->with('error', 'Periode penilaian sudah ditutup.');
        }

        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // Max 10MB
            'description' => ['nullable', 'string', 'max:500'],
            'link' => ['nullable', 'url', 'max:500'],
        ]);

        // Determine file type
        $allowedTypes = $this->getAllowedTypes($item);
        if ($request->hasFile('file')) {
            $extension = $request->file('file')->getClientOriginalExtension();
            $mimeType = $request->file('file')->getMimeType();

            // Validate file type if restricted
            if (!empty($allowedTypes) && !$this->isAllowedType($mimeType, $extension, $allowedTypes)) {
                return back()->with('error', 'Tipe file tidak diizinkan untuk indikator ini.');
            }
        }

        // Delete existing evidence if any
        $existing = EvidenceUpload::where('assessment_id', $assessment->id)
            ->where('form_item_id', $item->id)
            ->where('uploaded_by', $user->id)
            ->first();

        if ($existing) {
            if ($existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }
            $existing->delete();
        }

        // Upload new file
        $filePath = null;
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
            'assessment_id' => $assessment->id,
            'form_item_id' => $item->id,
            'uploaded_by' => $user->id,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'link' => $request->link,
            'description' => $request->description,
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
        if ($evidence->assessment->period->status !== 'active') {
            return back()->with('error', 'Periode penilaian sudah ditutup.');
        }

        // Delete file
        if ($evidence->file_path) {
            Storage::disk('public')->delete($evidence->file_path);
        }

        $evidence->delete();

        return back()->with('success', 'Bukti berhasil dihapus.');
    }

    public function download(EvidenceUpload $evidence)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($evidence->uploaded_by !== $user->id && $evidence->assessment->teacher_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses.');
        }

        if (!$evidence->file_path || !Storage::disk('public')->exists($evidence->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return Storage::disk('public')->download($evidence->file_path, $evidence->file_name);
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
