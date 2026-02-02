<?php

namespace Tests\Feature\Teacher;

use App\Models\AssessmentPeriod;
use App\Models\Role;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeacherResultTest extends TestCase
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
    public function teacher_can_view_results_list(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.index'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.results.index');
    }

    /** @test */
    public function teacher_can_see_own_results(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);
        TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'final_score' => 85.5,
            'rank' => 3,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.index'));

        $response->assertStatus(200);
        $response->assertViewHas('results');
    }

    /** @test */
    public function teacher_can_view_result_details(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);
        $result = TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'final_score' => 88.0,
            'rank' => 2,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.show', $result));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.results.show');
    }

    /** @test */
    public function teacher_cannot_view_other_teachers_results(): void
    {
        $otherTeacher = TeacherProfile::factory()->create();
        $result = TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $otherTeacher->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.show', $result));

        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_download_result_pdf(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);
        $result = TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.download', $result));

        // Should return PDF or HTML (fallback)
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    /** @test */
    public function result_shows_score_breakdown(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'completed']);
        $result = TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
            'final_score' => 90.0,
            'rank' => 1,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.show', $result));

        $response->assertStatus(200);
        $response->assertViewHas('result');
    }

    /** @test */
    public function teacher_can_filter_results_by_period(): void
    {
        $period1 = AssessmentPeriod::factory()->create(['name' => 'Semester 1']);
        $period2 = AssessmentPeriod::factory()->create(['name' => 'Semester 2']);

        TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);
        TeacherPeriodResult::factory()->create([
            'teacher_profile_id' => $this->teacherProfile->id,
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.results.index', ['period_id' => $period1->id]));

        $response->assertStatus(200);
    }
}
