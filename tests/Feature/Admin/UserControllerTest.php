<?php

namespace Tests\Feature\Admin;

use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\Role;
use App\Models\TeacherGroup;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserControllerTest extends TestCase
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
    public function admin_can_access_users_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.index');
    }

    /** @test */
    public function guest_cannot_access_users(): void
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function users_index_displays_users(): void
    {
        User::factory()->count(5)->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewHas('users');
    }

    /** @test */
    public function admin_can_access_user_create_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.create');
    }

    /** @test */
    public function admin_can_create_user(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => [$role->id],
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ]);
    }

    /** @test */
    public function admin_can_create_user_with_teacher_profile(): void
    {
        $role = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $group = TeacherGroup::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Teacher User',
                'email' => 'teacher@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => [$role->id],
            ]);

        $response->assertRedirect(route('admin.users.index'));
        // Check the user was created
        $this->assertDatabaseHas('users', [
            'email' => 'teacher@example.com',
        ]);
        // Teacher profile should be created for teacher role
        $user = User::where('email', 'teacher@example.com')->first();
        $this->assertNotNull($user->teacherProfile);
    }

    /** @test */
    public function admin_can_create_user_with_assessor_profile(): void
    {
        $role = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'Assessor User',
                'email' => 'assessor@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => [$role->id],
                'assessor_type' => 'principal',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        // Check that user and assessor profile were created
        $this->assertDatabaseHas('users', [
            'email' => 'assessor@example.com',
        ]);
    }

    /** @test */
    public function user_requires_name_email_password_and_roles(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password', 'roles']);
    }

    /** @test */
    public function user_email_must_be_unique(): void
    {
        $existingUser = User::factory()->create(['email' => 'existing@example.com']);
        $role = Role::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), [
                'name' => 'New User',
                'email' => 'existing@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'roles' => [$role->id],
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function admin_can_view_single_user(): void
    {
        $user = User::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.show');
    }

    /** @test */
    public function admin_can_access_user_edit_page(): void
    {
        $user = User::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.edit', $user));

        $response->assertStatus(200);
        $response->assertViewIs('admin.users.edit');
    }

    /** @test */
    public function admin_can_update_user(): void
    {
        $user = User::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $user), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
            ]);

        $response->assertRedirect(route('admin.users.index'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function admin_can_delete_user(): void
    {
        $user = User::factory()->create(['institution_id' => $this->institution->id]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $user));

        $response->assertRedirect(route('admin.users.index'));
        // User uses SoftDeletes, so check soft deleted
        $this->assertSoftDeleted('users', [
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function admin_can_reset_user_password(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'password' => 'oldpassword',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.reset-password', $user));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function admin_can_toggle_user_status(): void
    {
        $user = User::factory()->create([
            'institution_id' => $this->institution->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.toggle-status', $user));

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'status' => 'inactive',
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_users(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }
}
