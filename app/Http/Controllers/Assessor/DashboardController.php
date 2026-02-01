<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\KpiFormAssignment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        if (!$assessorProfile) {
            return redirect()->route('login')->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Get active periods where assessor is assigned
        $activePeriods = AssessmentPeriod::where('status', 'active')
            ->whereHas('assignments', function ($query) use ($assessorProfile) {
                $query->whereHas('assessors', function ($q) use ($assessorProfile) {
                    $q->where('assessor_profile_id', $assessorProfile->id);
                });
            })
            ->with(['assignments' => function ($query) use ($assessorProfile) {
                $query->whereHas('assessors', function ($q) use ($assessorProfile) {
                    $q->where('assessor_profile_id', $assessorProfile->id);
                });
            }])
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
        $stats = [
            'total_assigned' => Assessment::where('assessor_profile_id', $assessorProfile->id)->count(),
            'pending' => Assessment::where('assessor_profile_id', $assessorProfile->id)
                ->whereIn('status', ['draft', 'pending'])->count(),
            'submitted' => Assessment::where('assessor_profile_id', $assessorProfile->id)
                ->where('status', 'submitted')->count(),
            'finalized' => Assessment::where('assessor_profile_id', $assessorProfile->id)
                ->where('status', 'finalized')->count(),
        ];

        return view('assessor.dashboard', compact(
            'assessorProfile',
            'activePeriods',
            'pendingAssessments',
            'recentSubmitted',
            'stats'
        ));
    }
}
