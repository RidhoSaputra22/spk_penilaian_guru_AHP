<?php

namespace App\Http\Controllers\Teacher;

use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\TeacherPeriodResult;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Compute a grade label from a numeric score.
     */
    private function computeGrade(float $score): string
    {
        if ($score >= 90) return 'A';
        if ($score >= 80) return 'B';
        if ($score >= 70) return 'C';
        if ($score >= 60) return 'D';
        return 'E';
    }

    /**
     * Transform criteria scores Eloquent collection into an array for views.
     */
    private function transformCriteriaScores($criteriaScoresCollection): array
    {
        $result = [];
        foreach ($criteriaScoresCollection as $score) {
            $name = $score->criteriaNode->name ?? ('Kriteria ' . ($score->criteriaNode->code ?? '?'));
            $result[] = [
                'name' => $name,
                'code' => $score->criteriaNode->code ?? '',
                'raw_score' => (float) $score->raw_score,
                'weight' => (float) $score->weight,
                'weighted_score' => (float) $score->weighted_score,
            ];
        }
        return $result;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        if (! $teacherProfile) {
            return view('teacher.results.index', [
                'results' => collect(),
                'periods' => collect(),
            ]);
        }

        $query = TeacherPeriodResult::with(['periodResult.period', 'criteriaScores.criteriaNode'])
            ->where('teacher_profile_id', $teacherProfile->id)
            ->whereHas('periodResult') // ensure periodResult exists
            ->orderBy('created_at', 'desc');

        // Filter by period
        if ($request->filled('period_id')) {
            $query->whereHas('periodResult', function ($q) use ($request) {
                $q->where('assessment_period_id', $request->period_id);
            });
        }

        $results = $query->paginate(10);

        // Add computed grade and total_teachers to each result
        $results->getCollection()->transform(function ($result) {
            $result->grade = $this->computeGrade((float) $result->final_score);
            // Count total teachers in same period result
            $result->total_teachers = TeacherPeriodResult::where('period_result_id', $result->period_result_id)->count();
            return $result;
        });

        // Get all periods for filter
        $periods = AssessmentPeriod::whereHas('teacherResults', function ($q) use ($teacherProfile) {
            $q->where('teacher_profile_id', $teacherProfile->id);
        })->orderBy('created_at', 'desc')->get();

        return view('teacher.results.index', compact('results', 'periods'));
    }

    public function show(TeacherPeriodResult $result)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($result->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses ke hasil ini.');
        }

        $result->load(['periodResult.period', 'criteriaScores.criteriaNode']);

        // Get the assessment_period_id from periodResult
        $assessmentPeriodId = $result->periodResult?->assessment_period_id;

        // Get assessments for this period
        $assessments = collect();
        if ($assessmentPeriodId) {
            $assessments = Assessment::with(['assessor.user'])
                ->where('teacher_profile_id', $teacherProfile->id)
                ->where('assessment_period_id', $assessmentPeriodId)
                ->whereIn('status', ['submitted', 'finalized'])
                ->get();
        }

        // Transform criteria scores into array for the view
        $criteriaScores = $this->transformCriteriaScores($result->criteriaScores ?? collect());

        // Compute grade
        $grade = $this->computeGrade((float) $result->final_score);

        // Total teachers in this period result
        $totalTeachers = TeacherPeriodResult::where('period_result_id', $result->period_result_id)->count();

        // Period info via periodResult
        $period = $result->periodResult?->period;

        return view('teacher.results.show', compact(
            'result',
            'assessments',
            'criteriaScores',
            'grade',
            'totalTeachers',
            'period'
        ));
    }

    public function download(TeacherPeriodResult $result)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($result->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses ke hasil ini.');
        }

        $result->load(['periodResult.period', 'criteriaScores.criteriaNode']);

        // Get the assessment_period_id from periodResult
        $assessmentPeriodId = $result->periodResult?->assessment_period_id;

        // Get assessments
        $assessments = collect();
        if ($assessmentPeriodId) {
            $assessments = Assessment::with(['assessor.user'])
                ->where('teacher_profile_id', $teacherProfile->id)
                ->where('assessment_period_id', $assessmentPeriodId)
                ->whereIn('status', ['submitted', 'finalized'])
                ->get();
        }

        // Transform criteria scores
        $criteriaScores = $this->transformCriteriaScores($result->criteriaScores ?? collect());

        // Computed values
        $grade = $this->computeGrade((float) $result->final_score);
        $totalTeachers = TeacherPeriodResult::where('period_result_id', $result->period_result_id)->count();
        $period = $result->periodResult?->period;

        // Check if DomPDF is available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('teacher.results.pdf', [
                'result' => $result,
                'teacher' => $teacherProfile,
                'user' => $user,
                'assessments' => $assessments,
                'criteriaScores' => $criteriaScores,
                'grade' => $grade,
                'totalTeachers' => $totalTeachers,
                'period' => $period,
            ]);

            $filename = 'Hasil_Penilaian_' . str_replace(' ', '_', $user->name) . '_' . ($period->name ?? 'hasil') . '.pdf';

            return $pdf->download($filename);
        }

        // Fallback: return HTML view for printing
        return view('teacher.results.pdf', [
            'result' => $result,
            'teacher' => $teacherProfile,
            'user' => $user,
            'assessments' => $assessments,
            'criteriaScores' => $criteriaScores,
            'grade' => $grade,
            'totalTeachers' => $totalTeachers,
            'period' => $period,
        ]);
    }
}
