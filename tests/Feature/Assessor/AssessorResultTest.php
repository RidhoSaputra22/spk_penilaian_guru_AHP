<?php

namespace Tests\Feature\Assessor;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\PeriodResult;
use App\Models\Role;
use App\Models\TeacherPeriodResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessorResultTest extends TestCase
{
    use RefreshDatabase;

    protected User $assessor;
    protected AssessorProfile $assessorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['name' => 'assessor', 'slug' => 'assessor']);
        $this->assessor = User::factory()->create();
        $this->assessor->roles()->attach($role);
        $this->assessorProfile = AssessorProfile::factory()->create(['user_id' => $this->assessor->id]);
    }

    /** @test */
    public function assessor_can_view_results_list(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.index'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.results.index');
    }

    /** @test */
    public function assessor_can_view_result_details(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);
        $assessment = Assessment::factory()->create([
            'assessor_id' => $this->assessorProfile->id,
            'period_id' => $period->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.show', $assessment));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.results.show');
    }

    /** @test */
    public function assessor_can_only_see_own_assessment_results(): void
    {
        $otherAssessor = AssessorProfile::factory()->create();
        $otherAssessment = Assessment::factory()->create([
            'assessor_id' => $otherAssessor->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.show', $otherAssessment));

        $response->assertStatus(403);
    }

    /** @test */
    public function results_show_teacher_rankings(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);

        // Create multiple submitted assessments
        Assessment::factory()->count(5)->create([
            'assessor_id' => $this->assessorProfile->id,
            'period_id' => $period->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.results.index', ['period_id' => $period->id]));

        $response->assertStatus(200);
    }
}
