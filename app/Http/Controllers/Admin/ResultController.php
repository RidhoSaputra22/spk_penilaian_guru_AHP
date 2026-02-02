<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentPeriod;
use App\Models\PeriodResult;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherCriteriaScore;
use App\Models\CriteriaNode;
use App\Models\AhpWeight;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $periods = AssessmentPeriod::where('institution_id', $institution?->id)
            ->orderByDesc('scoring_open_at')
            ->get();

        $selectedPeriod = null;
        $results = collect();
        $criteria = collect();
        $criteriaAverages = [];

        if ($request->filled('period')) {
            $selectedPeriod = $periods->firstWhere('id', $request->period);
        } else {
            $selectedPeriod = $periods->firstWhere('status', 'closed')
                ?? $periods->firstWhere('status', 'open')
                ?? $periods->first();
        }

        if ($selectedPeriod) {
            // Get or create period result
            $periodResult = PeriodResult::where('assessment_period_id', $selectedPeriod->id)->first();

            if ($periodResult) {
                $query = TeacherPeriodResult::with(['teacher.user', 'teacher.teacherGroup'])
                    ->where('period_result_id', $periodResult->id);

                // Search filter
                if ($request->filled('search')) {
                    $search = $request->search;
                    $query->whereHas('teacher.user', function($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%");
                    });
                }

                // Grade filter - calculated from score
                if ($request->filled('grade')) {
                    $gradeRanges = [
                        'A' => [90, 100],
                        'B' => [80, 89.99],
                        'C' => [70, 79.99],
                        'D' => [60, 69.99],
                        'E' => [0, 59.99],
                    ];
                    if (isset($gradeRanges[$request->grade])) {
                        $range = $gradeRanges[$request->grade];
                        $query->whereBetween('final_score', $range);
                    }
                }

                // Group filter
                if ($request->filled('group')) {
                    $query->whereHas('teacher', function($q) use ($request) {
                        $q->where('teacher_group_id', $request->group);
                    });
                }

                $results = $query->orderByDesc('final_score')->get();

                // Add rank and grade
                $results = $results->map(function($result, $index) {
                    $result->rank = $index + 1;
                    $result->grade = $this->determineGrade($result->final_score);
                    return $result;
                });
            }

            // Get criteria for this period (via AHP model)
            $criteriaSetId = $selectedPeriod->ahpModel?->criteria_set_id;
            $criteria = $criteriaSetId ? CriteriaNode::where('criteria_set_id', $criteriaSetId)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get() : collect();

            // Calculate criteria averages from teacher criteria scores
            if ($periodResult) {
                foreach ($criteria as $criterion) {
                    $avgScore = TeacherCriteriaScore::whereHas('teacherPeriodResult', function($q) use ($periodResult) {
                        $q->where('period_result_id', $periodResult->id);
                    })
                    ->where('criteria_node_id', $criterion->id)
                    ->avg('weighted_score');
                    $criteriaAverages[$criterion->id] = round($avgScore ?? 0, 2);
                }
            }
        }

        return view('admin.results.index', compact(
            'periods',
            'selectedPeriod',
            'results',
            'criteria',
            'criteriaAverages'
        ));
    }

    public function show(TeacherPeriodResult $result)
    {
        $result->load([
            'teacher.user',
            'teacher.teacherGroup',
            'periodResult.period.criteriaSet.criteriaNodes',
            'periodResult.period.ahpModel.weights',
            'criteriaScores.criteriaNode',
        ]);

        $period = $result->periodResult->period;

        // Get AHP weights
        $weights = [];
        if ($period->ahpModel) {
            $weights = AhpWeight::where('ahp_model_id', $period->ahpModel->id)
                ->pluck('global_weight', 'criteria_node_id')
                ->toArray();
        }

        // Get root criteria with their scores
        $rootCriteria = CriteriaNode::where('criteria_set_id', $period->criteria_set_id)
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get()
            ->map(function($criterion) use ($result, $weights) {
                $score = $result->criteriaScores->firstWhere('criteria_node_id', $criterion->id);
                $weight = $weights[$criterion->id] ?? 0;

                return [
                    'criterion' => $criterion,
                    'raw_score' => round($score->raw_score ?? 0, 2),
                    'weight' => round($weight * 100, 2),
                    'weighted_score' => round($score->weighted_score ?? 0, 4),
                ];
            });

        // Historical results
        $historicalResults = TeacherPeriodResult::with('periodResult.period')
            ->where('teacher_profile_id', $result->teacher_profile_id)
            ->where('id', '!=', $result->id)
            ->orderByDesc('created_at')
            ->take(5)
            ->get();

        // Add grade to result
        $result->grade = $this->determineGrade($result->final_score);

        return view('admin.results.show', compact(
            'result',
            'rootCriteria',
            'historicalResults'
        ));
    }

    public function export(Request $request)
    {
        $periodId = $request->input('period');

        $period = AssessmentPeriod::findOrFail($periodId);
        $periodResult = PeriodResult::where('assessment_period_id', $period->id)->first();

        if (!$periodResult) {
            return back()->with('error', 'Tidak ada hasil untuk diekspor.');
        }

        $results = TeacherPeriodResult::with(['teacher.user', 'teacher.teacherGroup'])
            ->where('period_result_id', $periodResult->id)
            ->orderByDesc('final_score')
            ->get();

        $filename = "hasil_penilaian_" . Str::slug($period->name) . "_" . date('Y-m-d') . ".csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($results) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Ranking',
                'Nama Guru',
                'NIP',
                'Kelompok',
                'Skor Akhir',
                'Grade',
            ]);

            // Data rows
            foreach ($results as $index => $result) {
                fputcsv($file, [
                    $index + 1,
                    $result->teacher->user->name ?? '-',
                    $result->teacher->nip ?? '-',
                    $result->teacher->teacherGroup->name ?? '-',
                    number_format($result->final_score, 4),
                    $this->determineGrade($result->final_score),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function calculate(Request $request)
    {
        $validated = $request->validate([
            'period_id' => ['required', 'exists:assessment_periods,id'],
        ]);

        $period = AssessmentPeriod::with(['ahpModel.weights', 'criteriaSet.criteriaNodes'])
            ->findOrFail($validated['period_id']);

        if (!$period->ahpModel || $period->ahpModel->status !== 'finalized') {
            return back()->with('error', 'Model AHP harus difinalisasi terlebih dahulu.');
        }

        $weights = AhpWeight::where('ahp_model_id', $period->ahpModel->id)
            ->pluck('global_weight', 'criteria_node_id');

        // Get all completed assessments for this period
        $assessments = Assessment::where('assessment_period_id', $period->id)
            ->where('status', 'submitted')
            ->with('itemValues')
            ->get()
            ->groupBy('teacher_profile_id');

        if ($assessments->isEmpty()) {
            return back()->with('error', 'Tidak ada penilaian yang sudah selesai.');
        }

        DB::beginTransaction();
        try {
            // Get or create period result
            $periodResult = PeriodResult::firstOrCreate(
                ['assessment_period_id' => $period->id],
                [
                    'id' => Str::ulid(),
                    'calculated_at' => now(),
                    'status' => 'calculated',
                ]
            );

            foreach ($assessments as $teacherId => $teacherAssessments) {
                // Calculate weighted score for each teacher
                $totalWeightedScore = 0;
                $criteriaScores = [];

                foreach ($weights as $criteriaId => $weight) {
                    // Average score from all assessors for this criteria
                    $avgScore = $teacherAssessments->flatMap->itemValues
                        ->where('criteria_node_id', $criteriaId)
                        ->avg('score') ?? 0;

                    $weightedScore = $avgScore * $weight;
                    $totalWeightedScore += $weightedScore;

                    $criteriaScores[$criteriaId] = [
                        'raw_score' => $avgScore,
                        'weighted_score' => $weightedScore,
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
                        'final_score' => $totalWeightedScore,
                        'details' => ['calculated_at' => now()->toIso8601String()],
                    ]
                );

                // Save criteria scores
                foreach ($criteriaScores as $criteriaId => $scores) {
                    TeacherCriteriaScore::updateOrCreate(
                        [
                            'teacher_period_result_id' => $teacherResult->id,
                            'criteria_node_id' => $criteriaId,
                        ],
                        [
                            'id' => Str::ulid(),
                            'raw_score' => $scores['raw_score'],
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

            $periodResult->update(['calculated_at' => now()]);

            DB::commit();

            return back()->with('success', 'Perhitungan hasil penilaian berhasil dilakukan untuk ' . $assessments->count() . ' guru.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    protected function determineGrade(float $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }
}
