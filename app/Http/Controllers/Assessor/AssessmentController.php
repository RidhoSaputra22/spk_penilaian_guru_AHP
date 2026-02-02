<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentStatusLog;
use App\Models\KpiFormAssignment;
use App\Models\TeacherProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    /**
     * Display list of periods with assigned teachers
     */
    public function index()
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        if (!$assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Get periods where assessor has assignments
        $periods = AssessmentPeriod::whereIn('status', ['open', 'closed'])
            ->whereHas('assignments', function ($query) use ($assessorProfile) {
                $query->whereHas('assessors', function ($q) use ($assessorProfile) {
                    $q->where('assessor_profile_id', $assessorProfile->id);
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('assessor.assessments.index', compact('periods', 'assessorProfile'));
    }

    /**
     * Display teachers for a specific period
     */
    public function period(AssessmentPeriod $period)
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        if (!$assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Get assignments for this period where assessor is assigned
        $assignments = KpiFormAssignment::where('assessment_period_id', $period->id)
            ->whereHas('assessors', function ($query) use ($assessorProfile) {
                $query->where('assessor_profile_id', $assessorProfile->id);
            })
            ->with(['formVersion.template', 'teachers.user', 'teacherGroups.teachers.user'])
            ->get();

        // Collect all teachers from assignments
        $teachers = collect();
        foreach ($assignments as $assignment) {
            // Direct teacher assignments
            foreach ($assignment->teachers as $teacher) {
                if (!$teachers->contains('id', $teacher->id)) {
                    $teacher->assignment = $assignment;
                    $teachers->push($teacher);
                }
            }
            // Teachers from groups
            foreach ($assignment->teacherGroups as $group) {
                foreach ($group->teachers as $teacher) {
                    if (!$teachers->contains('id', $teacher->id)) {
                        $teacher->assignment = $assignment;
                        $teachers->push($teacher);
                    }
                }
            }
        }

        // Get existing assessments for these teachers by this assessor
        $existingAssessments = Assessment::where('assessor_profile_id', $assessorProfile->id)
            ->where('assessment_period_id', $period->id)
            ->get()
            ->keyBy('teacher_profile_id');

        return view('assessor.assessments.period', compact(
            'period',
            'teachers',
            'existingAssessments',
            'assessorProfile'
        ));
    }

    /**
     * Show scoring form for a specific teacher
     */
    public function score(AssessmentPeriod $period, TeacherProfile $teacher)
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        if (!$assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Check if there's an existing assessment for this teacher by another assessor
        $existingAssessmentByOther = Assessment::where('assessment_period_id', $period->id)
            ->where('teacher_profile_id', $teacher->id)
            ->where('assessor_profile_id', '!=', $assessorProfile->id)
            ->first();

        if ($existingAssessmentByOther) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Find the assignment for this teacher in this period
        $assignment = KpiFormAssignment::where('assessment_period_id', $period->id)
            ->whereHas('assessors', function ($query) use ($assessorProfile) {
                $query->where('assessor_profile_id', $assessorProfile->id);
            })
            ->where(function ($query) use ($teacher) {
                $query->whereHas('teachers', function ($q) use ($teacher) {
                    $q->where('teacher_profile_id', $teacher->id);
                })->orWhereHas('teacherGroups.teachers', function ($q) use ($teacher) {
                    $q->where('teacher_profiles.id', $teacher->id);
                });
            })
            ->with(['formVersion.sections.items.scale.options', 'formVersion.sections.items.options'])
            ->first();

        if (!$assignment) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Check if period is still open for scoring
        if ($period->status !== 'open') {
            return redirect()->route('assessor.assessments.period', $period)
                ->with('error', 'Periode penilaian sudah ditutup.');
        }

        // Get or create assessment
        $assessment = Assessment::firstOrCreate([
            'assessment_period_id' => $period->id,
            'assignment_id' => $assignment->id,
            'teacher_profile_id' => $teacher->id,
            'assessor_profile_id' => $assessorProfile->id,
        ], [
            'status' => 'draft',
            'started_at' => now(),
        ]);

        // If assessment is already submitted/finalized, redirect to view
        if (in_array($assessment->status, ['submitted', 'finalized'])) {
            return redirect()->route('assessor.results.show', $assessment)
                ->with('info', 'Penilaian ini sudah disubmit dan tidak dapat diubah.');
        }

        // Load existing values
        $existingValues = $assessment->itemValues()->get()->keyBy('form_item_id');

        $formVersion = $assignment->formVersion;

        return view('assessor.assessments.score', compact(
            'period',
            'teacher',
            'assignment',
            'assessment',
            'formVersion',
            'existingValues'
        ));
    }

    /**
     * Save assessment as draft
     */
    public function saveDraft(Request $request, Assessment $assessment)
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        // Verify ownership
        if ($assessment->assessor_profile_id !== $assessorProfile->id) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Verify status allows editing
        if (in_array($assessment->status, ['submitted', 'finalized'])) {
            if ($request->ajax()) {
                return response()->json(['error' => 'Penilaian sudah disubmit dan tidak dapat diubah.'], 403);
            }
            abort(403, 'Penilaian sudah disubmit dan tidak dapat diubah.');
        }

        // Validate scores if provided
        $scores = $request->input('scores', []);
        $values = $request->input('values', []);

        // Merge scores into values for unified processing
        foreach ($scores as $itemId => $scoreData) {
            if (is_array($scoreData) && isset($scoreData['score'])) {
                // Validate score range
                $score = $scoreData['score'];
                if ($score !== null && $score !== '' && (floatval($score) < 0 || floatval($score) > 100)) {
                    return redirect()->back()
                        ->withErrors(['scores' => 'Nilai skor harus antara 0 dan 100.'])
                        ->withInput();
                }
                $values[$itemId] = $score;
            }
        }

        // Replace scores with validated values
        $request->merge(['values' => $values]);

        $this->saveAssessmentValues($request, $assessment);

        $assessment->update(['status' => 'draft']);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Draft tersimpan.']);
        }

        return redirect()->back()->with('success', 'Draft tersimpan.');
    }

    /**
     * Submit assessment (finalize)
     */
    public function submit(Request $request, Assessment $assessment)
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        // Verify ownership
        if ($assessment->assessor_profile_id !== $assessorProfile->id) {
            return redirect()->route('assessor.assessments.index')
                ->with('error', 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Verify status allows submission
        if (in_array($assessment->status, ['submitted', 'finalized'])) {
            return redirect()->route('assessor.results.show', $assessment)
                ->with('error', 'Penilaian sudah disubmit.');
        }

        // Save values first if provided
        if ($request->has('values') || $request->has('scores')) {
            $scores = $request->input('scores', []);
            $values = $request->input('values', []);

            foreach ($scores as $itemId => $scoreData) {
                if (is_array($scoreData) && isset($scoreData['score'])) {
                    $values[$itemId] = $scoreData['score'];
                }
            }
            $request->merge(['values' => $values]);
            $this->saveAssessmentValues($request, $assessment);
        }

        // Validate all required fields are filled
        $formVersion = $assessment->assignment->formVersion;
        $requiredItems = $formVersion->sections->flatMap->items->where('is_required', true);
        $filledItems = $assessment->itemValues()->whereNotNull('score_value')->pluck('form_item_id')->toArray();

        $missingItems = $requiredItems->filter(function ($item) use ($filledItems) {
            return !in_array($item->id, $filledItems);
        });

        if ($missingItems->isNotEmpty()) {
            return redirect()->back()
                ->withErrors(['scores' => 'Masih ada ' . $missingItems->count() . ' indikator wajib yang belum diisi.']);
        }

        DB::transaction(function () use ($assessment) {
            $assessment->update([
                'status' => 'submitted',
                'submitted_at' => now(),
            ]);

            // Log status change
            AssessmentStatusLog::create([
                'assessment_id' => $assessment->id,
                'from_status' => 'draft',
                'to_status' => 'submitted',
                'changed_by' => auth()->id(),
                'reason' => 'Submitted by assessor',
            ]);
        });

        return redirect()->route('assessor.results.show', $assessment)
            ->with('success', 'Penilaian berhasil disubmit.');
    }

    /**
     * Helper to save assessment values
     */
    private function saveAssessmentValues(Request $request, Assessment $assessment)
    {
        $values = $request->input('values', []);
        $notes = $request->input('notes', []);

        foreach ($values as $itemId => $value) {
            $data = [
                'assessment_id' => $assessment->id,
                'form_item_id' => $itemId,
                'notes' => $notes[$itemId] ?? null,
            ];

            // Determine the value type based on the input
            if (is_numeric($value)) {
                $data['value_number'] = $value;
                $data['score_value'] = $value;
            } elseif (is_bool($value) || in_array($value, ['0', '1', 'true', 'false'])) {
                $data['value_bool'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } else {
                $data['value_string'] = $value;
            }

            AssessmentItemValue::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'form_item_id' => $itemId,
                ],
                $data
            );
        }
    }
}
