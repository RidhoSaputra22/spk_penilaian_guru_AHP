<?php

namespace Tests\Feature\Auth;

use App\Models\AssessorProfile;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_access_admin_routes(): void
    {
        $role = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($role);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));
        $response->assertStatus(200);
    }

    /** @test */
    public function admin_cannot_access_assessor_routes(): void
    {
        $role = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($role);

        $response = $this->actingAs($admin)->get(route('assessor.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_cannot_access_teacher_routes(): void
    {
        $role = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $admin = User::factory()->create();
        $admin->roles()->attach($role);

        $response = $this->actingAs($admin)->get(route('teacher.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function assessor_can_access_assessor_routes(): void
    {
        $role = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create();
        $assessor->roles()->attach($role);
        AssessorProfile::factory()->create(['user_id' => $assessor->id]);

        $response = $this->actingAs($assessor)->get(route('assessor.dashboard'));
        $response->assertStatus(200);
    }

    /** @test */
    public function assessor_cannot_access_admin_routes(): void
    {
        $role = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create();
        $assessor->roles()->attach($role);

        $response = $this->actingAs($assessor)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function assessor_cannot_access_teacher_routes(): void
    {
        $role = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create();
        $assessor->roles()->attach($role);

        $response = $this->actingAs($assessor)->get(route('teacher.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_can_access_teacher_routes(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create();
        $teacher->roles()->attach($role);
        TeacherProfile::factory()->create(['user_id' => $teacher->id]);

        $response = $this->actingAs($teacher)->get(route('teacher.dashboard'));
        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_cannot_access_admin_routes(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create();
        $teacher->roles()->attach($role);

        $response = $this->actingAs($teacher)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function teacher_cannot_access_assessor_routes(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create();
        $teacher->roles()->attach($role);

        $response = $this->actingAs($teacher)->get(route('assessor.dashboard'));
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_access_protected_routes(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('assessor.dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('teacher.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function user_with_multiple_roles_can_access_multiple_panels(): void
    {
        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);

        $user = User::factory()->create();
        $user->roles()->attach([$adminRole->id, $assessorRole->id]);
        AssessorProfile::factory()->create(['user_id' => $user->id]);

        // Can access admin routes
        $response = $this->actingAs($user)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        // Can access assessor routes
        $response = $this->actingAs($user)->get(route('assessor.dashboard'));
        $response->assertStatus(200);
    }
}
