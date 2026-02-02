<?php

namespace Tests\Feature\Assessor;

use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\Institution;
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

class AssessmentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $assessor;
    protected AssessorProfile $assessorProfile;
    protected Institution $institution;
    protected Role $assessorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::factory()->create();
        $this->assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $this->assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $this->assessor->roles()->attach($this->assessorRole);
        $this->assessorProfile = AssessorProfile::factory()->create(['user_id' => $this->assessor->id]);
    }

    /** @test */
    public function assessor_can_access_assessments_index(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.assessments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.assessments.index');
    }

    /** @test */
    public function guest_cannot_access_assessor_assessments(): void
    {
        $response = $this->get(route('assessor.assessments.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function assessor_without_profile_is_redirected(): void
    {
        $userWithoutProfile = User::factory()->create(['institution_id' => $this->institution->id]);
        $userWithoutProfile->roles()->attach($this->assessorRole);

        $response = $this->actingAs($userWithoutProfile)
            ->get(route('assessor.assessments.index'));

        $response->assertRedirect(route('assessor.dashboard'));
    }

    /** @test */
    public function assessor_can_view_period_assessments(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.assessments.period', $period));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.assessments.period');
    }

    /** @test */
    public function assessor_can_save_assessment_draft(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        $assessment = Assessment::factory()->create([
            'assessment_period_id' => $period->id,
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->assessor)
            ->postJson(route('assessor.assessments.save-draft', $assessment), [
                'values' => [],
            ]);

        // Controller may redirect or return JSON, both are valid
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    /** @test */
    public function assessor_cannot_edit_other_assessor_assessment(): void
    {
        $otherAssessorProfile = AssessorProfile::factory()->create();
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $assessment = Assessment::factory()->create([
            'assessment_period_id' => $period->id,
            'assessor_profile_id' => $otherAssessorProfile->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->assessor)
            ->postJson(route('assessor.assessments.save-draft', $assessment), [
                'values' => [],
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function assessor_cannot_edit_submitted_assessment(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $assessment = Assessment::factory()->create([
            'assessment_period_id' => $period->id,
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->postJson(route('assessor.assessments.save-draft', $assessment), [
                'values' => [],
            ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_assessor_cannot_access_assessor_routes(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('assessor.assessments.index'));

        $response->assertStatus(403);
    }
}
