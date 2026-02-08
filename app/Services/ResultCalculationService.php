<?php

namespace App\Services;

use App\Models\AhpWeight;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\PeriodResult;
use App\Models\ScoringScale;
use App\Models\TeacherCriteriaScore;
use App\Models\TeacherPeriodResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResultCalculationService
{
    /**
     * Calculate results for a given assessment period.
     *
     * Hierarchy: Goal → Criteria → SubCriteria → Indicator
     * Form items are linked at the Indicator level.
     * AHP weights exist at Criteria level (local, sum≈1) and SubCriteria level (local, sum≈1 per parent).
     *
     * Calculation flow:
     * 1. For each sub-criteria: collect ALL descendant node IDs (including indicators),
     *    find matching form item values, calculate average raw score across assessors.
     * 2. For each criteria: aggregate sub-criteria scores using their local weights.
     *    criteria_raw = Σ(sub_raw × sub_weight)
     * 3. criteria_weighted = criteria_raw × criteria_weight
     * 4. final_score = Σ(criteria_weighted) normalized to 0–100 scale.
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

        // Build criteria hierarchy: Goal → Criteria → SubCriteria
        $criteriaSetId = $period->ahpModel->criteria_set_id;
        $goalNode = CriteriaNode::where('criteria_set_id', $criteriaSetId)
            ->where('node_type', 'goal')
            ->first();

        if (! $goalNode) {
            return [
                'success' => false,
                'message' => 'Goal node tidak ditemukan dalam set kriteria.',
                'teacher_count' => 0,
            ];
        }

        // Get criteria (direct children of goal)
        $criteriaNodes = CriteriaNode::where('parent_id', $goalNode->id)
            ->where('node_type', 'criteria')
            ->orderBy('sort_order')
            ->get();

        // Build structure: criteria → sub-criteria → all descendant IDs
        $criteriaStructure = [];
        foreach ($criteriaNodes as $criterion) {
            $criteriaWeight = $weights[$criterion->id] ?? 0;

            $subCriteria = CriteriaNode::where('parent_id', $criterion->id)
                ->where('node_type', 'subcriteria')
                ->orderBy('sort_order')
                ->get();

            $subs = [];
            foreach ($subCriteria as $sc) {
                // Collect ALL descendant IDs (subcriteria + indicators + deeper)
                $descendantIds = $this->getAllDescendantIds($sc->id);

                $subs[] = [
                    'node' => $sc,
                    'weight' => $weights[$sc->id] ?? 0,
                    'descendant_ids' => $descendantIds,
                ];
            }

            // If no sub-criteria, collect descendants of the criteria itself
            if (empty($subs)) {
                $descendantIds = $this->getAllDescendantIds($criterion->id);
                $criteriaStructure[$criterion->id] = [
                    'node' => $criterion,
                    'weight' => $criteriaWeight,
                    'sub_criteria' => [],
                    'descendant_ids' => $descendantIds,
                ];
            } else {
                $criteriaStructure[$criterion->id] = [
                    'node' => $criterion,
                    'weight' => $criteriaWeight,
                    'sub_criteria' => $subs,
                    'descendant_ids' => collect(),
                ];
            }
        }

        // Get scoring scale max value for normalization (default 4)
        $maxScale = $this->getMaxScoreScale($period);

        // Get all submitted/finalized assessments for this period, grouped by teacher
        $assessments = Assessment::where('assessment_period_id', $period->id)
            ->whereIn('status', ['submitted', 'finalized'])
            ->with('itemValues.formItem')
            ->get()
            ->groupBy('teacher_profile_id');

        if ($assessments->isEmpty()) {
            return [
                'success' => false,
                'message' => 'Tidak ada penilaian yang sudah selesai (submitted/finalized).',
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
                $totalFinalScore = 0;
                $criteriaScoresData = [];

                // Flatten all item values for this teacher across all assessors
                $allItemValues = $teacherAssessments->flatMap->itemValues;

                foreach ($criteriaStructure as $criteriaId => $criteriaData) {
                    $criteriaWeight = $criteriaData['weight'];
                    $subCriteria = $criteriaData['sub_criteria'];

                    if (empty($subCriteria)) {
                        // No sub-criteria: calculate directly from descendant form items
                        $nodeIds = $criteriaData['descendant_ids']->push($criteriaId);
                        $avgScore = $this->calculateAvgScore($allItemValues, $nodeIds);

                        $normalizedRaw = ($maxScale > 0) ? ($avgScore / $maxScale) * 100 : 0;
                        $weightedScore = $normalizedRaw * $criteriaWeight;
                        $totalFinalScore += $weightedScore;

                        $criteriaScoresData[$criteriaId] = [
                            'raw_score' => round($normalizedRaw, 2),
                            'weight' => round($criteriaWeight, 4),
                            'weighted_score' => round($weightedScore, 2),
                        ];
                    } else {
                        // Has sub-criteria: aggregate from sub-criteria scores
                        $criteriaRawScore = 0;

                        foreach ($subCriteria as $sc) {
                            $scNodeId = $sc['node']->id;
                            $scWeight = $sc['weight'];

                            // Include the subcriteria ID itself + all descendants (indicators, etc.)
                            $nodeIds = $sc['descendant_ids']->push($scNodeId);

                            $avgScore = $this->calculateAvgScore($allItemValues, $nodeIds);
                            $normalizedSubRaw = ($maxScale > 0) ? ($avgScore / $maxScale) * 100 : 0;

                            // Weighted contribution within this criteria
                            $criteriaRawScore += $normalizedSubRaw * $scWeight;

                            // Store sub-criteria score too
                            $criteriaScoresData[$scNodeId] = [
                                'raw_score' => round($normalizedSubRaw, 2),
                                'weight' => round($scWeight, 4),
                                'weighted_score' => round($normalizedSubRaw * $scWeight, 2),
                            ];
                        }

                        // Parent criteria aggregated
                        $weightedScore = $criteriaRawScore * $criteriaWeight;
                        $totalFinalScore += $weightedScore;

                        $criteriaScoresData[$criteriaId] = [
                            'raw_score' => round($criteriaRawScore, 2),
                            'weight' => round($criteriaWeight, 4),
                            'weighted_score' => round($weightedScore, 2),
                        ];
                    }
                }

                // Create or update teacher period result
                $teacherResult = TeacherPeriodResult::updateOrCreate(
                    [
                        'period_result_id' => $periodResult->id,
                        'teacher_profile_id' => $teacherId,
                    ],
                    [
                        'id' => Str::ulid(),
                        'final_score' => round($totalFinalScore, 2),
                        'details' => [
                            'calculated_at' => now()->toIso8601String(),
                            'max_scale' => $maxScale,
                            'normalized' => true,
                        ],
                    ]
                );

                // Delete old scores and save new ones
                TeacherCriteriaScore::where('teacher_period_result_id', $teacherResult->id)->delete();

                foreach ($criteriaScoresData as $criteriaNodeId => $scores) {
                    TeacherCriteriaScore::create([
                        'id' => Str::ulid(),
                        'teacher_period_result_id' => $teacherResult->id,
                        'criteria_node_id' => $criteriaNodeId,
                        'raw_score' => $scores['raw_score'],
                        'weight' => $scores['weight'],
                        'weighted_score' => $scores['weighted_score'],
                    ]);
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
                'message' => 'Perhitungan hasil penilaian berhasil dilakukan untuk '.$assessments->count().' guru.',
                'teacher_count' => $assessments->count(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
                'teacher_count' => 0,
            ];
        }
    }

    /**
     * Get all descendant node IDs recursively for a given parent node.
     */
    private function getAllDescendantIds(string $parentId): \Illuminate\Support\Collection
    {
        $descendants = collect();
        $children = CriteriaNode::where('parent_id', $parentId)->pluck('id');

        foreach ($children as $childId) {
            $descendants->push($childId);
            $descendants = $descendants->merge($this->getAllDescendantIds($childId));
        }

        return $descendants;
    }

    /**
     * Calculate average score from item values matching the given node IDs.
     */
    private function calculateAvgScore($allItemValues, $nodeIds): float
    {
        $matchingValues = $allItemValues->filter(function ($itemValue) use ($nodeIds) {
            return $itemValue->formItem
                && $nodeIds->contains($itemValue->formItem->criteria_node_id);
        });

        return $matchingValues->avg('score_value') ?? 0;
    }

    /**
     * Get the maximum score from the scoring scale used in this period.
     */
    private function getMaxScoreScale(AssessmentPeriod $period): float
    {
        // Try to get from the scoring scale linked to the period or form template
        $scoringScale = ScoringScale::first();

        if ($scoringScale && $scoringScale->max_value) {
            return (float) $scoringScale->max_value;
        }

        // Default fallback
        return 4.0;
    }
}
