<?php

namespace Tests\Feature\Admin;

use App\Models\AhpModel;
use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::factory()->create();
        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $this->admin = User::factory()->create(['institution_id' => $this->institution->id]);
        $this->admin->roles()->attach($adminRole);
    }

    /** @test */
    public function admin_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
    }

    /** @test */
    public function guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function dashboard_displays_statistics(): void
    {
        // Create some test data
        AssessmentPeriod::factory()->count(3)->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalTeachers');
        $response->assertViewHas('totalAssessors');
    }

    /** @test */
    public function dashboard_displays_active_period(): void
    {
        AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('activePeriod');
    }

    /** @test */
    public function dashboard_displays_teacher_count(): void
    {
        $teacherUser = User::factory()->create(['institution_id' => $this->institution->id]);
        TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalTeachers');
    }

    /** @test */
    public function dashboard_displays_assessor_count(): void
    {
        $assessorUser = User::factory()->create(['institution_id' => $this->institution->id]);
        AssessorProfile::factory()->create(['user_id' => $assessorUser->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalAssessors');
    }

    /** @test */
    public function dashboard_displays_assessment_progress(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'open',
        ]);
        Assessment::factory()->count(5)->create([
            'assessment_period_id' => $period->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('totalAssessments');
        $response->assertViewHas('completedAssessments');
        $response->assertViewHas('assessmentProgress');
    }

    /** @test */
    public function dashboard_displays_recent_activities(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('recentActivities');
    }

    /** @test */
    public function dashboard_displays_ahp_model_info(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'open',
        ]);
        AhpModel::factory()->create([
            'assessment_period_id' => $period->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewHas('ahpModel');
    }

    /** @test */
    public function super_admin_can_access_dashboard(): void
    {
        $superAdminRole = Role::factory()->create(['key' => 'super_admin', 'name' => 'Super Admin']);
        $superAdmin = User::factory()->create(['institution_id' => $this->institution->id]);
        $superAdmin->roles()->attach($superAdminRole);

        $response = $this->actingAs($superAdmin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_dashboard(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }
}
