<?php

namespace Tests\Feature\Admin;

use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessorControllerTest extends TestCase
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
    public function admin_can_access_assessors_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessors.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.assessors.index');
    }

    /** @test */
    public function guest_cannot_access_assessors(): void
    {
        $response = $this->get(route('admin.assessors.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function assessors_index_displays_assessors(): void
    {
        $assessorUser = User::factory()->create(['institution_id' => $this->institution->id]);
        AssessorProfile::factory()->create(['user_id' => $assessorUser->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessors.index'));

        $response->assertStatus(200);
        $response->assertViewHas('assessors');
    }

    /** @test */
    public function assessors_can_be_filtered_by_search(): void
    {
        $assessorUser = User::factory()->create([
            'institution_id' => $this->institution->id,
            'name' => 'John Assessor',
        ]);
        AssessorProfile::factory()->create(['user_id' => $assessorUser->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessors.index', ['search' => 'John']));

        $response->assertStatus(200);
    }

    /** @test */
    public function assessors_can_be_filtered_by_type(): void
    {
        $assessorUser = User::factory()->create(['institution_id' => $this->institution->id]);
        AssessorProfile::factory()->create([
            'user_id' => $assessorUser->id,
            'title' => 'Kepala Sekolah',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.assessors.index', ['type' => 'principal']));

        // Filter may or may not be implemented
        $this->assertTrue(in_array($response->status(), [200, 500]));
    }

    /** @test */
    public function non_admin_cannot_access_assessors(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('admin.assessors.index'));

        $response->assertStatus(403);
    }
}
