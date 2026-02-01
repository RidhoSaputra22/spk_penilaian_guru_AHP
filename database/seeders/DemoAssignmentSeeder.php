<?php

namespace Database\Seeders;

use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\TeacherGroup;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        $admin = User::where('email', 'admin@demo.local')->first();

        if (!$institution || !$admin) {
            return;
        }

        $template = KpiFormTemplate::where('institution_id', $institution->id)
            ->where('name', 'Form Penilaian KPI (Demo)')
            ->first();

        $version = $template
            ? KpiFormVersion::where('template_id', $template->id)->where('version', 1)->first()
            : null;

        if (!$version) {
            return; // run DemoFormSeeder first
        }

        $year1 = (int) now()->format('Y');
        $year2 = $year1 + 1;
        $periodName = "Semester Ganjil {$year1}/{$year2} (Demo)";

        DB::transaction(function () use ($institution, $admin, $version, $periodName, $year1, $year2) {
            $period = AssessmentPeriod::firstOrCreate(
                ['institution_id' => $institution->id, 'name' => $periodName],
                [
                    'academic_year' => "{$year1}/{$year2}",
                    'semester' => 'ganjil',
                    'scoring_open_at' => now()->subDays(3),
                    'scoring_close_at' => now()->addDays(30),
                    'status' => 'open',
                    'meta' => ['demo' => true],
                ]
            );

            // Avoid duplicates
            if (KpiFormAssignment::where('assessment_period_id', $period->id)->where('form_version_id', $version->id)->exists()) {
                return;
            }

            $assignment = KpiFormAssignment::create([
                'assessment_period_id' => $period->id,
                'form_version_id' => $version->id,
                'status' => 'active',
                'assigned_at' => now(),
                'locked_at' => null,
                'assigned_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            $assessors = AssessorProfile::whereHas('user', fn ($q) => $q->where('institution_id', $institution->id))->get();
            $teachers  = TeacherProfile::whereHas('user', fn ($q) => $q->where('institution_id', $institution->id))->get();
            $groups    = TeacherGroup::where('institution_id', $institution->id)->get();

            $assignment->assessors()->sync($assessors->pluck('id')->all());
            $assignment->teachers()->sync($teachers->pluck('id')->all());
            $assignment->teacherGroups()->sync($groups->pluck('id')->all());
        });
    }
}
