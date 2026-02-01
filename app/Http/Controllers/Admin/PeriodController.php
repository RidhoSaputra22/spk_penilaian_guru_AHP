<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaSet;
use App\Models\KpiFormTemplate;
use App\Models\Assessment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PeriodController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = AssessmentPeriod::with(['criteriaSet'])
            ->where('institution_id', $institution?->id);

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Year filter
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        $periods = $query->latest('start_date')->paginate(10)->withQueryString();

        // Stats
        $stats = [
            'total' => AssessmentPeriod::where('institution_id', $institution?->id)->count(),
            'open' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'open')->count(),
            'closed' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'closed')->count(),
            'draft' => AssessmentPeriod::where('institution_id', $institution?->id)->where('status', 'draft')->count(),
        ];

        return view('admin.periods.index', compact('periods', 'stats'));
    }

    public function create()
    {
        $criteriaSets = CriteriaSet::where('institution_id', auth()->user()->institution_id)->get();
        $kpiTemplates = KpiFormTemplate::where('institution_id', auth()->user()->institution_id)->get();

        return view('admin.periods.create', compact('criteriaSets', 'kpiTemplates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'scoring_start' => ['nullable', 'date', 'after_or_equal:start_date'],
            'scoring_end' => ['nullable', 'date', 'before_or_equal:end_date', 'after:scoring_start'],
            'criteria_set_id' => ['required', 'exists:criteria_sets,id'],
            'kpi_form_template_id' => ['nullable', 'exists:kpi_form_templates,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,open,closed'],
        ]);

        $period = AssessmentPeriod::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            ...$validated,
        ]);

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
        $period->load(['criteriaSet.criteriaNodes', 'ahpModel.weights']);

        // Get assessment stats
        $assessmentStats = Assessment::where('period_id', $period->id)
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        return view('admin.periods.show', compact('period', 'assessmentStats'));
    }

    public function edit(AssessmentPeriod $period)
    {
        $criteriaSets = CriteriaSet::where('institution_id', auth()->user()->institution_id)->get();
        $kpiTemplates = KpiFormTemplate::where('institution_id', auth()->user()->institution_id)->get();

        return view('admin.periods.edit', compact('period', 'criteriaSets', 'kpiTemplates'));
    }

    public function update(Request $request, AssessmentPeriod $period)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'scoring_start' => ['nullable', 'date', 'after_or_equal:start_date'],
            'scoring_end' => ['nullable', 'date', 'before_or_equal:end_date', 'after:scoring_start'],
            'criteria_set_id' => ['required', 'exists:criteria_sets,id'],
            'kpi_form_template_id' => ['nullable', 'exists:kpi_form_templates,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'in:draft,open,closed'],
        ]);

        $period->update($validated);

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
}
