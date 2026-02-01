<?php

namespace Tests\Feature\Teacher;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessmentStatusLog;
use App\Models\AssessorProfile;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected TeacherProfile $teacherProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['name' => 'teacher', 'slug' => 'teacher']);
        $this->teacher = User::factory()->create();
        $this->teacher->roles()->attach($role);
        $this->teacherProfile = TeacherProfile::factory()->create(['user_id' => $this->teacher->id]);
    }

    /** @test */
    public function teacher_can_view_assessment_status_list(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.status.index');
    }

    /** @test */
    public function teacher_can_see_own_assessments(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        Assessment::factory()->count(3)->create([
            'teacher_id' => $this->teacherProfile->id,
            'period_id' => $period->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index'));

        $response->assertStatus(200);
        $response->assertViewHas('assessments');
    }

    /** @test */
    public function teacher_can_view_assessment_details(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        $assessorRole = Role::factory()->create(['name' => 'assessor', 'slug' => 'assessor']);
        $assessorUser = User::factory()->create();
        $assessorUser->roles()->attach($assessorRole);
        $assessor = AssessorProfile::factory()->create(['user_id' => $assessorUser->id]);

        $assessment = Assessment::factory()->create([
            'teacher_id' => $this->teacherProfile->id,
            'assessor_id' => $assessor->id,
            'period_id' => $period->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.show', $assessment));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.status.show');
    }

    /** @test */
    public function teacher_can_see_status_timeline(): void
    {
        $assessment = Assessment::factory()->create([
            'teacher_id' => $this->teacherProfile->id,
            'status' => 'pending',
        ]);

        AssessmentStatusLog::factory()->create([
            'assessment_id' => $assessment->id,
            'status' => 'created',
        ]);
        AssessmentStatusLog::factory()->create([
            'assessment_id' => $assessment->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.show', $assessment));

        $response->assertStatus(200);
        $response->assertViewHas('statusLogs');
    }

    /** @test */
    public function teacher_cannot_view_other_teachers_assessment(): void
    {
        $otherTeacher = TeacherProfile::factory()->create();
        $otherAssessment = Assessment::factory()->create([
            'teacher_id' => $otherTeacher->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.show', $otherAssessment));

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_filter_assessments_by_period(): void
    {
        $period1 = AssessmentPeriod::factory()->create(['name' => 'Period 1']);
        $period2 = AssessmentPeriod::factory()->create(['name' => 'Period 2']);

        Assessment::factory()->count(2)->create([
            'teacher_id' => $this->teacherProfile->id,
            'period_id' => $period1->id,
        ]);
        Assessment::factory()->count(3)->create([
            'teacher_id' => $this->teacherProfile->id,
            'period_id' => $period2->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index', ['period_id' => $period1->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_can_filter_assessments_by_status(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);
        Assessment::factory()->count(2)->create([
            'teacher_id' => $this->teacherProfile->id,
            'period_id' => $period->id,
            'status' => 'pending',
        ]);
        Assessment::factory()->count(3)->create([
            'teacher_id' => $this->teacherProfile->id,
            'period_id' => $period->id,
            'status' => 'submitted',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.status.index', ['status' => 'submitted']));

        $response->assertStatus(200);
    }
}
