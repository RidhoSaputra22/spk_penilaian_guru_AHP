<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AhpWeight;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\PeriodResult;
use App\Models\TeacherCriteriaScore;
use App\Models\TeacherPeriodResult;
use App\Services\ResultCalculationService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $periods = AssessmentPeriod::orderBy('created_at', 'desc')->get();
        $selectedPeriod = null;
        $results = collect();
        $criteria = collect();
        $criteriaAverages = [];
        $statsData = [];

        if ($request->filled('period_id')) {
            $selectedPeriod = AssessmentPeriod::find($request->period_id);

            if ($selectedPeriod) {
                $periodResult = PeriodResult::where('assessment_period_id', $selectedPeriod->id)->first();

                if ($periodResult) {
                    $query = TeacherPeriodResult::with(['teacher.user', 'teacher.groups', 'criteriaScores.criteriaNode'])
                        ->where('period_result_id', $periodResult->id);

                    // Search filter
                    if ($request->filled('search')) {
                        $search = $request->search;
                        $query->whereHas('teacher.user', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                    }

                    // Grade filter
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
                        $query->whereHas('teacher.groups', function ($q) use ($request) {
                            $q->where('teacher_groups.id', $request->group);
                        });
                    }

                    // Get all results for ranking calculation
                    $allResults = $query->orderByDesc('final_score')->get();

                    // Calculate stats from all results
                    $statsData = [
                        'avg_score' => $allResults->avg('final_score'),
                        'max_score' => $allResults->max('final_score'),
                        'min_score' => $allResults->min('final_score'),
                    ];

                    // Paginate
                    $results = $query->orderByDesc('final_score')->paginate(5)->withQueryString();

                    // Add rank and grade based on position in all results
                    $results->getCollection()->transform(function ($result) use ($allResults) {
                        $rank = $allResults->search(function ($item) use ($result) {
                            return $item->id === $result->id;
                        });

                        $result->rank = $rank !== false ? $rank + 1 : 0;
                        $result->grade = $this->determineGrade($result->final_score);

                        return $result;
                    });
                }

                // Get criteria for this period
                $criteriaSetId = $selectedPeriod->ahpModel?->criteria_set_id;

                if ($criteriaSetId) {
                    // Get goal node first, then get its children (criteria)
                    $goal = CriteriaNode::where('criteria_set_id', $criteriaSetId)
                        ->where('node_type', 'goal')
                        ->first();

                    $criteria = $goal ? CriteriaNode::where('parent_id', $goal->id)
                        ->where('node_type', 'criteria')
                        ->orderBy('sort_order')
                        ->get() : collect();
                } else {
                    $criteria = collect();
                }

                // Calculate criteria averages
                if (isset($periodResult) && $periodResult) {
                    foreach ($criteria as $criterion) {
                        $avgScore = TeacherCriteriaScore::whereHas('teacherPeriodResult', function ($q) use ($periodResult) {
                            $q->where('period_result_id', $periodResult->id);
                        })
                            ->where('criteria_node_id', $criterion->id)
                            ->avg('weighted_score');
                        $criteriaAverages[$criterion->id] = round($avgScore ?? 0, 2);
                    }
                }
            }
        }

        // Calculate readiness info for the selected period
        $readiness = null;
        if ($selectedPeriod) {
            $ahpModel = $selectedPeriod->ahpModel;
            $submittedCount = Assessment::where('assessment_period_id', $selectedPeriod->id)
                ->whereIn('status', ['submitted', 'finalized'])
                ->count();
            $draftCount = Assessment::where('assessment_period_id', $selectedPeriod->id)
                ->where('status', 'draft')
                ->count();
            $totalAssessments = Assessment::where('assessment_period_id', $selectedPeriod->id)->count();
            $periodResultExists = PeriodResult::where('assessment_period_id', $selectedPeriod->id)->exists();

            $readiness = [
                'ahp_finalized' => $ahpModel && $ahpModel->status === 'finalized',
                'ahp_status' => $ahpModel?->status ?? 'tidak ada',
                'ahp_weight_count' => $ahpModel ? AhpWeight::where('ahp_model_id', $ahpModel->id)->count() : 0,
                'submitted_count' => $submittedCount,
                'draft_count' => $draftCount,
                'total_assessments' => $totalAssessments,
                'can_calculate' => ($ahpModel && $ahpModel->status === 'finalized' && $submittedCount > 0),
                'has_results' => $periodResultExists,
            ];
        }

        // dd($results);

        return view('admin.results.index', compact(
            'periods',
            'selectedPeriod',
            'results',
            'criteria',
            'criteriaAverages',
            'statsData',
            'readiness'
        ));
    }

    public function show(TeacherPeriodResult $result)
    {
        $result->load(['teacher.user', 'periodResult.period', 'criteriaScores.criteriaNode']);

        $period = $result->periodResult?->period;

        // Get criteria nodes (actual criteria, not goal nodes)
        $criteriaScores = collect();
        if ($period?->ahpModel?->criteria_set_id) {
            $goal = CriteriaNode::where('criteria_set_id', $period->ahpModel->criteria_set_id)
                ->where('node_type', 'goal')
                ->first();

            if ($goal) {
                $criteria = CriteriaNode::where('parent_id', $goal->id)
                    ->where('node_type', 'criteria')
                    ->orderBy('sort_order')
                    ->get();

                // Map criteria to their scores
                $criteriaScores = $criteria->map(function ($criterion) use ($result) {
                    $score = $result->criteriaScores->firstWhere('criteria_node_id', $criterion->id);

                    return [
                        'name' => $criterion->name,
                        'code' => $criterion->code ?? '',
                        'raw_score' => (float) ($score->raw_score ?? 0),
                        'weight' => (float) ($score->weight ?? 0),
                        'weighted_score' => (float) ($score->weighted_score ?? 0),
                    ];
                });
            }
        }

        // Grade
        $grade = $this->determineGrade((float) $result->final_score);

        // Total teachers in same period
        $totalTeachers = TeacherPeriodResult::where('period_result_id', $result->period_result_id)->count();

        // Get assessments for this teacher in this period
        $assessments = collect();
        if ($period) {
            $assessments = Assessment::with(['assessor.user'])
                ->where('teacher_profile_id', $result->teacher_profile_id)
                ->where('assessment_period_id', $period->id)
                ->whereIn('status', ['submitted', 'finalized'])
                ->get();
        }

        // Get historical results for this teacher
        $historicalResults = TeacherPeriodResult::where('teacher_profile_id', $result->teacher_profile_id)
            ->with('periodResult.period')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($r) {
                $r->grade = $this->determineGrade((float) $r->final_score);

                return $r;
            });

        return view('admin.results.show', compact(
            'result',
            'period',
            'criteriaScores',
            'grade',
            'totalTeachers',
            'assessments',
            'historicalResults'
        ));
    }

    public function export(Request $request)
    {
        if (! $request->filled('period_id')) {
            return back()->with('error', 'Silakan pilih periode terlebih dahulu.');
        }

        $period = AssessmentPeriod::find($request->period_id);
        if (! $period) {
            return back()->with('error', 'Periode tidak ditemukan.');
        }

        $periodResult = PeriodResult::where('assessment_period_id', $period->id)->first();
        if (! $periodResult) {
            return back()->with('error', 'Hasil belum dihitung untuk periode ini.');
        }

        $results = TeacherPeriodResult::with(['teacher.user', 'teacher.groups', 'criteriaScores.criteriaNode'])
            ->where('period_result_id', $periodResult->id)
            ->orderByDesc('final_score')
            ->get()
            ->map(function ($result, $index) {
                $result->rank = $index + 1;
                $result->grade = $this->determineGrade($result->final_score);

                return $result;
            });

        // Get criteria
        $criteriaSetId = $period->ahpModel?->criteria_set_id;
        $criteria = collect();

        if ($criteriaSetId) {
            $goal = CriteriaNode::where('criteria_set_id', $criteriaSetId)
                ->where('node_type', 'goal')
                ->first();

            $criteria = $goal ? CriteriaNode::where('parent_id', $goal->id)
                ->where('node_type', 'criteria')
                ->orderBy('sort_order')
                ->get() : collect();
        }

        $format = $request->get('format', 'excel');

        if ($format === 'pdf') {
            return $this->exportPdf($period, $results, $criteria);
        } else {
            return $this->exportExcel($period, $results, $criteria);
        }
    }

    private function exportPdf($period, $results, $criteria)
    {
        // For now, return a simple HTML that can be printed as PDF
        $html = view('admin.results.export-pdf', compact('period', 'results', 'criteria'))->render();

        // You can use a PDF library like dompdf or wkhtmltopdf here
        // For simplicity, we'll return HTML that can be printed
        return response($html)
            ->header('Content-Type', 'text/html')
            ->header('Content-Disposition', 'inline; filename="hasil_penilaian_'.$period->name.'.html"');
    }

    private function exportExcel($period, $results, $criteria)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="hasil_penilaian_'.$period->name.'.csv"',
        ];

        $callback = function () use ($results, $criteria) {
            $file = fopen('php://output', 'w');

            // Header row
            $headerRow = [
                'Ranking',
                'Nama Guru',
                'NIP',
            ];

            // Add criteria columns
            foreach ($criteria as $criterion) {
                $headerRow[] = $criterion->name;
            }

            $headerRow[] = 'Skor Akhir';
            $headerRow[] = 'Grade';

            fputcsv($file, $headerRow);

            // Data rows
            foreach ($results as $result) {
                $row = [
                    $result->rank,
                    $result->teacher->user->name ?? '-',
                    $result->teacher->nip ?? '-',
                ];

                // Add criteria scores
                foreach ($criteria as $criterion) {
                    $criteriaScore = $result->criteriaScores->firstWhere('criteria_node_id', $criterion->id);
                    $row[] = number_format($criteriaScore->weighted_score ?? 0, 2);
                }

                $row[] = number_format($result->final_score, 2);
                $row[] = $result->grade;

                fputcsv($file, $row);
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

        $period = AssessmentPeriod::with(['ahpModel.weights', 'ahpModel.criteriaSet.nodes'])
            ->findOrFail($validated['period_id']);

        $service = new ResultCalculationService;
        $result = $service->calculate($period);

        if (! $result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    protected function determineGrade(float $score): string
    {
        if ($score >= 90) {
            return 'A';
        }
        if ($score >= 80) {
            return 'B';
        }
        if ($score >= 70) {
            return 'C';
        }
        if ($score >= 60) {
            return 'D';
        }

        return 'E';
    }
}
