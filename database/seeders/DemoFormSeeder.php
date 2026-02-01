<?php

namespace Database\Seeders;

use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Institution;
use App\Models\KpiFormItem;
use App\Models\KpiFormSection;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\ScoringScale;
use App\Models\ScoringScaleOption;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoFormSeeder extends Seeder
{
    public function run(): void
    {
        $institution = Institution::where('code', 'DEMO')->first();
        $admin = User::where('email', 'admin@demo.local')->first();

        if (!$institution || !$admin) {
            return;
        }

        $set = CriteriaSet::where('institution_id', $institution->id)
            ->where('name', 'Kriteria Kinerja Guru')
            ->first();

        if (!$set) {
            return; // run DemoCriteriaSeeder first
        }

        // Avoid duplicating
        if (KpiFormTemplate::where('institution_id', $institution->id)->where('name', 'Form Penilaian KPI (Demo)')->exists()) {
            return;
        }

        DB::transaction(function () use ($institution, $admin, $set) {
            // Ensure scale 1-4 exists
            $scale = ScoringScale::firstOrCreate(
                ['institution_id' => $institution->id, 'name' => 'Scale 1-4'],
                ['scale_type' => 'numeric', 'min_value' => 1, 'max_value' => 4, 'step' => 1]
            );

            if ($scale->options()->count() === 0) {
                foreach ([1,2,3,4] as $v) {
                    ScoringScaleOption::create([
                        'scoring_scale_id' => $scale->id,
                        'value' => (string) $v,
                        'label' => (string) $v,
                        'score_value' => $v,
                        'sort_order' => $v,
                    ]);
                }
            }

            $template = KpiFormTemplate::create([
                'institution_id' => $institution->id,
                'name' => 'Form Penilaian KPI (Demo)',
                'description' => 'Form demo untuk input KPI oleh assessor.',
                'default_scoring_scale_id' => $scale->id,
                'created_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            $version = KpiFormVersion::create([
                'template_id' => $template->id,
                'version' => 1,
                'status' => 'published',
                'published_at' => now(),
                'locked_at' => null,
                'created_by' => $admin->id,
                'meta' => ['demo' => true],
            ]);

            $goal = CriteriaNode::where('criteria_set_id', $set->id)->where('node_type', 'goal')->first();
            $criteriaNodes = CriteriaNode::where('criteria_set_id', $set->id)
                ->where('parent_id', $goal?->id)
                ->where('node_type', 'criteria')
                ->orderBy('sort_order')
                ->get();

            $sectionOrder = 1;
            foreach ($criteriaNodes as $criteria) {
                $section = KpiFormSection::create([
                    'form_version_id' => $version->id,
                    'criteria_node_id' => $criteria->id,
                    'title' => "Aspek: {$criteria->name}",
                    'description' => null,
                    'sort_order' => $sectionOrder++,
                    'meta' => ['demo' => true],
                ]);

                // Items: all indicator nodes under this criterion
                $subcriteria = CriteriaNode::where('parent_id', $criteria->id)->where('node_type', 'subcriteria')->orderBy('sort_order')->get();
                $itemOrder = 1;

                foreach ($subcriteria as $sub) {
                    $indicators = CriteriaNode::where('parent_id', $sub->id)->where('node_type', 'indicator')->orderBy('sort_order')->get();
                    foreach ($indicators as $ind) {
                        KpiFormItem::create([
                            'section_id' => $section->id,
                            'criteria_node_id' => $ind->id,
                            'label' => "{$sub->name}: {$ind->name}",
                            'help_text' => 'Nilai 1 (kurang) s/d 4 (sangat baik).',
                            'field_type' => 'numeric',
                            'is_required' => true,
                            'min_value' => 1,
                            'max_value' => 4,
                            'scoring_scale_id' => $scale->id,
                            'default_value' => null,
                            'sort_order' => $itemOrder++,
                            'meta' => ['demo' => true],
                        ]);
                    }
                }
            }
        });
    }
}
