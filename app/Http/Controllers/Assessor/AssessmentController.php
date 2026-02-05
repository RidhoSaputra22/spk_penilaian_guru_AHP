<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentStatusLog;
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

        if (! $assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Get periods where assessor has assessments assigned
        $periods = AssessmentPeriod::whereIn('status', ['open', 'closed'])
            ->whereHas('assessments', function ($query) use ($assessorProfile) {
                $query->where('assessor_profile_id', $assessorProfile->id);
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

        if (! $assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Get assessments for this period where assessor is assigned
        $assessments = Assessment::where('assessment_period_id', $period->id)
            ->where('assessor_profile_id', $assessorProfile->id)
            ->with(['teacher.user', 'assignment.formVersion.template'])
            ->get();

        // Collect teachers with their assessment info
        $teachers = collect();
        foreach ($assessments as $assessment) {
            $teacher = $assessment->teacher;
            if ($teacher && ! $teachers->contains('id', $teacher->id)) {
                $teacher->assessment = $assessment;
                $teacher->assignment = $assessment->assignment;
                $teachers->push($teacher);
            }
        }

        // Get existing assessments for these teachers by this assessor
        $existingAssessments = $assessments->keyBy('teacher_profile_id');

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

        if (! $assessorProfile) {
            return redirect()->route('assessor.dashboard')
                ->with('error', 'Profil penilai tidak ditemukan.');
        }

        // Check if assessor is assigned to this teacher for this period
        $existingAssessment = Assessment::where('assessment_period_id', $period->id)
            ->where('teacher_profile_id', $teacher->id)
            ->where('assessor_profile_id', $assessorProfile->id)
            ->first();

        if (! $existingAssessment) {
            abort(403, 'Anda tidak memiliki akses ke penilaian ini.');
        }

        // Get or create the assignment with form version
        $assignment = $existingAssessment->assignment;

        // dd($existingAssessment, $assignment);

        if (! $assignment) {
            // Get the first available form template
            $formTemplate = \App\Models\KpiFormTemplate::first();

            if (! $formTemplate) {
                return redirect()->route('assessor.assessments.period', $period)
                    ->with('error', 'Form KPI belum tersedia. Hubungi administrator.');
            }

            // Get the latest published version
            $formVersion = $formTemplate->versions()
                ->where('status', 'published')
                ->orderByDesc('version')
                ->first();

            if (! $formVersion) {
                // If no published version, get the latest version
                $formVersion = $formTemplate->versions()
                    ->orderByDesc('version')
                    ->first();
            }

            if (! $formVersion) {
                return redirect()->route('assessor.assessments.period', $period)
                    ->with('error', 'Versi form KPI belum tersedia. Hubungi administrator.');
            }

            // Create assignment
            $assignment = \App\Models\KpiFormAssignment::firstOrCreate([
                'assessment_period_id' => $period->id,
                'form_version_id' => $formVersion->id,
            ]);

            // Update assessment with assignment_id
            $existingAssessment->update(['assignment_id' => $assignment->id]);
            $existingAssessment->refresh();
        }

        $assignment->load(['formVersion.sections.items.scale.options', 'formVersion.sections.items.options']);

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
            return ! in_array($item->id, $filledItems);
        });

        if ($missingItems->isNotEmpty()) {
            return redirect()->back()
                ->withErrors(['scores' => 'Masih ada '.$missingItems->count().' indikator wajib yang belum diisi.']);
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

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disubmit.',
                'redirect' => route('assessor.results.show', $assessment),
            ]);
        }

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
            // Skip empty values
            if ($value === null || $value === '') {
                continue;
            }

            $data = [
                'assessment_id' => $assessment->id,
                'form_item_id' => $itemId,
                'notes' => $notes[$itemId] ?? null,
            ];

            // Determine the value type based on the input
            if (is_numeric($value)) {
                $data['value_number'] = floatval($value);
                $data['score_value'] = floatval($value);
            } elseif (is_bool($value) || in_array($value, ['0', '1', 'true', 'false', 'on'])) {
                $data['value_bool'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                $data['score_value'] = $data['value_bool'] ? 1 : 0;
            } else {
                $data['value_string'] = $value;
                // For string values, try to extract numeric score if possible
                if (is_numeric($value)) {
                    $data['score_value'] = floatval($value);
                }
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
