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

class DashboardControllerTest extends TestCase
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
    public function assessor_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.dashboard');
    }

    /** @test */
    public function guest_cannot_access_assessor_dashboard(): void
    {
        $response = $this->get(route('assessor.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_displays_assessor_statistics(): void
    {
        Assessment::factory()->count(3)->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
        $response->assertViewHas('pendingCount');
        $response->assertViewHas('completedCount');
    }

    /** @test */
    public function dashboard_displays_active_periods(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('activePeriods');
    }

    /** @test */
    public function dashboard_displays_pending_assessments(): void
    {
        Assessment::factory()->count(3)->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('pendingAssessments');
    }

    /** @test */
    public function dashboard_displays_recent_submitted(): void
    {
        Assessment::factory()->count(3)->create([
            'assessor_profile_id' => $this->assessorProfile->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentSubmitted');
    }

    /** @test */
    public function assessor_without_profile_is_redirected(): void
    {
        $userWithoutProfile = User::factory()->create(['institution_id' => $this->institution->id]);
        $userWithoutProfile->roles()->attach($this->assessorRole);

        $response = $this->actingAs($userWithoutProfile)
            ->get(route('assessor.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function non_assessor_cannot_access_dashboard(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('assessor.dashboard'));

        $response->assertStatus(403);
    }
}
