<?php

namespace Tests\Feature\Auth;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
    }

    /** @test */
    public function users_can_authenticate(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
    }

    /** @test */
    public function users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->post(route('login'), [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    /** @test */
    public function users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('logout'));

        $this->assertGuest();
    }

    /** @test */
    public function admin_is_redirected_to_admin_dashboard(): void
    {
        $adminRole = Role::factory()->create(['name' => 'admin', 'slug' => 'admin']);
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $admin->roles()->attach($adminRole);

        $response = $this->post(route('login'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
    }

    /** @test */
    public function assessor_is_redirected_to_assessor_dashboard(): void
    {
        $assessorRole = Role::factory()->create(['name' => 'assessor', 'slug' => 'assessor']);
        $assessor = User::factory()->create([
            'email' => 'assessor@example.com',
            'password' => Hash::make('password'),
        ]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->post(route('login'), [
            'email' => 'assessor@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('assessor.dashboard'));
    }

    /** @test */
    public function teacher_is_redirected_to_teacher_dashboard(): void
    {
        $teacherRole = Role::factory()->create(['name' => 'teacher', 'slug' => 'teacher']);
        $teacher = User::factory()->create([
            'email' => 'teacher@example.com',
            'password' => Hash::make('password'),
        ]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->post(route('login'), [
            'email' => 'teacher@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('teacher.dashboard'));
    }

    /** @test */
    public function inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => Hash::make('password'),
            'is_active' => false,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'inactive@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors();
    }

    /** @test */
    public function login_validates_required_fields(): void
    {
        $response = $this->post(route('login'), [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
    }

    /** @test */
    public function login_validates_email_format(): void
    {
        $response = $this->post(route('login'), [
            'email' => 'not-an-email',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
