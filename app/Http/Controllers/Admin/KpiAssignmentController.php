<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\KpiFormAssignment;
use App\Models\TeacherGroup;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KpiAssignmentController extends Controller
{
    public function index(Request $request)
    {
        // Fetch periods for filter dropdown
        $periods = AssessmentPeriod::orderBy('academic_year', 'desc')
            ->orderBy('semester', 'desc')
            ->get()
            ->pluck('name', 'id')
            ->prepend('Semua Periode', '');

        // Build query for assessments (which represent individual teacher assignments)
        $query = Assessment::with([
            'teacher.user',
            'assignment.formVersion.template',
            'period',
            'assessor.user',
        ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('teacher.user', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                })->orWhereHas('assignment.formVersion.template', function ($subQ) use ($search) {
                    $subQ->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($request->filled('period')) {
            $query->where('assessment_period_id', $request->period);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assignments = $query->latest()->get();

        // Calculate stats
        $stats = [
            'total' => $assignments->count(),
            'assigned' => $assignments->where('status', 'draft')->count(),
            'in_progress' => $assignments->where('status', 'in_progress')->count(),
            'completed' => $assignments->where('status', 'finalized')->count(),
        ];

        // Status options for filter (matching Assessment status)
        $statusOptions = [
            '' => 'Semua Status',
            'draft' => 'Ditugaskan',
            'in_progress' => 'Dikerjakan',
            'finalized' => 'Selesai',
        ];

        return view('admin.kpi-assignments.index', compact(
            'assignments',
            'periods',
            'statusOptions',
            'stats'
        ));
    }

    public function create()
    {
        // Fetch data for dropdowns
        $periods = AssessmentPeriod::orderBy('academic_year', 'desc')
            ->orderBy('semester', 'desc')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => "{$p->name} ({$p->academic_year} - {$p->semester})"]);

        // Get unique form versions (group by form_version_id to avoid duplicates)
        $formVersions = KpiFormAssignment::with(['formVersion.template', 'period'])
            ->get()
            ->unique('form_version_id')
            ->mapWithKeys(fn ($f) => [
                $f->id => "{$f->formVersion->template->name} (v{$f->formVersion->version})",
            ]);

        $teachers = TeacherProfile::with('user')
            ->get()
            ->mapWithKeys(fn ($t) => [$t->id => "{$t->user->name} ({$t->employee_no})"]);

        $assessors = AssessorProfile::with('user')
            ->get()
            ->mapWithKeys(fn ($a) => [$a->id => $a->user?->name]);

        // dd($assessors);

        return view('admin.kpi-assignments.create', compact(
            'periods',
            'formVersions',
            'teachers',
            'assessors'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assessment_period_id' => 'required|exists:assessment_periods,id',
            'form_version_id' => 'required|exists:kpi_form_assignments,id',
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'assessor_profile_id' => 'required|exists:assessor_profiles,id',
        ]);

        // dd($validated);

        $exists = Assessment::where('assessment_period_id', $validated['assessment_period_id'])
            ->where('teacher_profile_id', $validated['teacher_profile_id'])
            ->where('assessor_profile_id', $validated['assessor_profile_id'])
            ->exists();

        if ($exists) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Penugasan sudah ada. Kombinasi periode, guru, dan penilai ini sudah pernah dibuat.');
        }

        $kpiFormAssignment = KpiFormAssignment::where('id', $validated['form_version_id'])
            ->first();

        // dd($kpiFormAssignment, $validated['form_version_id']);
        // dd($kpiFormAssignment);
        // dd($kpiFormAssignment->formVersion->template->name);

        // Create assessment
        Assessment::create($validated + [
            'status' => 'draft',
            'assignment_id' => $kpiFormAssignment->id,

        ]);

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Penugasan KPI berhasil dibuat');
    }

    public function show($id)
    {
        $assignment = Assessment::with([
            'teacher.user',
            'assignment.formVersion.template',
            'period',
            'assessor.user',
            'itemValues.formItem',
        ])->findOrFail($id);

        return view('admin.kpi-assignments.show', compact('assignment'));
    }

    public function destroy($id)
    {
        $assignment = Assessment::findOrFail($id);
        $assignment->delete();

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', 'Penugasan KPI berhasil dibatalkan');
    }

    /**
     * Show the bulk assignment form.
     */
    public function bulkCreate()
    {
        // Periods
        $periods = AssessmentPeriod::orderBy('academic_year', 'desc')
            ->where('status', 'open')
            ->orderBy('semester', 'desc')
            ->get()
            ->mapWithKeys(fn ($p) => [$p->id => "{$p->name} ({$p->academic_year} - {$p->semester})"]);

        // KPI Form Assignments (published forms assigned to periods)
        // Get unique form versions to avoid duplicates
        $formVersions = KpiFormAssignment::with(['formVersion.template', 'period'])
            ->get()
            ->unique('form_version_id')
            ->mapWithKeys(fn ($f) => [
                $f->id => "{$f->formVersion->template->name} (v{$f->formVersion->version})",
            ]);

        // Teachers with user info
        $teachers = TeacherProfile::with('user')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'name' => $t->user->name,
                'employee_no' => $t->employee_no,
                'subject' => $t->subject ?? '-',
                'position' => $t->position ?? '-',
            ]);

        // Assessors
        $assessors = AssessorProfile::with('user')
            ->whereHas('user', fn ($q) => $q->where('status', 'active'))
            ->get()
            ->mapWithKeys(fn ($a) => [$a->id => $a->user->name]);

        // Teacher Groups for quick selection
        $teacherGroups = TeacherGroup::with('teachers')->get();

        // Existing assignments to flag already-assigned teachers
        // Grouped by period + assessor: [period_id => [assessor_id => [teacher_id, ...]]]
        $existingAssignments = Assessment::select('assessment_period_id', 'assessor_profile_id', 'teacher_profile_id')
            ->get()
            ->groupBy('assessment_period_id')
            ->map(fn ($group) => $group->groupBy('assessor_profile_id')
                ->map(fn ($subGroup) => $subGroup->pluck('teacher_profile_id')->toArray())
            )
            ->toArray();

        return view('admin.kpi-assignments.bulk-create', compact(
            'periods',
            'formVersions',
            'teachers',
            'assessors',
            'teacherGroups',
            'existingAssignments'
        ));
    }

    /**
     * Store bulk assignments.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'assessment_period_id' => 'required|exists:assessment_periods,id',
            'form_version_id' => 'required|exists:kpi_form_assignments,id',
            'assessor_profile_id' => 'required|exists:assessor_profiles,id',
            'teacher_ids' => 'required|array|min:1',
            'teacher_ids.*' => 'exists:teacher_profiles,id',
        ], [
            'teacher_ids.required' => 'Pilih minimal 1 guru untuk ditugaskan.',
            'teacher_ids.min' => 'Pilih minimal 1 guru untuk ditugaskan.',
        ]);

        $kpiFormAssignment = KpiFormAssignment::findOrFail($validated['form_version_id']);

        $created = 0;
        $skipped = 0;
        $skippedNames = [];

        DB::beginTransaction();
        try {
            foreach ($validated['teacher_ids'] as $teacherId) {
                // Check if assignment already exists
                $exists = Assessment::where('assessment_period_id', $validated['assessment_period_id'])
                    ->where('teacher_profile_id', $teacherId)
                    ->where('assessor_profile_id', $validated['assessor_profile_id'])
                    ->exists();

                if ($exists) {
                    $skipped++;
                    $teacher = TeacherProfile::with('user')->find($teacherId);
                    $skippedNames[] = $teacher->user->name ?? $teacherId;

                    continue;
                }

                Assessment::create([
                    'assessment_period_id' => $validated['assessment_period_id'],
                    'assignment_id' => $kpiFormAssignment->id,
                    'teacher_profile_id' => $teacherId,
                    'assessor_profile_id' => $validated['assessor_profile_id'],
                    'status' => 'draft',
                ]);

                $created++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->route('admin.kpi-assignments.bulk-create')
                ->withInput()
                ->with('error', 'Gagal membuat penugasan: '.$e->getMessage());
        }

        // Build success message
        $message = "Berhasil menugaskan {$created} guru.";
        if ($skipped > 0) {
            $message .= " {$skipped} guru dilewati karena sudah ditugaskan sebelumnya (".implode(', ', $skippedNames).').';
        }

        return redirect()
            ->route('admin.kpi-assignments.index')
            ->with('success', $message);
    }
}
