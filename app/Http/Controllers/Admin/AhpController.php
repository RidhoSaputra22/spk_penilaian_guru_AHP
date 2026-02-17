<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AhpComparison;
use App\Models\AhpModel;
use App\Models\AhpWeight;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AhpController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        // Get periods with AHP models
        $periodsCollection = AssessmentPeriod::with(['ahpModel'])
            ->where('institution_id', $institution?->id)
            ->latest('scoring_open_at')
            ->get();

        // Format periods for select dropdown
        $periods = $periodsCollection->mapWithKeys(fn ($p) => [
            $p->id => "{$p->name} ({$p->academic_year} - {$p->semester})",
        ]);

        // Get active period or selected period
        $selectedPeriod = null;
        $ahpModel = null;
        $criteria = collect();
        $comparisons = collect();
        $weights = collect();

        if ($request->filled('period')) {
            $selectedPeriod = $periodsCollection->firstWhere('id', $request->period);
        } else {
            $selectedPeriod = $periodsCollection->firstWhere('status', 'open') ?? $periodsCollection->first();
        }

        if ($selectedPeriod) {
            $ahpModel = $selectedPeriod->ahpModel;

            if ($ahpModel) {
                // Get goal node first, then get its children (criteria)
                $goal = CriteriaNode::where('criteria_set_id', $ahpModel->criteria_set_id)
                    ->where('node_type', 'goal')
                    ->first();

                $criteria = $goal ? CriteriaNode::where('parent_id', $goal->id)
                    ->where('node_type', 'criteria')
                    ->orderBy('sort_order')
                    ->get() : collect();

                $comparisons = AhpComparison::where('ahp_model_id', $ahpModel->id)
                    ->with(['nodeA', 'nodeB'])
                    ->get();

                $weights = AhpWeight::where('ahp_model_id', $ahpModel->id)
                    ->where('level', 'criteria')
                    ->with('criteriaNode')
                    ->get();
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
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        // Auto-generate comparison pairs
        $this->generateComparisonPairs($ahpModel);

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

    /**
     * Generate pairwise comparison pairs for AHP model
     */
    protected function generateComparisonPairs(AhpModel $ahpModel)
    {
        // Get goal node
        $goal = CriteriaNode::where('criteria_set_id', $ahpModel->criteria_set_id)
            ->where('node_type', 'goal')
            ->first();

        if (! $goal) {
            return;
        }

        // Get all criteria nodes (children of goal)
        $criteria = CriteriaNode::where('parent_id', $goal->id)
            ->where('node_type', 'criteria')
            ->orderBy('sort_order')
            ->get();

        if ($criteria->count() < 2) {
            return; // Need at least 2 criteria to compare
        }

        // Generate all unique pairs (combinations)
        for ($i = 0; $i < $criteria->count(); $i++) {
            for ($j = $i + 1; $j < $criteria->count(); $j++) {
                AhpComparison::create([
                    'id' => Str::ulid(),
                    'ahp_model_id' => $ahpModel->id,
                    'parent_node_id' => $goal->id,
                    'node_a_id' => $criteria[$i]->id,
                    'node_b_id' => $criteria[$j]->id,
                    'value' => 1, // Default: equally important
                ]);
            }
        }
    }

    public function saveComparisons(Request $request, ?AhpModel $ahpModel = null)
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
            'description' => 'Saved AHP comparisons and calculated weights',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'weights' => AhpWeight::where('ahp_model_id', $ahpModel->id)->get(),
            'consistency_ratio' => $ahpModel->fresh()->consistency_ratio,
        ]);
    }

    /**
     * Store comparisons from HTML form (format: comparisons[node_a_id][node_b_id] = value)
     */
    public function storeComparisons(Request $request, AhpModel $ahpModel)
    {
        if ($ahpModel->status === 'finalized') {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('error', 'Model AHP sudah finalized, tidak dapat diubah.');
        }

        $validated = $request->validate([
            'comparisons' => ['required', 'array'],
        ]);

        // dd($validated);

        // Get goal node for parent_node_id
        $goal = CriteriaNode::where('criteria_set_id', $ahpModel->criteria_set_id)
            ->where('node_type', 'goal')
            ->first();

        // Update comparison values from form format: comparisons[node_a_id][node_b_id] = value
        foreach ($validated['comparisons'] as $nodeAId => $nodes) {
            foreach ($nodes as $nodeBId => $value) {
                $comparison = AhpComparison::where('ahp_model_id', $ahpModel->id)
                    ->where('node_a_id', $nodeAId)
                    ->where('node_b_id', $nodeBId)
                    ->first();

                if ($comparison) {
                    $comparison->update(['value' => (float) $value]);
                }
            }
        }

        // Calculate weights using AHP algorithm
        $this->calculateWeights($ahpModel);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_ahp_comparisons',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => 'Updated AHP comparisons and recalculated weights',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $cr = $ahpModel->fresh()->consistency_ratio;
        $crPercent = number_format($cr * 100, 2);

        if ($cr <= 0.1) {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('success', "Bobot berhasil dihitung! Consistency Ratio: {$crPercent}% (Valid)");
        } else {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('warning', "Bobot dihitung, tapi Consistency Ratio: {$crPercent}% melebihi 10%. Perbaiki perbandingan untuk hasil yang lebih konsisten.");
        }
    }

    protected function calculateWeights(AhpModel $ahpModel)
    {
        $comparisons = AhpComparison::where('ahp_model_id', $ahpModel->id)
            ->get();

        if ($comparisons->isEmpty()) {
            return;
        }

        // Get unique criteria IDs
        $criteriaIds = $comparisons->pluck('node_a_id')
            ->merge($comparisons->pluck('node_b_id'))
            ->unique()
            ->values();

        $n = $criteriaIds->count();
        if ($n == 0) {
            return;
        }

        // Build comparison matrix
        $matrix = [];
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $n; $j++) {
                if ($i == $j) {
                    $matrix[$i][$j] = 1;
                } else {
                    $comparison = $comparisons->first(function ($c) use ($criteriaIds, $i, $j) {
                        return $c->node_a_id == $criteriaIds[$i] && $c->node_b_id == $criteriaIds[$j];
                    });

                    if ($comparison) {
                        $matrix[$i][$j] = $comparison->value;
                    } else {
                        // Try reverse (B compared to A)
                        $reverse = $comparisons->first(function ($c) use ($criteriaIds, $i, $j) {
                            return $c->node_a_id == $criteriaIds[$j] && $c->node_b_id == $criteriaIds[$i];
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
                'weight' => $weights[$index],
                'level' => 'criteria',
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
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('error', 'Consistency Ratio harus ≤ 0.1 untuk finalisasi.');
        }

        $ahpModel->update([
            'status' => 'finalized',
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

        return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
            ->with('success', 'Model AHP berhasil difinalisasi.');
    }

    public function regenerateComparisons(Request $request, AhpModel $ahpModel)
    {
        if ($ahpModel->status === 'finalized') {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('error', 'Model AHP yang sudah final tidak dapat diubah.');
        }

        // Delete existing comparisons and weights first
        AhpComparison::where('ahp_model_id', $ahpModel->id)->delete();
        AhpWeight::where('ahp_model_id', $ahpModel->id)->delete();

        // Reset consistency ratio
        $ahpModel->update(['consistency_ratio' => null]);

        // Generate new comparison pairs
        $this->generateComparisonPairs($ahpModel);

        // Check if comparisons were generated
        $count = AhpComparison::where('ahp_model_id', $ahpModel->id)->count();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'regenerate_ahp_comparisons',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => "Regenerated {$count} comparison pairs",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($count > 0) {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('success', "Berhasil generate {$count} pasangan perbandingan. Silakan atur skala perbandingan dan klik 'Hitung Ulang Bobot'.");
        } else {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('error', 'Tidak ada kriteria untuk dibandingkan. Pastikan Set Kriteria memiliki minimal 2 kriteria di bawah node Goal.');
        }
    }

    public function reset(AhpModel $ahpModel)
    {
        if ($ahpModel->status === 'finalized') {
            return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
                ->with('error', 'Model AHP yang sudah final tidak dapat direset.');
        }

        // Delete comparisons and weights
        AhpComparison::where('ahp_model_id', $ahpModel->id)->delete();
        AhpWeight::where('ahp_model_id', $ahpModel->id)->delete();

        $ahpModel->update([
            'consistency_ratio' => null,
            'status' => 'draft',
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'reset_ahp_model',
            'entity_type' => AhpModel::class,
            'entity_id' => $ahpModel->id,
            'description' => 'Reset AHP model',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.ahp.index', ['period' => $ahpModel->assessment_period_id])
            ->with('success', 'Model AHP berhasil direset.');
    }
}
