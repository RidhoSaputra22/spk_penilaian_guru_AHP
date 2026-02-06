<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\KpiFormAssignment;
use App\Models\TeacherProfile;
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

        if ($request->filled('period') || $request->filled('period_id')) {
            $periodId = $request->filled('period') ? $request->period : $request->period_id;
            $selectedPeriod = $periods->firstWhere('id', $periodId);
        } else {
            $selectedPeriod = $periods->firstWhere('status', 'open') ?? $periods->first();
        }

        if ($selectedPeriod) {
            $query = Assessment::with(['teacher.user', 'assessor.user', 'period', 'assignment'])
                ->where('assessment_period_id', $selectedPeriod->id);

            // Search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('teacher.user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            }

            // Status filter
            if ($request->filled('status')) {
                if ($request->status === 'completed') {
                    // For completed status, include both submitted and finalized
                    $query->whereIn('status', ['submitted', 'finalized']);
                } else {
                    $query->where('status', $request->status);
                }
            }

            $assessments = $query->latest()->paginate(10)->withQueryString();

            // Stats - count submitted and finalized as completed
            $stats = [
                'total' => Assessment::where('assessment_period_id', $selectedPeriod->id)->count(),
                'pending' => Assessment::where('assessment_period_id', $selectedPeriod->id)->where('status', 'pending')->count(),
                'in_progress' => Assessment::where('assessment_period_id', $selectedPeriod->id)->where('status', 'in_progress')->count(),
                'completed' => Assessment::where('assessment_period_id', $selectedPeriod->id)->whereIn('status', ['submitted', 'finalized'])->count(),
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
            'assignment.formVersion.sections',
            'itemValues',
            'statusLogs.user',
        ]);

        return view('admin.assessments.show', compact('assessment'));
    }

    public function create(Request $request)
    {
        $institution = auth()->user()->institution;

        $periods = AssessmentPeriod::where('institution_id', $institution?->id)
            ->orderByDesc('scoring_open_at')
            ->get()
            ->mapWithKeys(fn ($p) => [
                $p->id => "{$p->name} ({$p->academic_year} - {$p->semester})",
            ]);

        $selectedPeriod = null;
        if ($request->filled('period')) {
            $selectedPeriod = AssessmentPeriod::find($request->period);
        } else {
            $selectedPeriod = AssessmentPeriod::where('institution_id', $institution?->id)
                ->where('status', 'open')
                ->first();
        }

        $teachers = TeacherProfile::whereHas('user', function ($q) use ($institution) {
            $q->where('institution_id', $institution?->id);
        })
            ->with('user')
            ->get()
            ->mapWithKeys(fn ($t) => [
                $t->id => $t->user->name.' ('.($t->nip ?? '-').')',
            ]);

        $assessors = AssessorProfile::whereHas('user', function ($q) use ($institution) {
            $q->where('institution_id', $institution?->id);
        })
            ->with('user')
            ->get()
            ->mapWithKeys(fn ($a) => [
                $a->id => $a->user->name.' ('.($a->employee_id ?? '-').')',
            ]);

        return view('admin.assessments.create', compact(
            'periods',
            'selectedPeriod',
            'teachers',
            'assessors'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'period_id' => ['required', 'exists:assessment_periods,id'],
            'assignment_type' => ['required', 'in:individual,bulk'],
            'teacher_id' => ['required_if:assignment_type,individual', 'nullable', 'exists:teacher_profiles,id'],
            'assessor_id' => ['required_if:assignment_type,individual', 'nullable', 'exists:assessor_profiles,id'],
            'teacher_ids' => ['required_if:assignment_type,bulk', 'nullable', 'array'],
            'teacher_ids.*' => ['exists:teacher_profiles,id'],
            'assessor_ids' => ['required_if:assignment_type,bulk', 'nullable', 'array'],
            'assessor_ids.*' => ['exists:assessor_profiles,id'],
        ]);

        $period = AssessmentPeriod::findOrFail($validated['period_id']);

        // Get or create form assignment
        $latestFormVersion = \App\Models\KpiFormVersion::whereHas('template', function ($q) use ($period) {
            $q->where('institution_id', $period->institution_id);
        })
            ->where('status', 'published')
            ->latest('version')
            ->first();

        $assignment = KpiFormAssignment::firstOrCreate(
            [
                'assessment_period_id' => $period->id,
            ],
            [
                'id' => Str::ulid(),
                'form_version_id' => $latestFormVersion?->id,
                'status' => 'active',
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]
        );

        $createdCount = 0;

        if ($validated['assignment_type'] === 'individual') {
            // Individual assignment
            $assessment = Assessment::firstOrCreate(
                [
                    'assessment_period_id' => $period->id,
                    'teacher_profile_id' => $validated['teacher_id'],
                    'assessor_profile_id' => $validated['assessor_id'],
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
        } else {
            // Bulk assignment
            foreach ($validated['teacher_ids'] as $teacherId) {
                foreach ($validated['assessor_ids'] as $assessorId) {
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
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_assessments',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Created {$createdCount} assessment assignments",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('admin.assessments.index', ['period' => $period->id])
            ->with('success', "Berhasil membuat {$createdCount} penugasan penilaian.");
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
        // Get the latest active KPI form version for this institution
        $latestFormVersion = \App\Models\KpiFormVersion::whereHas('template', function ($q) use ($period) {
            $q->where('institution_id', $period->institution_id);
        })
            ->where('status', 'published')
            ->latest('version')
            ->first();

        $assignment = KpiFormAssignment::firstOrCreate(
            [
                'assessment_period_id' => $period->id,
            ],
            [
                'id' => Str::ulid(),
                'form_version_id' => $latestFormVersion?->id,
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
            'description' => "Assigned {$createdCount} assessments for ".count($validated['teacher_ids']).' teachers to '.count($validated['assessor_ids']).' assessors',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', "Berhasil membuat {$createdCount} penugasan penilaian.");
    }

    public function finalize(Request $request, Assessment $assessment)
    {
        // Validate assessment can be finalized (must be submitted)
        if ($assessment->status !== 'submitted') {
            return back()->with('error', 'Penilaian hanya bisa difinalisasi jika sudah di-submit oleh assessor.');
        }

        $validated = $request->validate([
            'admin_notes' => ['nullable', 'string', 'max:500'],
        ]);

        // Update status to finalized
        $oldStatus = $assessment->status;
        $assessment->status = 'finalized';
        $assessment->finalized_at = now();
        $assessment->finalized_by = auth()->id();
        $assessment->admin_notes = $validated['admin_notes'] ?? null;
        $assessment->save();

        // Log status change
        \App\Models\AssessmentStatusLog::create([
            'id' => Str::ulid(),
            'assessment_id' => $assessment->id,
            'from_status' => $oldStatus,
            'to_status' => 'finalized',
            'changed_by' => auth()->id(),
            'reason' => 'Finalized by admin: ' . ($validated['admin_notes'] ?? 'No notes'),
            'created_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'finalize_assessment',
            'entity_type' => Assessment::class,
            'entity_id' => $assessment->id,
            'description' => "Finalized assessment for {$assessment->teacher->user->name} by {$assessment->assessor->user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Penilaian berhasil difinalisasi.');
    }

    public function reopen(Request $request, Assessment $assessment)
    {
        // Validate assessment can be reopened (must be submitted or finalized)
        if (!in_array($assessment->status, ['submitted', 'finalized'])) {
            return back()->with('error', 'Hanya penilaian yang sudah di-submit atau difinalisasi yang bisa dibuka kembali.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:500'],
        ]);

        // Update status back to in_progress
        $oldStatus = $assessment->status;
        $assessment->status = 'in_progress';
        $assessment->finalized_at = null;
        $assessment->finalized_by = null;
        $assessment->save();

        // Log status change
        \App\Models\AssessmentStatusLog::create([
            'id' => Str::ulid(),
            'assessment_id' => $assessment->id,
            'from_status' => $oldStatus,
            'to_status' => 'in_progress',
            'changed_by' => auth()->id(),
            'reason' => 'Reopened by admin: ' . $validated['reason'],
            'created_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'reopen_assessment',
            'entity_type' => Assessment::class,
            'entity_id' => $assessment->id,
            'description' => "Reopened assessment for {$assessment->teacher->user->name} by {$assessment->assessor->user->name}. Reason: {$validated['reason']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Penilaian berhasil dibuka kembali.');
    }
}
