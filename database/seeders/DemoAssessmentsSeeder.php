<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentStatusLog;
use App\Models\EvidenceUpload;
use App\Models\Institution;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoAssessmentsSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        if (!$institution) return;

        $period = AssessmentPeriod::where('institution_id', $institution->id)->where('meta->demo', true)->first();
        if (!$period) return;

        $assignment = KpiFormAssignment::where('assessment_period_id', $period->id)->first();
        if (!$assignment) return;

        // Avoid duplicating (if at least 1 assessment exists, skip)
        if (Assessment::where('assessment_period_id', $period->id)->exists()) {
            return;
        }

        $assessors = $assignment->assessors()->get();
        $teachers  = $assignment->teachers()->get();
        $items     = KpiFormItem::whereIn('section_id', $assignment->formVersion->sections()->pluck('id'))->orderBy('sort_order')->get();

        if ($assessors->isEmpty() || $teachers->isEmpty() || $items->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($period, $assignment, $assessors, $teachers, $items) {
            foreach ($teachers as $teacher) {
                $assessor = $assessors->random();

                $assessment = Assessment::create([
                    'assessment_period_id' => $period->id,
                    'assignment_id' => $assignment->id,
                    'teacher_profile_id' => $teacher->id,
                    'assessor_profile_id' => $assessor->id,
                    'status' => 'finalized',
                    'started_at' => now()->subDays(2),
                    'submitted_at' => now()->subDay(),
                    'finalized_at' => now(),
                    'meta' => ['demo' => true],
                ]);

                // status log: draft -> finalized
                AssessmentStatusLog::create([
                    'assessment_id' => $assessment->id,
                    'from_status' => 'draft',
                    'to_status' => 'finalized',
                    'changed_by' => $assessor->user_id, // user id
                    'reason' => null,
                    'created_at' => now(),
                ]);

                foreach ($items as $item) {
                    $v = random_int(1, 4);

                    $value = AssessmentItemValue::create([
                        'assessment_id' => $assessment->id,
                        'form_item_id' => $item->id,
                        'value_number' => $v,
                        'value_string' => null,
                        'value_bool' => null,
                        'notes' => null,
                        'score_value' => $v,
                        'meta' => ['demo' => true],
                    ]);

                    // evidence for ~15% of items
                    if (random_int(1, 100) <= 15) {
                        $filename = Str::random(10) . '.pdf';

                        EvidenceUpload::create([
                            'assessment_item_value_id' => $value->id,
                            'uploaded_by' => $assessor->user_id,
                            'disk' => 'public',
                            'path' => 'evidence/' . $filename,
                            'original_name' => $filename,
                            'mime_type' => 'application/pdf',
                            'size' => random_int(10_000, 2_000_000),
                            'url' => null,
                            'meta' => ['demo' => true],
                        ]);
                    }
                }
            }
        });
    }
}
