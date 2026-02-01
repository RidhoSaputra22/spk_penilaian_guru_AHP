<?php

namespace Tests\Feature\Assessor;

use App\Models\AssessorProfile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AssessorProfileTest extends TestCase
{
    use RefreshDatabase;

    protected User $assessor;
    protected AssessorProfile $assessorProfile;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::factory()->create(['name' => 'assessor', 'slug' => 'assessor']);
        $this->assessor = User::factory()->create();
        $this->assessor->roles()->attach($role);
        $this->assessorProfile = AssessorProfile::factory()->create(['user_id' => $this->assessor->id]);
    }

    /** @test */
    public function assessor_can_view_profile_edit_page(): void
    {
        $response = $this->actingAs($this->assessor)
            ->get(route('assessor.profile.edit'));

        $response->assertStatus(200);
        $response->assertViewIs('assessor.profile.edit');
    }

    /** @test */
    public function assessor_can_update_profile(): void
    {
        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '081234567890',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assessor->refresh();
        $this->assertEquals('Updated Name', $this->assessor->name);
        $this->assertEquals('updated@example.com', $this->assessor->email);
    }

    /** @test */
    public function assessor_can_update_password(): void
    {
        $this->assessor->update(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.password'), [
                'current_password' => 'oldpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assessor->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->assessor->password));
    }

    /** @test */
    public function assessor_cannot_update_password_with_wrong_current_password(): void
    {
        $this->assessor->update(['password' => Hash::make('oldpassword')]);

        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.password'), [
                'current_password' => 'wrongpassword',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertSessionHasErrors('current_password');
    }

    /** @test */
    public function profile_update_validates_email_uniqueness(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->assessor)
            ->put(route('assessor.profile.update'), [
                'name' => 'Test Name',
                'email' => 'existing@example.com',
            ]);

        $response->assertSessionHasErrors('email');
    }
}
