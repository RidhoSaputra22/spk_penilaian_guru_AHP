<?php

namespace Tests\Feature\Teacher;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\Role;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected TeacherProfile $teacherProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $this->teacher = User::factory()->create();
        $this->teacher->roles()->attach($role);
        $this->teacherProfile = TeacherProfile::factory()->create(['user_id' => $this->teacher->id]);
    }

    /** @test */
    public function teacher_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.dashboard');
    }

    /** @test */
    public function dashboard_shows_assessment_statistics(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        Assessment::factory()->count(2)->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
            'status' => 'pending',
        ]);
        Assessment::factory()->count(3)->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    /** @test */
    public function dashboard_shows_active_periods(): void
    {
        AssessmentPeriod::factory()->count(2)->create(['status' => 'active']);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('activePeriods');
    }

    /** @test */
    public function dashboard_shows_recent_results(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);
        TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentResults');
    }

    /** @test */
    public function guest_cannot_access_teacher_dashboard(): void
    {
        $response = $this->get(route('teacher.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function non_teacher_cannot_access_dashboard(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create();
        $assessor->roles()->attach($assessorRole);

        $response = $this->actingAs($assessor)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(403);
    }
}
