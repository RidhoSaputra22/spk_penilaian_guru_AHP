<?php

namespace Tests\Feature\Teacher;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\Institution;
use App\Models\Role;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected TeacherProfile $teacherProfile;
    protected Institution $institution;
    protected Role $teacherRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::factory()->create();
        $this->teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $this->teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $this->teacher->roles()->attach($this->teacherRole);
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
    public function guest_cannot_access_teacher_dashboard(): void
    {
        $response = $this->get(route('teacher.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_displays_teacher_statistics(): void
    {
        Assessment::factory()->count(3)->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('stats');
    }

    /** @test */
    public function dashboard_displays_active_periods(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);
        Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('activePeriods');
    }

    /** @test */
    public function dashboard_displays_recent_assessments(): void
    {
        Assessment::factory()->count(5)->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentAssessments');
    }

    /** @test */
    public function dashboard_displays_latest_results(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('latestResults');
    }

    /** @test */
    public function dashboard_works_without_teacher_profile(): void
    {
        $userWithoutProfile = User::factory()->create(['institution_id' => $this->institution->id]);
        $userWithoutProfile->roles()->attach($this->teacherRole);

        $response = $this->actingAs($userWithoutProfile)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('teacherProfile', null);
    }

    /** @test */
    public function non_teacher_cannot_access_dashboard(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->actingAs($assessor)
            ->get(route('teacher.dashboard'));

        $response->assertStatus(403);
    }
}
