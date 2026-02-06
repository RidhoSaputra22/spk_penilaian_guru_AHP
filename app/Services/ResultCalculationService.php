<?php

namespace App\Services;

use App\Models\AhpWeight;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\PeriodResult;
use App\Models\TeacherCriteriaScore;
use App\Models\TeacherPeriodResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResultCalculationService
{
    /**
     * Calculate results for a given assessment period.
     *
     * Flow:
     * 1. Load the finalized AHP weights for the period's AHP model.
     * 2. Collect all submitted assessments grouped by teacher.
     * 3. For each teacher, for each criteria node with a weight:
     *    - Find all KPI form items linked to that criteria_node_id.
     *    - Average the score_value from AssessmentItemValue for those items across all assessors.
     *    - Multiply average score by AHP weight to get weighted_score.
     * 4. Sum all weighted_scores to get final_score.
     * 5. Rank teachers by final_score descending.
     *
     * @return array{success: bool, message: string, teacher_count: int}
     */
    public function calculate(AssessmentPeriod $period): array
    {
        // Validate AHP model is finalized
        if (! $period->ahpModel || $period->ahpModel->status !== 'finalized') {
            return [
                'success' => false,
                'message' => 'Model AHP harus difinalisasi terlebih dahulu.',
                'teacher_count' => 0,
            ];
        }

        // Get AHP weights keyed by criteria_node_id
        $weights = AhpWeight::where('ahp_model_id', $period->ahpModel->id)
            ->pluck('weight', 'criteria_node_id');

        if ($weights->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada bobot AHP yang tersedia.',
                'teacher_count' => 0,
            ];
        }

        // Get all submitted assessments for this period, grouped by teacher
        $assessments = Assessment::where('assessment_period_id', $period->id)
            ->where('status', 'submitted')
            ->with('itemValues.formItem')
            ->get()
            ->groupBy('teacher_profile_id');

        if ($assessments->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada penilaian yang sudah selesai (submitted).',
                'teacher_count' => 0,
            ];
        }

        DB::beginTransaction();
        try {
            // Get or create period result
            $periodResult = PeriodResult::firstOrCreate(
                ['assessment_period_id' => $period->id],
                [
                    'id' => Str::ulid(),
                    'generated_at' => now(),
                    'status' => 'generated',
                ]
            );

            foreach ($assessments as $teacherId => $teacherAssessments) {
                $totalWeightedScore = 0;
                $criteriaScores = [];

                foreach ($weights as $criteriaNodeId => $weight) {
                    // Get the criteria node to determine if it has children
                    $criteriaNode = CriteriaNode::find($criteriaNodeId);

                    if (! $criteriaNode) {
                        continue;
                    }

                    // Collect all criteria_node_ids to match against form items
                    // This includes the criteria node itself AND all its sub-criteria children
                    $nodeIds = collect([$criteriaNodeId]);
                    $childIds = CriteriaNode::where('parent_id', $criteriaNodeId)
                        ->pluck('id');
                    $nodeIds = $nodeIds->merge($childIds);

                    // Average score from all assessors for items linked to these criteria nodes
                    $avgScore = $teacherAssessments->flatMap->itemValues
                        ->filter(function ($itemValue) use ($nodeIds) {
                            return $itemValue->formItem
                                && $nodeIds->contains($itemValue->formItem->criteria_node_id);
                        })
                        ->avg('score_value') ?? 0;

                    $weightedScore = $avgScore * $weight;
                    $totalWeightedScore += $weightedScore;

                    $criteriaScores[$criteriaNodeId] = [
                        'raw_score' => round($avgScore, 4),
                        'weight' => $weight,
                        'weighted_score' => round($weightedScore, 4),
                    ];
                }

                // Create or update teacher period result
                $teacherResult = TeacherPeriodResult::updateOrCreate(
                    [
                        'period_result_id' => $periodResult->id,
                        'teacher_profile_id' => $teacherId,
                    ],
                    [
                        'id' => Str::ulid(),
                        'final_score' => round($totalWeightedScore, 2),
                        'details' => ['calculated_at' => now()->toIso8601String()],
                    ]
                );

                // Save criteria scores
                foreach ($criteriaScores as $criteriaNodeId => $scores) {
                    TeacherCriteriaScore::updateOrCreate(
                        [
                            'teacher_period_result_id' => $teacherResult->id,
                            'criteria_node_id' => $criteriaNodeId,
                        ],
                        [
                            'id' => Str::ulid(),
                            'raw_score' => $scores['raw_score'],
                            'weight' => $scores['weight'],
                            'weighted_score' => $scores['weighted_score'],
                        ]
                    );
                }
            }

            // Update ranks
            $allResults = TeacherPeriodResult::where('period_result_id', $periodResult->id)
                ->orderByDesc('final_score')
                ->get();

            foreach ($allResults as $index => $result) {
                $result->update(['rank' => $index + 1]);
            }

            $periodResult->update(['generated_at' => now()]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Perhitungan hasil penilaian berhasil dilakukan untuk ' . $assessments->count() . ' guru.',
                'teacher_count' => $assessments->count(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'teacher_count' => 0,
            ];
        }
    }
}
