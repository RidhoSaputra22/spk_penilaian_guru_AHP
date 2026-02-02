<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\TeacherPeriodResult;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return view('teacher.dashboard', [
                'teacherProfile' => null,
                'stats' => [
                    'total_assessments' => 0,
                    'pending' => 0,
                    'completed' => 0,
                    'results_available' => 0,
                ],
                'activePeriods' => collect(),
                'recentAssessments' => collect(),
                'latestResults' => collect(),
            ]);
        }

        // Stats
        $stats = [
            'total_assessments' => Assessment::where('teacher_profile_id', $teacherProfile->id)->count(),
            'pending' => Assessment::where('teacher_profile_id', $teacherProfile->id)
                ->whereIn('status', ['pending', 'draft', 'in_progress'])
                ->count(),
            'completed' => Assessment::where('teacher_profile_id', $teacherProfile->id)
                ->whereIn('status', ['submitted', 'finalized'])
                ->count(),
            'results_available' => TeacherPeriodResult::where('teacher_profile_id', $teacherProfile->id)->count(),
        ];

        // Active periods where teacher is being assessed
        $activePeriods = AssessmentPeriod::whereHas('assessments', function ($query) use ($teacherProfile) {
            $query->where('teacher_profile_id', $teacherProfile->id);
        })
            ->where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Recent assessments
        $recentAssessments = Assessment::with(['period', 'assessor.user'])
            ->where('teacher_profile_id', $teacherProfile->id)
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        // Latest results
        $latestResults = TeacherPeriodResult::with('period')
            ->where('teacher_profile_id', $teacherProfile->id)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();

        // Alias for recentResults
        $recentResults = $latestResults;

        return view('teacher.dashboard', compact(
            'teacherProfile',
            'stats',
            'activePeriods',
            'recentAssessments',
            'latestResults',
            'recentResults'
        ));
    }
}
