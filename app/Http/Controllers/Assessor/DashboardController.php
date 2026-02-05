<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        if (! $assessorProfile) {
            return redirect()->route('login')->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Get active periods where assessor has assessments
        $activePeriods = AssessmentPeriod::where('status', 'open')
            ->where('institution_id', $user->institution_id)
            ->whereHas('assessments', function ($query) use ($assessorProfile) {
                $query->where('assessor_profile_id', $assessorProfile->id);
            })
            ->orderBy('scoring_close_at', 'desc')
            ->get();

        // Get pending assessments (draft or not started)
        $pendingAssessments = Assessment::where('assessor_profile_id', $assessorProfile->id)
            ->whereIn('status', ['draft', 'pending'])
            ->with(['teacher.user', 'period', 'assignment.formVersion.template'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get recent submitted assessments
        $recentSubmitted = Assessment::where('assessor_profile_id', $assessorProfile->id)
            ->where('status', 'submitted')
            ->with(['teacher.user', 'period'])
            ->orderBy('submitted_at', 'desc')
            ->limit(5)
            ->get();

        // Statistics
        $pendingCount = Assessment::where('assessor_profile_id', $assessorProfile->id)
            ->whereIn('status', ['draft', 'pending'])->count();
        $completedCount = Assessment::where('assessor_profile_id', $assessorProfile->id)
            ->where('status', 'submitted')->count();

        $stats = [
            'total_assigned' => Assessment::where('assessor_profile_id', $assessorProfile->id)->count(),
            'pending' => $pendingCount,
            'submitted' => $completedCount,
            'finalized' => Assessment::where('assessor_profile_id', $assessorProfile->id)
                ->where('status', 'finalized')->count(),
        ];

        return view('assessor.dashboard', compact(
            'assessorProfile',
            'activePeriods',
            'pendingAssessments',
            'recentSubmitted',
            'stats',
            'pendingCount',
            'completedCount'
        ));
    }
}