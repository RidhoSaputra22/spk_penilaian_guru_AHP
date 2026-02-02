<?php

namespace Tests\Feature\Teacher;

use App\Models\Institution;
use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
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
    public function teacher_can_access_profile_edit_page(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.profile.edit');
    }

    /** @test */
    public function guest_cannot_access_teacher_profile(): void
    {
        $response = $this->get(route('teacher.profile.edit'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function profile_page_displays_user_and_teacher_data(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewHas('user');
        $response->assertViewHas('teacherProfile');
    }

    /** @test */
    public function teacher_can_update_profile(): void
    {
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'name' => 'Updated Teacher Name',
                'email' => 'updatedteacher@example.com',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->teacher->id,
            'name' => 'Updated Teacher Name',
            'email' => 'updatedteacher@example.com',
        ]);
    }

    /** @test */
    public function profile_update_requires_name_and_email(): void
    {
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /** @test */
    public function profile_email_must_be_unique(): void
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'name' => 'Test Name',
                'email' => 'other@example.com',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function teacher_can_keep_same_email(): void
    {
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'name' => 'Updated Name',
                'email' => $this->teacher->email,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function teacher_can_update_password(): void
    {
        $this->teacher->update(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update-password'), [
                'current_password' => 'currentpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    /** @test */
    public function password_update_requires_current_password(): void
    {
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update-password'), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    /** @test */
    public function password_update_validates_current_password(): void
    {
        $this->teacher->update(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update-password'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    /** @test */
    public function password_update_requires_matching_confirmation(): void
    {
        $this->teacher->update(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update-password'), [
                'current_password' => 'currentpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function non_teacher_cannot_access_profile(): void
    {
        $assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $assessor->roles()->attach($assessorRole);

        $response = $this->actingAs($assessor)
            ->get(route('teacher.profile.edit'));

        $response->assertStatus(403);
    }
}
