<?php

namespace Tests\Feature\Teacher;

use App\Models\AssessmentPeriod;
use App\Models\Institution;
use App\Models\PeriodResult;
use App\Models\Role;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultControllerTest extends TestCase
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
    public function teacher_can_access_results_index(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.index'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.results.index');
    }

    /** @test */
    public function guest_cannot_access_teacher_results(): void
    {
        $response = $this->get(route('teacher.results.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function results_index_displays_results(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.index'));

        $response->assertStatus(200);
        $response->assertViewHas('results');
        $response->assertViewHas('periods');
    }

    /** @test */
    public function results_can_be_filtered_by_period(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $periodResult = PeriodResult::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        TeacherPeriodResult::factory()->create([
            'period_result_id' => $periodResult->id,
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.index', ['period_id' => $period->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_can_view_single_result(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $periodResult = PeriodResult::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $result = TeacherPeriodResult::factory()->create([
            'period_result_id' => $periodResult->id,
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.show', $result));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.results.show');
    }

    /** @test */
    public function teacher_cannot_view_other_teacher_result(): void
    {
        $otherTeacherProfile = TeacherProfile::factory()->create();
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $periodResult = PeriodResult::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $result = TeacherPeriodResult::factory()->create([
            'period_result_id' => $periodResult->id,
            'teacher_profile_id' => $otherTeacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.show', $result));

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_download_result(): void
    {
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $periodResult = PeriodResult::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $result = TeacherPeriodResult::factory()->create([
            'period_result_id' => $periodResult->id,
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.download', $result));

        // This would typically return a PDF or document
        // The exact response depends on the implementation
        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_cannot_download_other_teacher_result(): void
    {
        $otherTeacherProfile = TeacherProfile::factory()->create();
        $period = AssessmentPeriod::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $periodResult = PeriodResult::factory()->create([
            'assessment_period_id' => $period->id,
        ]);
        $result = TeacherPeriodResult::factory()->create([
            'period_result_id' => $periodResult->id,
            'teacher_profile_id' => $otherTeacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.download', $result));

        $response->assertStatus(403);
    }

    /** @test */
    public function results_index_works_without_teacher_profile(): void
    {
        $userWithoutProfile = User::factory()->create(['institution_id' => $this->institution->id]);
        $userWithoutProfile->roles()->attach($this->teacherRole);

        $response = $this->actingAs($userWithoutProfile)
            ->get(route('teacher.results.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_teacher_cannot_access_results(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->actingAs($assessor)
            ->get(route('teacher.results.index'));

        $response->assertStatus(403);
    }
}
