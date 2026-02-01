<?php

namespace Tests\Feature\Assessor;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessorDashboardTest extends TestCase
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
    public function assessor_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.dashboard');
    }

    /** @test */
    public function dashboard_shows_pending_assessments(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        Assessment::factory()->count(3)->create([
            'assessor_id' => $this->assessorProfile->id,
            'period_id' => $period->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('pendingCount', 3);
    }

    /** @test */
    public function dashboard_shows_completed_assessments(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        Assessment::factory()->count(5)->create([
            'assessor_id' => $this->assessorProfile->id,
            'period_id' => $period->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('completedCount', 5);
    }

    /** @test */
    public function guest_cannot_access_assessor_dashboard(): void
    {
        $response = $this->get(route('assessor.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function non_assessor_cannot_access_dashboard(): void
    {
        $teacherRole = Role::factory()->create(['name' => 'teacher', 'slug' => 'teacher']);
        $teacher = User::factory()->create();
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(403);
    }
}
