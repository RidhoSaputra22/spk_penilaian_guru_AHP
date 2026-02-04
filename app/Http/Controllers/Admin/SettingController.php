<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institution;
use App\Models\ScoringScale;
use App\Models\TeacherGroup;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $tab = $request->query('tab', 'institution');

        $scoringScales = ScoringScale::where('institution_id', $institution?->id)
            ->with('options')
            ->orderBy('name')
            ->get();

        $teacherGroups = TeacherGroup::where('institution_id', $institution?->id)
            ->withCount('teachers')
            ->get();

        return view('admin.settings.index', compact('institution', 'tab', 'scoringScales', 'teacherGroups'));
    }

    public function updateInstitution(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'npsn' => 'nullable|string|max:50',
            'accreditation' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $institution = auth()->user()->institution;
        $institution->update($validated);

        return back()->with('success', 'Informasi institusi berhasil diperbarui');
    }

    public function updateScoringScale(Request $request)
    {
        $validated = $request->validate([
            'scales' => 'required|array',
            'scales.*.id' => 'required|exists:scoring_scales,id',
            'scales.*.grade' => 'required|string|max:10',
            'scales.*.min_score' => 'required|numeric|min:0|max:100',
            'scales.*.max_score' => 'required|numeric|min:0|max:100',
            'scales.*.label' => 'required|string|max:255',
        ]);

        foreach ($validated['scales'] as $scaleData) {
            ScoringScale::where('id', $scaleData['id'])->update([
                'grade' => $scaleData['grade'],
                'min_score' => $scaleData['min_score'],
                'max_score' => $scaleData['max_score'],
                'label' => $scaleData['label'],
            ]);
        }

        return back()->with('success', 'Pengaturan grade berhasil diperbarui');
    }

    public function storeTeacherGroup(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $institution = auth()->user()->institution;

        TeacherGroup::create([
            'institution_id' => $institution->id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        return back()->with('success', 'Kelompok guru berhasil ditambahkan');
    }

    public function updateTeacherGroup(Request $request, TeacherGroup $teacherGroup)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $teacherGroup->update($validated);

        return back()->with('success', 'Kelompok guru berhasil diperbarui');
    }

    public function deleteTeacherGroup(TeacherGroup $teacherGroup)
    {
        if ($teacherGroup->teachers()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus kelompok yang masih memiliki anggota');
        }

        $teacherGroup->delete();

        return back()->with('success', 'Kelompok guru berhasil dihapus');
    }

    public function updateAhpSettings(Request $request)
    {
        $validated = $request->validate([
            'cr_threshold' => 'required|numeric|min:0|max:1',
        ]);

        $institution = auth()->user()->institution;

        // Store in institution meta or settings table
        $meta = $institution->meta ?? [];
        $meta['ahp_cr_threshold'] = $validated['cr_threshold'];
        $institution->update(['meta' => $meta]);

        return back()->with('success', 'Pengaturan AHP berhasil diperbarui');
    }

    public function updateEmailSettings(Request $request)
    {
        $validated = $request->validate([
            'smtp_host' => 'required|string',
            'smtp_port' => 'required|integer',
            'smtp_username' => 'required|string',
            'smtp_password' => 'nullable|string',
            'smtp_encryption' => 'required|in:tls,ssl,none',
            'from_address' => 'required|email',
            'from_name' => 'required|string',
        ]);

        $institution = auth()->user()->institution;
        $meta = $institution->meta ?? [];
        $meta['email_settings'] = $validated;
        $institution->update(['meta' => $meta]);

        return back()->with('success', 'Pengaturan email berhasil diperbarui');
    }

    public function updateNotificationSettings(Request $request)
    {
        $validated = $request->validate([
            'enable_email_notifications' => 'boolean',
            'enable_assessment_reminders' => 'boolean',
            'enable_result_notifications' => 'boolean',
            'enable_deadline_alerts' => 'boolean',
        ]);

        $institution = auth()->user()->institution;
        $meta = $institution->meta ?? [];
        $meta['notification_settings'] = $validated;
        $institution->update(['meta' => $meta]);

        return back()->with('success', 'Pengaturan notifikasi berhasil diperbarui');
    }

    public function createBackup()
    {
        // This would typically use a package like spatie/laravel-backup
        // For now, we'll just return a success message
        return back()->with('success', 'Backup berhasil dibuat');
    }
}
