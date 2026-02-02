<?php

namespace Tests\Feature\Auth;

use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected Institution $institution;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::factory()->create();
    }

    /** @test */
    public function login_page_is_displayed(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    /** @test */
    public function users_can_authenticate(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'status' => 'active',
        ]);
        $user->roles()->attach($role);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.dashboard'));
    }

    /** @test */
    public function admin_is_redirected_to_admin_dashboard(): void
    {
        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $admin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach($adminRole);

        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function super_admin_is_redirected_to_admin_dashboard(): void
    {
        $superAdminRole = Role::factory()->create(['key' => 'super_admin', 'name' => 'Super Admin']);
        $superAdmin = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'superadmin@example.com',
            'password' => Hash::make('password'),
        ]);
        $superAdmin->roles()->attach($superAdminRole);

        $response = $this->post(route('login'), [
            'email' => 'superadmin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function assessor_is_redirected_to_assessor_dashboard(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'assessor@example.com',
            'password' => Hash::make('password'),
        ]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->post(route('login'), [
            'email' => 'assessor@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('assessor.dashboard'));
    }

    /** @test */
    public function teacher_is_redirected_to_teacher_dashboard(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
        ]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->post(route('login'), [
            'email' => 'teacher@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.dashboard'));
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function users_cannot_authenticate_with_nonexistent_email(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'nonexistent@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function deactivated_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'deactivated@example.com',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'deactivated@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function inactive_users_cannot_authenticate(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'status' => 'inactive',
        ]);

        $response = $this->post(route('login'), [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function login_requires_email(): void
    {
        $response = $this->post(route('login'), [
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function login_requires_password(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function login_requires_valid_email(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'invalid-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function users_can_logout(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $user->roles()->attach($role);

        $this->actingAs($user);
        $this->assertAuthenticated();

        $response = $this->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    /** @test */
    public function guest_is_redirected_from_protected_route(): void
    {
        $response = $this->get(route('admin.dashboard'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function authenticated_users_are_redirected_from_login_page(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
        ]);
        $user->roles()->attach($role);

        $response = $this->actingAs($user)
            ->get(route('login'));

        $response->assertRedirect();
    }
}
