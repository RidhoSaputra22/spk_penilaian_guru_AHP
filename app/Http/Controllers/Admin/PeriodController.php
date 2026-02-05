<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AhpModel;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaSet;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormVersion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = AssessmentPeriod::with(['ahpModel.criteriaSet'])
            ->where('institution_id', $institution?->id);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('scoring_open_at', $request->year);
        }

        $periods = $query->latest('scoring_open_at')->paginate(10)->withQueryString();

        // Status counts for stats cards
        $statusCounts = [
            'draft' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'draft')->count(),
            'open' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'open')->count(),
            'closed' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'closed')->count(),
            'archived' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'archived')->count(),
        ];

        // Academic years for filter dropdown
        $academicYears = AssessmentPeriod::where('institution_id', $institution?->id)
            ->whereNotNull('academic_year')
            ->distinct()
            ->pluck('academic_year', 'academic_year')
            ->toArray();

        // Legacy stats for compatibility
        $stats = [
            'total' => AssessmentPeriod::where('institution_id', $institution?->id)->count(),
            'open' => $statusCounts['open'],
            'closed' => $statusCounts['closed'],
            'draft' => $statusCounts['draft'],
        ];

        return view('admin.periods.index', compact('periods', 'stats', 'statusCounts', 'academicYears'));
    }

    public function create()
    {
        // Get criteria sets and format for select component
        $criteriaSets = CriteriaSet::where('institution_id', auth()->user()->institution_id)
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        // If no criteria sets available, provide empty array
        if (empty($criteriaSets)) {
            $criteriaSets = [];
        }

        // Get KPI form versions (published) and format for select component
        $kpiForms = KpiFormVersion::with('template')
            ->whereHas('template', function ($q) {
                $q->where('institution_id', auth()->user()->institution_id);
            })
            ->where('status', 'published')
            ->get()
            ->mapWithKeys(function ($version) {
                return [$version->id => $version->template->name.' v'.$version->version];
            })
            ->toArray();

        // If no form versions available, provide empty array
        if (empty($kpiForms)) {
            $kpiForms = [];
        }

        return view('admin.periods.create', compact('criteriaSets', 'kpiForms'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'scoring_open_at' => ['nullable', 'date_format:Y-m-d'],
            'scoring_close_at' => ['nullable', 'date_format:Y-m-d', 'after:scoring_open_at'],
            'criteria_set_id' => ['nullable', 'exists:criteria_sets,id'],
            'kpi_form_version_id' => ['nullable', 'exists:kpi_form_versions,id'],
            'description' => ['nullable', 'string'],
        ]);

        // Set default status to 'draft' for new periods
        $validated['status'] = 'draft';

        // Process date fields to proper datetime format
        if (! empty($validated['scoring_open_at'])) {
            $validated['scoring_open_at'] = Carbon::parse($validated['scoring_open_at'])->startOfDay();
        }

        if (! empty($validated['scoring_close_at'])) {
            $validated['scoring_close_at'] = Carbon::parse($validated['scoring_close_at'])->endOfDay();
        }

        // Separate the validated data for assessment period
        $periodData = collect($validated)->except(['criteria_set_id', 'kpi_form_version_id'])->toArray();

        $period = AssessmentPeriod::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            ...$periodData,
        ]);

        // Create AHP Model if criteria_set_id is provided
        if (! empty($validated['criteria_set_id'])) {
            AhpModel::create([
                'id' => Str::ulid(),
                'assessment_period_id' => $period->id,
                'criteria_set_id' => $validated['criteria_set_id'],
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]);
        }

        // Create KPI Form Assignment if kpi_form_version_id is provided
        if (! empty($validated['kpi_form_version_id'])) {
            KpiFormAssignment::create([
                'id' => Str::ulid(),
                'assessment_period_id' => $period->id,
                'form_version_id' => $validated['kpi_form_version_id'],
                'status' => 'draft',
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_period',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Created assessment period: {$period->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Periode penilaian berhasil dibuat.');
    }

    public function show(AssessmentPeriod $period)
    {
        // Load all necessary relationships
        $period->load([
            'ahpModel.criteriaSet.nodes',
            'ahpModel.weights',
            'assignments.formVersion.template',
        ]);

        // Get assessment stats
        $assessmentStats = Assessment::where('assessment_period_id', $period->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.periods.show', compact('period', 'assessmentStats'));
    }

    public function edit(AssessmentPeriod $period)
    {
        // Get criteria sets and format for select component
        $criteriaSets = CriteriaSet::where('institution_id', auth()->user()->institution_id)
            ->get()
            ->pluck('name', 'id')
            ->toArray();

        // If no criteria sets available, provide empty array
        if (empty($criteriaSets)) {
            $criteriaSets = [];
        }

        // Get KPI form versions (published) and format for select component
        $kpiForms = KpiFormVersion::with('template')
            ->whereHas('template', function ($q) {
                $q->where('institution_id', auth()->user()->institution_id);
            })
            ->where('status', 'published')
            ->get()
            ->mapWithKeys(function ($version) {
                return [$version->id => $version->template->name.' v'.$version->version];
            })
            ->toArray();

        // If no form versions available, provide empty array
        if (empty($kpiForms)) {
            $kpiForms = [];
        }

        // Load period relationships to get current selected values
        $period->load(['ahpModel', 'assignments.formVersion']);

        return view('admin.periods.edit', compact('period', 'criteriaSets', 'kpiForms'));
    }

    public function update(Request $request, AssessmentPeriod $period)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'academic_year' => ['nullable', 'string', 'max:20'],
            'semester' => ['nullable', 'string', 'max:20'],
            'scoring_open_at' => ['nullable', 'date_format:Y-m-d'],
            'scoring_close_at' => ['nullable', 'date_format:Y-m-d', 'after:scoring_open_at'],
            'criteria_set_id' => ['nullable', 'exists:criteria_sets,id'],
            'kpi_form_version_id' => ['nullable', 'exists:kpi_form_versions,id'],
            'description' => ['nullable', 'string'],
        ]);

        // Process date fields to proper datetime format
        if (! empty($validated['scoring_open_at'])) {
            $validated['scoring_open_at'] = Carbon::parse($validated['scoring_open_at'])->startOfDay();
        }

        if (! empty($validated['scoring_close_at'])) {
            $validated['scoring_close_at'] = Carbon::parse($validated['scoring_close_at'])->endOfDay();
        }

        // Separate period data from relational data
        $periodData = collect($validated)->except(['criteria_set_id', 'kpi_form_version_id'])->toArray();

        // Store description in meta if provided
        if (isset($validated['description'])) {
            $meta = $period->meta ?? [];
            $meta['description'] = $validated['description'];
            $periodData['meta'] = $meta;
            unset($periodData['description']);
        }

        $period->update($periodData);

        // Update AHP Model if criteria_set_id is provided
        if (isset($validated['criteria_set_id'])) {
            $period->ahpModel()->updateOrCreate(
                ['assessment_period_id' => $period->id],
                [
                    'criteria_set_id' => $validated['criteria_set_id'],
                    'status' => $period->ahpModel?->status ?? 'draft',
                    'created_by' => $period->ahpModel?->created_by ?? auth()->id(),
                ]
            );
        }

        // Update KPI Form Assignment if kpi_form_version_id is provided
        if (isset($validated['kpi_form_version_id'])) {
            // Remove existing assignments
            $period->assignments()->delete();

            // Create new assignment
            KpiFormAssignment::create([
                'id' => Str::ulid(),
                'assessment_period_id' => $period->id,
                'form_version_id' => $validated['kpi_form_version_id'],
                'status' => 'draft',
                'assigned_at' => now(),
                'assigned_by' => auth()->id(),
            ]);
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_period',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Updated assessment period: {$period->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Periode penilaian berhasil diperbarui.');
    }

    public function destroy(AssessmentPeriod $period)
    {
        // Check if period has assessments
        if ($period->assessments()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus periode yang memiliki data penilaian.');
        }

        $periodName = $period->name;
        $period->delete();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'delete_period',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Deleted assessment period: {$periodName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.periods.index')
            ->with('success', 'Periode penilaian berhasil dihapus.');
    }

    public function updateStatus(Request $request, AssessmentPeriod $period)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:draft,open,closed'],
        ]);

        $oldStatus = $period->status;
        $period->update(['status' => $validated['status']]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'change_period_status',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Changed period status from {$oldStatus} to {$validated['status']}: {$period->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Status periode berhasil diperbarui.');
    }

    public function open(AssessmentPeriod $period)
    {
        // Validate that period can be opened
        if ($period->status !== 'draft') {
            return back()->with('error', 'Periode ini tidak dapat dibuka karena status bukan draft.');
        }

        // abort if there's other open period
        if (AssessmentPeriod::where('status', 'open')->exists()) {
            return back()->with('error', 'Sudah ada periode yang sedang dibuka. Silahkan tutup periode tersebut terlebih dahulu.');
        }

        // Update period status to open and set open date
        $period->update([
            'status' => 'open',
            'scoring_open_at' => $period->scoring_open_at ?? now(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'open_period',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Opened assessment period: {$period->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Periode penilaian berhasil dibuka.');
    }

    public function close(AssessmentPeriod $period)
    {
        // Validate that period can be closed
        if ($period->status !== 'open') {
            return back()->with('error', 'Periode ini tidak dapat ditutup karena status bukan open.');
        }

        // Update period status to closed and set close date
        $period->update([
            'status' => 'closed',
            'scoring_close_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'close_period',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Closed assessment period: {$period->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Periode penilaian berhasil ditutup.');
    }

    public function archive(AssessmentPeriod $period)
    {
        // Validate that period can be archived
        if ($period->status !== 'closed') {
            return back()->with('error', 'Periode ini tidak dapat diarsipkan karena status bukan closed.');
        }

        // Update period status to archived
        $period->update([
            'status' => 'archived',
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'archive_period',
            'entity_type' => AssessmentPeriod::class,
            'entity_id' => $period->id,
            'description' => "Archived assessment period: {$period->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Periode penilaian berhasil diarsipkan.');
    }
}
