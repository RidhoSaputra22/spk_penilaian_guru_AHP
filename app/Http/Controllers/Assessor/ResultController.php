<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    /**
     * Display list of completed assessments
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        if (! $assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        $query = Assessment::where('assessor_profile_id', $assessorProfile->id)
            ->whereIn('status', ['submitted', 'finalized'])
            ->with(['teacher.user', 'period', 'assignment.formVersion.template']);

        // Filter by period
        if ($request->filled('period_id')) {
            $query->where('assessment_period_id', $request->period_id);
        }

        $assessments = $query->orderBy('submitted_at', 'desc')->paginate(15);

        // Get periods for filter dropdown
        $periods = AssessmentPeriod::whereHas('assessments', function ($q) use ($assessorProfile) {
            $q->where('assessor_profile_id', $assessorProfile->id);
        })->orderBy('created_at', 'desc')->get();

        return view('assessor.results.index', compact('assessments', 'periods'));
    }

    /**
     * Show detail of a specific assessment result
     */
    public function show(Assessment $assessment)
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        // Verify ownership
        if ($assessment->assessor_profile_id !== $assessorProfile->id) {
            abort(403, 'Anda tidak memiliki akses ke hasil penilaian ini.');
        }

        $assessment->load([
            'teacher.user',
            'period',
            'assignment.formVersion.sections.items.scale.options',
            'assignment.formVersion.sections.items.options',
            'itemValues',
            'statusLogs.changer',
        ]);

        $valuesMap = $assessment->itemValues->keyBy('form_item_id');

        return view('assessor.results.show', compact('assessment', 'valuesMap'));
    }
}
