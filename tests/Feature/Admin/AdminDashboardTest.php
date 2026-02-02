<?php

namespace Tests\Feature\Admin;

use App\Models\AssessmentPeriod;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin role and user
        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $this->admin = User::factory()->create();
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
        AssessmentPeriod::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.dashboard'));

        $response->assertStatus(200);
        // Controller returns these variables instead of 'stats'
        $response->assertViewHas('totalTeachers');
        $response->assertViewHas('totalAssessors');
    }
}
