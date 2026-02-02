<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\KpiFormAssignment;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
    }

    /** @test */
    public function admin_can_view_assessments_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.assessments.index');
    }

    /** @test */
    public function admin_can_view_assessment_details(): void
    {
        $assessment = Assessment::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessments.show', $assessment));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_assign_assessment(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);

        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacherUser = User::factory()->create();
        $teacherUser->roles()->attach($teacherRole);
        $teacher = TeacherProfile::factory()->create(['user_id' => $teacherUser->id]);

        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessorUser = User::factory()->create();
        $assessorUser->roles()->attach($assessorRole);
        $assessor = AssessorProfile::factory()->create(['user_id' => $assessorUser->id]);

        $assignment = KpiFormAssignment::factory()->create(['assessment_period_id' => $period->id]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.assessments.assign'), [
                'period_id' => $period->id,
                'teacher_id' => $teacher->id,
                'assessor_id' => $assessor->id,
                'assignment_id' => $assignment->id,
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_filter_assessments_by_period(): void
    {
        $period = AssessmentPeriod::factory()->create();
        Assessment::factory()->count(3)->create(['assessment_period_id' => $period->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessments.index', ['period_id' => $period->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_filter_assessments_by_status(): void
    {
        Assessment::factory()->count(2)->create(['status' => 'pending']);
        Assessment::factory()->count(3)->create(['status' => 'submitted']);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessments.index', ['status' => 'submitted']));

        $response->assertStatus(200);
    }
}
