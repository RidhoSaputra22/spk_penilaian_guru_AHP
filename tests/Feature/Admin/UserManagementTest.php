<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Role $teacherRole;

    protected Role $assessorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $this->teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $this->assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);

        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
    }

    /** @test */
    public function admin_can_view_users_list(): void
    {
        User::factory()->count(5)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    /** @test */
    public function admin_can_view_create_user_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
    }

    /** @test */
    public function admin_can_create_new_user(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$this->teacherRole->id],
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ]);
    }

    /** @test */
    public function admin_can_view_user_details(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_edit_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
    }

    /** @test */
    public function admin_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => $user->email,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    /** @test */
    public function admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /** @test */
    public function admin_can_reset_user_password(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.reset-password', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_toggle_user_status(): void
    {
        $user = User::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $user));

        $response->assertRedirect();
    }

    /** @test */
    public function create_user_requires_valid_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }
}
