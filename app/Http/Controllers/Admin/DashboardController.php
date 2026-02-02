<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AhpModel;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherProfile;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // dd(Auth::user());
        $institution = auth()->user()->institution;

        // Get active period
        $activePeriod = AssessmentPeriod::where('institution_id', $institution?->id)
            ->where('status', 'open')
            ->first();

        // Stats
        $totalTeachers = TeacherProfile::whereHas('user', function ($q) use ($institution) {
            $q->where('institution_id', $institution?->id);
        })->count();

        $totalAssessors = AssessorProfile::whereHas('user', function ($q) use ($institution) {
            $q->where('institution_id', $institution?->id);
        })->count();

        // Assessment stats
        $totalAssessments = 0;
        $completedAssessments = 0;
        $assessmentsByStatus = [];

        if ($activePeriod) {
            $assessmentsByStatus = Assessment::where('assessment_period_id', $activePeriod->id)
                ->selectRaw('status, count(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            $totalAssessments = array_sum($assessmentsByStatus);
            $completedAssessments = $assessmentsByStatus['completed'] ?? 0;
        }

        $assessmentProgress = $totalAssessments > 0
            ? round(($completedAssessments / $totalAssessments) * 100)
            : 0;

        // Recent activities
        $recentActivities = ActivityLog::with('user')
            ->whereHas('user', function ($q) use ($institution) {
                $q->where('institution_id', $institution?->id);
            })
            ->latest()
            ->take(5)
            ->get();

        // Top 5 teachers
        $topTeachers = [];
        if ($activePeriod) {
            $topTeachers = TeacherPeriodResult::with(['teacher.user'])
                ->whereHas('periodResult', function ($q) use ($activePeriod) {
                    $q->where('assessment_period_id', $activePeriod->id);
                })
                ->orderByDesc('final_score')
                ->take(5)
                ->get();
        }

        // AHP Model
        $ahpModel = null;
        $totalCriteria = 0;
        if ($activePeriod) {
            $ahpModel = AhpModel::where('assessment_period_id', $activePeriod->id)->first();
            $totalCriteria = $ahpModel?->criteriaSet?->nodes()->count() ?? 0;
        }

        return view('admin.dashboard', compact(
            'activePeriod',
            'totalTeachers',
            'totalAssessors',
            'totalAssessments',
            'completedAssessments',
            'assessmentProgress',
            'assessmentsByStatus',
            'recentActivities',
            'topTeachers',
            'ahpModel',
            'totalCriteria'
        ));
    }
}
