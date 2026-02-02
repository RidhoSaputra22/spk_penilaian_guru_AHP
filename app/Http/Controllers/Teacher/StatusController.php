<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        if (!$teacherProfile) {
            return view('teacher.status.index', [
                'assessments' => collect(),
                'periods' => collect(),
            ]);
        }

        $query = Assessment::with(['period', 'assessor.user', 'assignment.formVersion.template'])
            ->where('teacher_profile_id', $teacherProfile->id)
            ->orderBy('created_at', 'desc');

        // Filter by period
        if ($request->filled('period_id')) {
            $query->where('period_id', $request->period_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $assessments = $query->paginate(10);

        // Get all periods for filter
        $periods = AssessmentPeriod::whereHas('assessments', function ($q) use ($teacherProfile) {
            $q->where('teacher_profile_id', $teacherProfile->id);
        })->orderBy('created_at', 'desc')->get();

        return view('teacher.status.index', compact('assessments', 'periods'));
    }

    public function show(Assessment $assessment)
    {
        $user = auth()->user();
        $teacherProfile = $user->teacherProfile;

        // Ensure teacher can only see their own assessments
        if ($assessment->teacher_profile_id !== $teacherProfile?->id) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        $assessment->load([
            'period',
            'assessor.user',
            'assignment.formVersion.template',
            'assignment.formVersion.sections.items',
        ]);

        return view('teacher.status.show', compact('assessment'));
    }
}
