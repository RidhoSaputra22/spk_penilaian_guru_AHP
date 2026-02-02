<?php

namespace Tests\Feature\Assessor;

use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormItem;
use App\Models\KpiFormSection;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentScoringTest extends TestCase
{
    use RefreshDatabase;

    protected User $assessor;
    protected AssessorProfile $assessorProfile;
    protected Assessment $assessment;
    protected KpiFormVersion $formVersion;

    protected function setUp(): void
    {
        parent::setUp();

        // Create assessor
        $role = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $this->assessor = User::factory()->create();
        $this->assessor->roles()->attach($role);
        $this->assessorProfile = AssessorProfile::factory()->create(['user_id' => $this->assessor->id]);

        // Create teacher
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacherUser = User::factory()->create();
        $teacherUser->roles()->attach($teacherRole);
        $teacher = TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        // Create form structure
        $template = KpiFormTemplate::factory()->create();
        $this->formVersion = KpiFormVersion::factory()->create(['template_id' => $template->id]);

        // Create period and assignment
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        KpiFormAssignment::factory()->create([
            'assessment_period_id' => $period->id,
            'form_version_id' => $this->formVersion->id,
        ]);

        // Create assessment
        $this->assessment = Assessment::factory()->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'teacher_profile_id' => $teacher->id,
            'assessment_period_id' => $period->id,
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function assessor_can_view_assessments_list(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.assessments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.assessments.index');
    }

    /** @test */
    public function assessor_can_view_scoring_form(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.assessments.score', [
                'period' => $this->assessment->period,
                'teacher' => $this->assessment->teacher
            ]));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.assessments.score');
    }

    /** @test */
    public function assessor_can_save_draft_scores(): void
    {
        $section = KpiFormSection::factory()->create(['form_version_id' => $this->formVersion->id]);
        $item = KpiFormItem::factory()->create(['section_id' => $section->id]);

        $response = $this->actingAs($this->assessor)
            ->post(route('assessor.assessments.save-draft', $this->assessment), [
                'scores' => [
                    $item->id => [
                        'score' => 85,
                        'note' => 'Good performance',
                    ],
                ],
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('assessment_item_values', [
            'assessment_id' => $this->assessment->id,
            'form_item_id' => $item->id,
            'score_value' => 85,
        ]);

        $this->assessment->refresh();
        $this->assertEquals('draft', $this->assessment->status);
    }

    /** @test */
    public function assessor_can_submit_assessment(): void
    {
        $section = KpiFormSection::factory()->create(['form_version_id' => $this->formVersion->id]);
        $item = KpiFormItem::factory()->create(['section_id' => $section->id]);

        // First save scores
        AssessmentItemValue::factory()->create([
            'assessment_id' => $this->assessment->id,
            'form_item_id' => $item->id,
            'score_value' => 90,
        ]);

        $response = $this->actingAs($this->assessor)
            ->post(route('assessor.assessments.submit', $this->assessment));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assessment->refresh();
        $this->assertEquals('submitted', $this->assessment->status);
    }

    /** @test */
    public function assessor_cannot_submit_assessment_without_scores(): void
    {
        $section = KpiFormSection::factory()->create(['form_version_id' => $this->formVersion->id]);
        KpiFormItem::factory()->create(['section_id' => $section->id]);

        $response = $this->actingAs($this->assessor)
            ->post(route('assessor.assessments.submit', $this->assessment));

        $response->assertSessionHasErrors();

        $this->assessment->refresh();
        $this->assertEquals('pending', $this->assessment->status);
    }

    /** @test */
    public function assessor_cannot_score_other_assessors_assessment(): void
    {
        // Create another assessor's assessment
        $otherAssessor = AssessorProfile::factory()->create();
        $otherAssessment = Assessment::factory()->create([
            'assessor_profile_id' => $otherAssessor->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.assessments.score', [
                'period' => $otherAssessment->period,
                'teacher' => $otherAssessment->teacher
            ]));

        $response->assertStatus(403);
    }

    /** @test */
    public function assessor_cannot_edit_submitted_assessment(): void
    {
        $this->assessment->update(['status' => 'submitted']);

        $response = $this->actingAs($this->assessor)
            ->post(route('assessor.assessments.save-draft', $this->assessment), [
                'scores' => [],
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function assessment_scores_are_validated(): void
    {
        $section = KpiFormSection::factory()->create(['form_version_id' => $this->formVersion->id]);
        $item = KpiFormItem::factory()->create(['section_id' => $section->id]);

        $response = $this->actingAs($this->assessor)
            ->post(route('assessor.assessments.save-draft', $this->assessment), [
                'scores' => [
                    $item->id => [
                        'score' => 150, // Invalid score > 100
                        'note' => 'Test',
                    ],
                ],
            ]);

        $response->assertSessionHasErrors();
    }
}
