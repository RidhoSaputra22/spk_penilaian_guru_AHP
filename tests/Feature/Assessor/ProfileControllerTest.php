<?php

namespace Tests\Feature\Assessor;

use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $assessor;
    protected AssessorProfile $assessorProfile;
    protected Institution $institution;
    protected Role $assessorRole;

    protected function setUp(): void
    {
        parent::setUp();

        $this->institution = Institution::factory()->create();
        $this->assessorRole = Role::factory()->create(['key' => 'assessor', 'name' => 'Assessor']);
        $this->assessor = User::factory()->create(['institution_id' => $this->institution->id]);
        $this->assessor->roles()->attach($this->assessorRole);
        $this->assessorProfile = AssessorProfile::factory()->create(['user_id' => $this->assessor->id]);
    }

    /** @test */
    public function assessor_can_access_profile_edit_page(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.profile.edit');
    }

    /** @test */
    public function guest_cannot_access_assessor_profile(): void
    {
        $response = $this->get(route('assessor.profile.edit'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function profile_page_displays_user_data(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewHas('user');
        $response->assertViewHas('assessorProfile');
    }

    /** @test */
    public function assessor_can_update_profile(): void
    {
        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'title' => 'Kepala Madrasah',
            ]);

        $response->assertRedirect(route('assessor.profile.edit'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $this->assessor->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function profile_update_requires_name_and_email(): void
    {
        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update'), []);

        $response->assertSessionHasErrors(['name', 'email']);
    }

    /** @test */
    public function profile_email_must_be_unique(): void
    {
        $otherUser = User::factory()->create(['email' => 'other@example.com']);

        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update'), [
                'name' => 'Test Name',
                'email' => 'other@example.com',
            ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function assessor_can_update_password(): void
    {
        $this->assessor->update(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update-password'), [
                'current_password' => 'currentpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect(route('assessor.profile.edit'));
        $response->assertSessionHas('success');
    }

    /** @test */
    public function password_update_requires_current_password(): void
    {
        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update-password'), [
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    /** @test */
    public function password_update_requires_matching_confirmation(): void
    {
        $this->assessor->update(['password' => Hash::make('currentpassword')]);

        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update-password'), [
                'current_password' => 'currentpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'differentpassword',
            ]);

        $response->assertSessionHasErrors(['password']);
    }

    /** @test */
    public function non_assessor_cannot_access_profile(): void
    {
        $teacherRole = Role::factory()->create(['key' => 'teacher', 'name' => 'Teacher']);
        $teacher = User::factory()->create(['institution_id' => $this->institution->id]);
        $teacher->roles()->attach($teacherRole);

        $response = $this->actingAs($teacher)
            ->get(route('assessor.profile.edit'));

        $response->assertStatus(403);
    }
}
