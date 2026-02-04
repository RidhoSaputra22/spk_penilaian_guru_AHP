<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\Assessment;
use App\Models\KpiFormVersion;
use App\Models\TeacherProfile;
use App\Models\AssessorProfile;
use Illuminate\Http\Request;

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
            'assessor.user'
        ]);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
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
            'finalized' => 'Selesai'
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
            ->mapWithKeys(fn($p) => [$p->id => "{$p->name} ({$p->academic_year} - {$p->semester})"]);

        $formVersions = KpiFormVersion::with('template')
            ->where('status', 'published')
            ->get()
            ->mapWithKeys(fn($v) => [$v->id => "{$v->template->name} (v{$v->version})"]);

        $teachers = TeacherProfile::with('user')
            ->get()
            ->mapWithKeys(fn($t) => [$t->id => "{$t->user->name} ({$t->employee_no})"]);

        $assessors = AssessorProfile::with('user')
            ->get()
            ->mapWithKeys(fn($a) => [$a->id => $a->user->name]);

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
            'form_version_id' => 'required|exists:kpi_form_versions,id',
            'teacher_profile_id' => 'required|exists:teacher_profiles,id',
            'assessor_profile_id' => 'required|exists:assessor_profiles,id',
        ]);

        // Create assessment
        Assessment::create($validated + [
            'status' => 'draft',
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
            'itemValues.formItem'
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
}
