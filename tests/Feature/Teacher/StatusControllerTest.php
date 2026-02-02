<?php

namespace Tests\Feature\Teacher;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\Institution;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatusControllerTest extends TestCase
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
    public function teacher_can_access_status_index(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.status.index');
    }

    /** @test */
    public function guest_cannot_access_teacher_status(): void
    {
        $response = $this->get(route('teacher.status.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function status_index_displays_assessments(): void
    {
        Assessment::factory()->count(3)->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index'));

        $response->assertStatus(200);
        $response->assertViewHas('assessments');
        $response->assertViewHas('periods');
    }

    /** @test */
    public function status_can_be_filtered_by_period(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'assessment_period_id' => $period->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index', ['period_id' => $period->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function status_can_be_filtered_by_status(): void
    {
        Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index', ['status' => 'pending']));

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_can_view_single_assessment_status(): void
    {
        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.show', $assessment));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.status.show');
    }

    /** @test */
    public function teacher_cannot_view_other_teacher_assessment(): void
    {
        $otherTeacherProfile = TeacherProfile::factory()->create();
        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $otherTeacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.show', $assessment));

        $response->assertStatus(403);
    }

    /** @test */
    public function status_show_displays_assessment_details(): void
    {
        $assessment = Assessment::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.show', $assessment));

        $response->assertStatus(200);
        $response->assertViewHas('assessment');
        $response->assertViewHas('statusLogs');
    }

    /** @test */
    public function status_index_works_without_teacher_profile(): void
    {
        $userWithoutProfile = User::factory()->create(['institution_id' => $this->institution->id]);
        $userWithoutProfile->roles()->attach($this->teacherRole);

        $response = $this->actingAs($userWithoutProfile)
            ->get(route('teacher.status.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_teacher_cannot_access_status(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->actingAs($assessor)
            ->get(route('teacher.status.index'));

        $response->assertStatus(403);
    }
}
