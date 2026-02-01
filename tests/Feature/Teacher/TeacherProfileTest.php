<?php

namespace Tests\Feature\Teacher;

use App\Models\Role;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $teacher;
    protected TeacherProfile $teacherProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['name' => 'teacher', 'slug' => 'teacher']);
        $this->teacher = User::factory()->create();
        $this->teacher->roles()->attach($role);
        $this->teacherProfile = TeacherProfile::factory()->create(['user_id' => $this->teacher->id]);
    }

    /** @test */
    public function teacher_can_view_profile_edit_page(): void
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.profile.edit');
    }

    /** @test */
    public function teacher_can_update_profile(): void
    {
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'name' => 'Updated Teacher Name',
                'email' => 'updatedteacher@example.com',
                'phone' => '081234567890',
                'address' => 'New Address',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->teacher->refresh();
        $this->assertEquals('Updated Teacher Name', $this->teacher->name);
        $this->assertEquals('updatedteacher@example.com', $this->teacher->email);
    }

    /** @test */
    public function teacher_can_update_password(): void
    {
        $this->teacher->update(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.password'), [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->teacher->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->teacher->password));
    }

    /** @test */
    public function teacher_cannot_update_password_with_wrong_current_password(): void
    {
        $this->teacher->update(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.password'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    /** @test */
    public function password_must_be_confirmed(): void
    {
        $this->teacher->update(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.password'), [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors('password');
    }

    /** @test */
    public function profile_update_validates_email_uniqueness(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'name' => 'Test Name',
                'email' => 'existing@example.com',
            ]);

        $response->assertSessionHasErrors('email');
    }

    /** @test */
    public function profile_update_validates_required_fields(): void
    {
        $response = $this->actingAs($this->teacher)
            ->put(route('teacher.profile.update'), [
                'name' => '',
                'email' => '',
            ]);

        $response->assertSessionHasErrors(['name', 'email']);
    }
}
