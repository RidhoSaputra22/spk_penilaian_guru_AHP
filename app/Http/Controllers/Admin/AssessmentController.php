<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\TeacherProfile;
use App\Models\AssessorProfile;
use App\Models\KpiFormAssignment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AssessmentController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $periods = AssessmentPeriod::where('institution_id', $institution?->id)
            ->orderByDesc('scoring_open_at')
            ->get();

        $selectedPeriod = null;
        $assessments = collect();
        $stats = [];

        if ($request->filled('period_id')) {
            $selectedPeriod = $periods->firstWhere('id', $request->period_id);
        } else {
            $selectedPeriod = $periods->firstWhere('status', 'open') ?? $periods->first();
        }

        if ($selectedPeriod) {
            $query = Assessment::with(['teacher.user', 'assessor.user', 'period', 'assignment'])
                ->where('assessment_period_id', $selectedPeriod->id);

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('teacher.user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $assessments = $query->latest()->paginate(10)->withQueryString();

            // Stats
            $stats = [
                'total' => Assessment::where('assessment_period_id', $selectedPeriod->id)->count(),
                'pending' => Assessment::where('assessment_period_id', $selectedPeriod->id)->where('status', 'pending')->count(),
                'in_progress' => Assessment::where('assessment_period_id', $selectedPeriod->id)->where('status', 'in_progress')->count(),
                'completed' => Assessment::where('assessment_period_id', $selectedPeriod->id)->where('status', 'submitted')->count(),
            ];
        }

        return view('admin.assessments.index', compact('periods', 'selectedPeriod', 'assessments', 'stats'));
    }

    public function show(Assessment $assessment)
    {
        $assessment->load([
            'teacher.user',
            'assessor.user',
            'period',
            'assignment.formVersion',
            'itemValues',
            'statusLogs.user',
        ]);

        return view('admin.assessments.show', compact('assessment'));
    }

    public function assign(Request $request)
    {
        $validated = $request->validate([
            'period_id' => ['required', 'exists:assessment_periods,id'],
            'teacher_ids' => ['required', 'array'],
            'teacher_ids.*' => ['exists:teacher_profiles,id'],
            'assessor_ids' => ['required', 'array'],
            'assessor_ids.*' => ['exists:assessor_profiles,id'],
        ]);

        $period = AssessmentPeriod::findOrFail($validated['period_id']);

        // Get or create form assignment
        $assignment = KpiFormAssignment::firstOrCreate(
            [
                'assessment_period_id' => $period->id,
            ],
            [
                'id' => Str::ulid(),
                'form_version_id' => $period->kpiFormTemplate?->versions()->latest('version_number')->first()?->id,
                'status' => 'active',
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]
        );

        $createdCount = 0;
        foreach ($validated['teacher_ids'] as $teacherId) {
            foreach ($validated['assessor_ids'] as $assessorId) {
                // Create assessment for each teacher-assessor pair
                $assessment = Assessment::firstOrCreate(
                    [
                        'assessment_period_id' => $period->id,
                        'teacher_profile_id' => $teacherId,
                        'assessor_profile_id' => $assessorId,
                    ],
                    [
                        'id' => Str::ulid(),
                        'assignment_id' => $assignment->id,
                        'status' => 'pending',
                    ]
                );

                if ($assessment->wasRecentlyCreated) {
                    $createdCount++;
                }
            }
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'assign_assessments',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Assigned {$createdCount} assessments for " . count($validated['teacher_ids']) . " teachers to " . count($validated['assessor_ids']) . " assessors",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Berhasil membuat {$createdCount} penugasan penilaian.");
    }
}
