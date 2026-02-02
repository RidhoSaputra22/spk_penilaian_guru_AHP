<?php

namespace Tests\Feature\Assessor;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultControllerTest extends TestCase
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
    public function assessor_can_access_results_index(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.index'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.results.index');
    }

    /** @test */
    public function guest_cannot_access_assessor_results(): void
    {
        $response = $this->get(route('assessor.results.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function results_index_displays_completed_assessments(): void
    {
        Assessment::factory()->count(3)->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.index'));

        $response->assertStatus(200);
        $response->assertViewHas('assessments');
    }

    /** @test */
    public function results_can_be_filtered_by_period(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        Assessment::factory()->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'assessment_period_id' => $period->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.index', ['period_id' => $period->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function assessor_can_view_single_result(): void
    {
        $assessment = Assessment::factory()->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.show', $assessment));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.results.show');
    }

    /** @test */
    public function assessor_cannot_view_other_assessor_results(): void
    {
        $otherAssessorProfile = AssessorProfile::factory()->create();
        $assessment = Assessment::factory()->create([
            'assessor_profile_id' => $otherAssessorProfile->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.show', $assessment));

        $response->assertStatus(403);
    }

    /** @test */
    public function assessor_without_profile_is_redirected(): void
    {
        $userWithoutProfile = User::factory()->create(['institution_id' => $this->institution->id]);
        $userWithoutProfile->roles()->attach($this->assessorRole);

        $response = $this->actingAs($userWithoutProfile)
            ->get(route('assessor.results.index'));

        $response->assertRedirect(route('assessor.dashboard'));
    }

    /** @test */
    public function non_assessor_cannot_access_results(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('assessor.results.index'));

        $response->assertStatus(403);
    }
}
