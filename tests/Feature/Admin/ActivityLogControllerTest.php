<?php

namespace Tests\Feature\Admin;

use App\Models\ActivityLog;
use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
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
    public function admin_can_access_activity_logs_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.activity-logs.index');
    }

    /** @test */
    public function guest_cannot_access_activity_logs(): void
    {
        $response = $this->get(route('admin.activity-logs.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function activity_logs_index_displays_logs(): void
    {
        ActivityLog::factory()->count(5)->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(200);
        $response->assertViewHas('logs');
    }

    /** @test */
    public function activity_logs_can_be_filtered_by_search(): void
    {
        ActivityLog::factory()->create([
            'user_id' => $this->admin->id,
            'action' => 'create_user_action',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', ['search' => 'create']));

        $response->assertStatus(200);
    }

    /** @test */
    public function activity_logs_can_be_filtered_by_action(): void
    {
        ActivityLog::factory()->create([
            'user_id' => $this->admin->id,
            'action' => 'create_user',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', ['action' => 'create_user']));

        $response->assertStatus(200);
    }

    /** @test */
    public function activity_logs_can_be_filtered_by_date_range(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', [
                'date_from' => now()->subDays(7)->format('Y-m-d'),
                'date_to' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);
    }

    /** @test */
    public function activity_logs_can_be_filtered_by_user(): void
    {
        ActivityLog::factory()->create([
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.activity-logs.index', ['user' => $this->admin->id]));

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_access_activity_logs(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('admin.activity-logs.index'));

        $response->assertStatus(403);
    }
}
