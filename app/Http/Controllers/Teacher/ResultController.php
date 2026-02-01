<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\TeacherPeriodResult;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return view('teacher.results.index', [
                'results' => collect(),
                'periods' => collect(),
            ]);
        }

        $query = TeacherPeriodResult::with(['period'])
            ->where('teacher_id', $teacherProfile->id)
            ->orderBy('created_at', 'desc');

        // Filter by period
        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        $results = $query->paginate(10);

        // Get all periods for filter
        $periods = AssessmentPeriod::whereHas('teacherResults', function ($q) use ($teacherProfile) {
            $q->where('teacher_id', $teacherProfile->id);
        })->orderBy('created_at', 'desc')->get();

        return view('teacher.results.index', compact('results', 'periods'));
    }

    public function show(TeacherPeriodResult $result)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($result->teacher_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses ke hasil ini.');
        }

        $result->load('period');

        // Get assessments for this period
        $assessments = Assessment::with([
            'assessor.user',
            'itemValues.formItem.section',
        ])
            ->where('teacher_id', $teacherProfile->id)
            ->where('period_id', $result->period_id)
            ->whereIn('status', ['submitted', 'finalized'])
            ->get();

        // Get criteria scores
        $criteriaScores = $result->criteria_scores ?? [];

        return view('teacher.results.show', compact('result', 'assessments', 'criteriaScores'));
    }

    public function download(TeacherPeriodResult $result)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Verify ownership
        if ($result->teacher_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses ke hasil ini.');
        }

        $result->load('period');

        // Get assessments
        $assessments = Assessment::with([
            'assessor.user',
            'itemValues.formItem.section',
        ])
            ->where('teacher_id', $teacherProfile->id)
            ->where('period_id', $result->period_id)
            ->whereIn('status', ['submitted', 'finalized'])
            ->get();

        $criteriaScores = $result->criteria_scores ?? [];

        // Check if DomPDF is available
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('teacher.results.pdf', [
                'result' => $result,
                'teacher' => $teacherProfile,
                'user' => $user,
                'assessments' => $assessments,
                'criteriaScores' => $criteriaScores,
            ]);

            $filename = 'Hasil_Penilaian_' . str_replace(' ', '_', $user->name) . '_' . $result->period->name . '.pdf';

            return $pdf->download($filename);
        }

        // Fallback: return HTML view for printing
        return view('teacher.results.pdf', [
            'result' => $result,
            'teacher' => $teacherProfile,
            'user' => $user,
            'assessments' => $assessments,
            'criteriaScores' => $criteriaScores,
        ]);
    }
}
