<?php

namespace Database\Seeders;

use App\Models\AhpModel;
use App\Models\AhpWeight;
use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Institution;
use App\Models\PeriodResult;
use App\Models\TeacherCriteriaScore;
use App\Models\TeacherPeriodResult;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoResultsSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        if (!$institution) return;

        $period = AssessmentPeriod::where('institution_id', $institution->id)->where('meta->demo', true)->first();
        if (!$period) return;

        if (PeriodResult::where('assessment_period_id', $period->id)->exists()) {
            return; // already generated
        }

        $set = CriteriaSet::where('institution_id', $institution->id)->where('name', 'Kriteria Kinerja Guru')->first();
        $ahp = AhpModel::where('assessment_period_id', $period->id)->first();

        if (!$set || !$ahp) return;

        $goal = CriteriaNode::where('criteria_set_id', $set->id)->where('node_type', 'goal')->first();
        if (!$goal) return;

        // Build weight maps
        $critWeights = AhpWeight::where('ahp_model_id', $ahp->id)->where('level', 'criteria')->get()->keyBy('criteria_node_id');
        $subWeights  = AhpWeight::where('ahp_model_id', $ahp->id)->where('level', 'subcriteria')->get()->keyBy('criteria_node_id');

        // Indicators grouped by subcriteria
        $indicatorNodes = CriteriaNode::where('criteria_set_id', $set->id)->where('node_type', 'indicator')->get();
        $indicatorsBySub = $indicatorNodes->groupBy('parent_id');

        DB::transaction(function () use ($period, $set, $goal, $critWeights, $subWeights, $indicatorsBySub) {
            $periodResult = PeriodResult::create([
                'assessment_period_id' => $period->id,
                'status' => 'generated',
                'generated_at' => now(),
                'published_at' => null,
                'generated_by' => null,
                'meta' => ['demo' => true],
            ]);

            $assessments = Assessment::where('assessment_period_id', $period->id)
                ->with(['teacher.user', 'itemValues.formItem'])
                ->get();

            $teacherResults = [];

            foreach ($assessments as $assessment) {
                $teacher = $assessment->teacher;

                // maps for raw averages
                $criterionTotals = [];
                $criterionCounts = [];
                $subTotals = [];
                $subCounts = [];

                $score = 0.0;

                foreach ($assessment->itemValues as $val) {
                    $item = $val->formItem;
                    if (!$item || !$item->criteria_node_id) continue;

                    /** @var \App\Models\CriteriaNode|null $indicator */
                    $indicator = CriteriaNode::find($item->criteria_node_id);
                    if (!$indicator) continue;

                    $subId = $indicator->parent_id;
                    if (!$subId) continue;

                    $subNode = CriteriaNode::find($subId);
                    if (!$subNode) continue;

                    $critId = $subNode->parent_id;
                    if (!$critId) continue;

                    $critW = (float) ($critWeights[$critId]->weight ?? 0.0);
                    $subW  = (float) ($subWeights[$subId]->weight ?? 0.0);
                    $globalSubW = $critW * $subW;

                    $indCount = (int) ($indicatorsBySub[$subId]?->count() ?? 0);
                    if ($indCount <= 0) continue;

                    $indicatorW = $globalSubW / $indCount;

                    $value = (float) ($val->score_value ?? $val->value_number ?? 0.0);
                    $normalized = $value / 4.0; // 1..4 -> 0.25..1.0

                    $score += $normalized * $indicatorW;

                    // raw aggregates
                    $criterionTotals[$critId] = ($criterionTotals[$critId] ?? 0) + $value;
                    $criterionCounts[$critId] = ($criterionCounts[$critId] ?? 0) + 1;

                    $subTotals[$subId] = ($subTotals[$subId] ?? 0) + $value;
                    $subCounts[$subId] = ($subCounts[$subId] ?? 0) + 1;
                }

                $finalScore = round($score * 100, 4);

                $tpr = TeacherPeriodResult::create([
                    'period_result_id' => $periodResult->id,
                    'teacher_profile_id' => $teacher->id,
                    'final_score' => $finalScore,
                    'rank' => null,
                    'details' => [
                        'teacher_name' => $teacher->user->name ?? null,
                        'demo' => true,
                    ],
                ]);

                // Save breakdowns at criteria + subcriteria level
                foreach ($criterionTotals as $critId => $total) {
                    $raw = $total / max($criterionCounts[$critId], 1);
                    $critW = (float) ($critWeights[$critId]->weight ?? 0.0);

                    TeacherCriteriaScore::create([
                        'teacher_period_result_id' => $tpr->id,
                        'criteria_node_id' => $critId,
                        'raw_score' => round($raw, 4),
                        'weight' => round($critW, 12),
                        'weighted_score' => round(($raw / 4.0) * $critW * 100.0, 4),
                        'meta' => ['level' => 'criteria', 'demo' => true],
                    ]);
                }

                foreach ($subTotals as $subId => $total) {
                    $raw = $total / max($subCounts[$subId], 1);

                    $subNode = CriteriaNode::find($subId);
                    $critId = $subNode?->parent_id;

                    $critW = (float) ($critWeights[$critId]->weight ?? 0.0);
                    $subW  = (float) ($subWeights[$subId]->weight ?? 0.0);
                    $globalSubW = $critW * $subW;

                    TeacherCriteriaScore::create([
                        'teacher_period_result_id' => $tpr->id,
                        'criteria_node_id' => $subId,
                        'raw_score' => round($raw, 4),
                        'weight' => round($globalSubW, 12),
                        'weighted_score' => round(($raw / 4.0) * $globalSubW * 100.0, 4),
                        'meta' => ['level' => 'subcriteria', 'demo' => true],
                    ]);
                }

                $teacherResults[] = $tpr;
            }

            // Rank teachers (higher score = better rank 1)
            $sorted = TeacherPeriodResult::where('period_result_id', $periodResult->id)
                ->orderByDesc('final_score')
                ->get();

            $rank = 1;
            foreach ($sorted as $row) {
                $row->update(['rank' => $rank++]);
            }
        });
    }
}
