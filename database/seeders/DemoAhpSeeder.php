<?php

namespace Database\Seeders;

use App\Models\AhpComparison;
use App\Models\AhpModel;
use App\Models\AhpWeight;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoAhpSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        $admin = User::where('email', 'admin@demo.local')->first();

        if (!$institution || !$admin) {
            return;
        }

        $period = AssessmentPeriod::where('institution_id', $institution->id)->where('meta->demo', true)->first();
        $set = CriteriaSet::where('institution_id', $institution->id)->where('name', 'Kriteria Kinerja Guru')->first();

        if (!$period || !$set) {
            return;
        }

        if (AhpModel::where('assessment_period_id', $period->id)->exists()) {
            return; // already seeded
        }

        DB::transaction(function () use ($period, $set, $admin) {
            $model = AhpModel::create([
                'assessment_period_id' => $period->id,
                'criteria_set_id' => $set->id,
                'status' => 'finalized',
                'consistency_ratio' => 0.05,
                'finalized_at' => now(),
                'created_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            $goal = CriteriaNode::where('criteria_set_id', $set->id)->where('node_type', 'goal')->first();
            if (!$goal) {
                return;
            }

            $criteria = CriteriaNode::where('criteria_set_id', $set->id)
                ->where('parent_id', $goal->id)
                ->where('node_type', 'criteria')
                ->orderBy('sort_order')
                ->get();

            // 1) weights for criteria level (sum=1)
            $critWeights = $this->randomNormalizedWeights($criteria->count());
            foreach ($criteria as $idx => $node) {
                AhpWeight::create([
                    'ahp_model_id' => $model->id,
                    'criteria_node_id' => $node->id,
                    'parent_node_id' => $goal->id,
                    'level' => 'criteria',
                    'weight' => $critWeights[$idx],
                    'meta' => ['demo' => true],
                ]);
            }

            $this->seedComparisons($model->id, $goal->id, $criteria->all(), $critWeights, $admin->id);

            // 2) weights for each subcriteria group under a criterion
            foreach ($criteria as $idx => $cNode) {
                $subs = CriteriaNode::where('parent_id', $cNode->id)->where('node_type', 'subcriteria')->orderBy('sort_order')->get();
                if ($subs->isEmpty()) {
                    continue;
                }

                $subWeights = $this->randomNormalizedWeights($subs->count());
                foreach ($subs as $sIdx => $sNode) {
                    AhpWeight::create([
                        'ahp_model_id' => $model->id,
                        'criteria_node_id' => $sNode->id,
                        'parent_node_id' => $cNode->id,
                        'level' => 'subcriteria',
                        'weight' => $subWeights[$sIdx],
                        'meta' => [
                            'demo' => true,
                            // handy for later reporting (global weight at subcriteria level)
                            'global_weight' => (float) $critWeights[$idx] * (float) $subWeights[$sIdx],
                        ],
                    ]);
                }

                $this->seedComparisons($model->id, $cNode->id, $subs->all(), $subWeights, $admin->id);
            }
        });
    }

    /**
     * @return float[]
     */
    private function randomNormalizedWeights(int $n): array
    {
        if ($n <= 0) return [];

        $raw = [];
        $sum = 0.0;
        for ($i = 0; $i < $n; $i++) {
            $v = mt_rand(10, 100) / 100; // 0.10 - 1.00
            $raw[] = $v;
            $sum += $v;
        }

        return array_map(fn ($v) => round($v / $sum, 12), $raw);
    }

    /**
     * Seeds AHP pairwise comparisons using weight ratios, clamped to Saaty range [1/9..9].
     *
     * @param string $ahpModelId
     * @param string|null $parentNodeId
     * @param array<int, \App\Models\CriteriaNode> $nodes
     * @param float[] $weights
     * @param string $createdBy
     */
    private function seedComparisons(string $ahpModelId, ?string $parentNodeId, array $nodes, array $weights, string $createdBy): void
    {
        $count = count($nodes);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $ratio = $weights[$i] / max($weights[$j], 1e-12);
                // clamp
                $ratio = max(1/9, min(9, $ratio));

                AhpComparison::create([
                    'ahp_model_id' => $ahpModelId,
                    'parent_node_id' => $parentNodeId,
                    'node_a_id' => $nodes[$i]->id,
                    'node_b_id' => $nodes[$j]->id,
                    'value' => round($ratio, 6),
                    'created_by' => $createdBy,
                ]);
            }
        }
    }
}
