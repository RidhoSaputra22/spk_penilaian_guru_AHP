<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AhpModelStatus;
use App\Enums\AssessmentPeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\AhpModel;
use App\Models\AhpComparison;
use App\Models\AhpWeight;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AhpController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        // Get periods with AHP models
        $periods = AssessmentPeriod::with(['ahpModel'])
            ->where('institution_id', $institution?->id)
            ->latest('scoring_open_at')
            ->get();

        // Get active period or selected period
        $selectedPeriod = null;
        $ahpModel = null;
        $criteria = collect();
        $comparisons = collect();
        $weights = collect();

        if ($request->filled('period')) {
            $selectedPeriod = $periods->firstWhere('id', $request->period);
        } else {
            $selectedPeriod = $periods->firstWhere('status', 'open') ?? $periods->first();
        }

        if ($selectedPeriod) {
            $ahpModel = $selectedPeriod->ahpModel;

            // Get root level criteria (no parent)
            $criteriaSetId = $ahpModel?->criteria_set_id;
            $criteria = $criteriaSetId ? CriteriaNode::where('criteria_set_id', $criteriaSetId)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get() : collect();

            if ($ahpModel) {
                $comparisons = AhpComparison::where('ahp_model_id', $ahpModel->id)
                    ->with(['nodeA', 'nodeB'])
                    ->get();
                $weights = AhpWeight::where('ahp_model_id', $ahpModel->id)->get();
            }
        }

        return view('admin.ahp.index', compact(
            'periods',
            'selectedPeriod',
            'ahpModel',
            'criteria',
            'comparisons',
            'weights'
        ));
    }

    public function createModel(Request $request)
    {
        $validated = $request->validate([
            'period_id' => ['required', 'exists:assessment_periods,id'],
            'criteria_set_id' => ['required', 'exists:criteria_sets,id'],
        ]);

        $period = AssessmentPeriod::findOrFail($validated['period_id']);

        // Check if model already exists
        if ($period->ahpModel) {
            return back()->with('error', 'Model AHP sudah ada untuk periode ini.');
        }

        $ahpModel = AhpModel::create([
            'id' => Str::ulid(),
            'assessment_period_id' => $period->id,
            'criteria_set_id' => $validated['criteria_set_id'],
            'status' => AhpModelStatus::Draft,
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_ahp_model',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => "Created AHP model for period: {$period->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.ahp.index', ['period' => $period->id])
            ->with('success', 'Model AHP berhasil dibuat.');
    }

    public function saveComparisons(Request $request, AhpModel $ahpModel = null)
    {
        $validated = $request->validate([
            'ahp_model_id' => $ahpModel ? ['nullable'] : ['required', 'exists:ahp_models,id'],
            'comparisons' => ['required', 'array'],
            'comparisons.*.node_a_id' => ['required', 'exists:criteria_nodes,id'],
            'comparisons.*.node_b_id' => ['required', 'exists:criteria_nodes,id'],
            'comparisons.*.value' => ['required', 'numeric', 'min:0.111', 'max:9'],
        ]);

        // Use the route model binding if provided, otherwise use from request
        $ahpModel = $ahpModel ?? AhpModel::findOrFail($validated['ahp_model_id']);

        // Delete existing comparisons
        AhpComparison::where('ahp_model_id', $ahpModel->id)->delete();

        // Insert new comparisons
        foreach ($validated['comparisons'] as $comparison) {
            AhpComparison::create([
                'id' => Str::ulid(),
                'ahp_model_id' => $ahpModel->id,
                'parent_node_id' => null, // Root level
                'node_a_id' => $comparison['node_a_id'],
                'node_b_id' => $comparison['node_b_id'],
                'value' => $comparison['value'],
            ]);
        }

        // Calculate weights using AHP algorithm
        $this->calculateWeights($ahpModel);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'save_ahp_comparisons',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => "Saved AHP comparisons and calculated weights",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'weights' => AhpWeight::where('ahp_model_id', $ahpModel->id)->get(),
            'consistency_ratio' => $ahpModel->fresh()->consistency_ratio,
        ]);
    }

    protected function calculateWeights(AhpModel $ahpModel)
    {
        $comparisons = AhpComparison::where('ahp_model_id', $ahpModel->id)
            ->whereNull('parent_criteria_id')
            ->get();

        // Get unique criteria IDs
        $criteriaIds = $comparisons->pluck('criteria_i_id')
            ->merge($comparisons->pluck('criteria_j_id'))
            ->unique()
            ->values();

        $n = $criteriaIds->count();
        if ($n == 0) return;

        // Build comparison matrix
        $matrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i == $j) {
                    $matrix[$i][$j] = 1;
                } else {
                    $comparison = $comparisons->first(function($c) use ($criteriaIds, $i, $j) {
                        return $c->criteria_i_id == $criteriaIds[$i] && $c->criteria_j_id == $criteriaIds[$j];
                    });

                    if ($comparison) {
                        $matrix[$i][$j] = $comparison->value;
                    } else {
                        // Try reverse
                        $reverse = $comparisons->first(function($c) use ($criteriaIds, $i, $j) {
                            return $c->criteria_i_id == $criteriaIds[$j] && $c->criteria_j_id == $criteriaIds[$i];
                        });
                        $matrix[$i][$j] = $reverse ? (1 / $reverse->value) : 1;
                    }
                }
            }
        }

        // Calculate column sums
        $colSums = [];
        for ($j = 0; $j < $n; $j++) {
            $colSums[$j] = 0;
            for ($i = 0; $i < $n; $i++) {
                $colSums[$j] += $matrix[$i][$j];
            }
        }

        // Normalize matrix and calculate weights
        $weights = [];
        for ($i = 0; $i < $n; $i++) {
            $rowSum = 0;
            for ($j = 0; $j < $n; $j++) {
                $rowSum += $matrix[$i][$j] / $colSums[$j];
            }
            $weights[$i] = $rowSum / $n;
        }

        // Calculate consistency ratio
        $lambdaMax = 0;
        for ($j = 0; $j < $n; $j++) {
            $lambdaMax += $colSums[$j] * $weights[$j];
        }

        $ci = ($lambdaMax - $n) / ($n - 1);

        // Random Index values for matrices of size 1-10
        $ri = [0, 0, 0.58, 0.90, 1.12, 1.24, 1.32, 1.41, 1.45, 1.49];
        $cr = ($n > 1 && isset($ri[$n - 1]) && $ri[$n - 1] > 0) ? $ci / $ri[$n - 1] : 0;

        // Delete existing weights
        AhpWeight::where('ahp_model_id', $ahpModel->id)->delete();

        // Save new weights
        foreach ($criteriaIds as $index => $criteriaId) {
            AhpWeight::create([
                'id' => Str::ulid(),
                'ahp_model_id' => $ahpModel->id,
                'criteria_node_id' => $criteriaId,
                'local_weight' => $weights[$index],
                'global_weight' => $weights[$index], // For root level, local = global
            ]);
        }

        // Update model with consistency ratio
        $ahpModel->update([
            'consistency_ratio' => $cr,
        ]);
    }

    public function finalize(Request $request, AhpModel $ahpModel)
    {
        // Check consistency ratio
        if ($ahpModel->consistency_ratio > 0.1) {
            return back()->with('error', 'Consistency Ratio harus ≤ 0.1 untuk finalisasi.');
        }

        $ahpModel->update([
            'status' => AhpModelStatus::Finalized,
            'finalized_at' => now(),
            'finalized_by' => auth()->id(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'finalize_ahp_model',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => "Finalized AHP model with CR: {$ahpModel->consistency_ratio}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Model AHP berhasil difinalisasi.');
    }

    public function reset(AhpModel $ahpModel)
    {
        if ($ahpModel->status === AhpModelStatus::Finalized) {
            return back()->with('error', 'Model AHP yang sudah final tidak dapat direset.');
        }

        // Delete comparisons and weights
        AhpComparison::where('ahp_model_id', $ahpModel->id)->delete();
        AhpWeight::where('ahp_model_id', $ahpModel->id)->delete();

        $ahpModel->update([
            'consistency_ratio' => null,
            'status' => AhpModelStatus::Draft,
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'reset_ahp_model',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => "Reset AHP model",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Model AHP berhasil direset.');
    }
}
