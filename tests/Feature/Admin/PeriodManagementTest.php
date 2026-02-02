<?php

namespace Tests\Feature\Admin;

use App\Models\AssessmentPeriod;
use App\Models\CriteriaSet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeriodManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create(['key' => 'admin', 'name' => 'Admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
    }

    /** @test */
    public function admin_can_view_periods_list(): void
    {
        AssessmentPeriod::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.periods.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.periods.index');
    }

    /** @test */
    public function admin_can_create_period(): void
    {
        $criteriaSet = CriteriaSet::factory()->create();

        $periodData = [
            'name' => 'Semester Ganjil 2025/2026',
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonth()->format('Y-m-d'),
            'criteria_set_id' => $criteriaSet->id,
            'status' => 'draft',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.periods.store'), $periodData);

        $response->assertRedirect(route('admin.periods.index'));
        $this->assertDatabaseHas('assessment_periods', [
            'name' => 'Semester Ganjil 2025/2026',
        ]);
    }

    /** @test */
    public function admin_can_update_period(): void
    {
        $period = AssessmentPeriod::factory()->create();
        $criteriaSet = CriteriaSet::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.periods.update', $period), [
                'name' => 'Updated Period Name',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addMonth()->format('Y-m-d'),
                'criteria_set_id' => $criteriaSet->id,
                'status' => $period->status,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessment_periods', [
            'id' => $period->id,
            'name' => 'Updated Period Name',
        ]);
    }

    /** @test */
    public function admin_can_update_period_status(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.periods.update-status', $period), [
                'status' => 'open',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('assessment_periods', [
            'id' => $period->id,
            'status' => 'open',
        ]);
    }

    /** @test */
    public function admin_can_delete_period(): void
    {
        $period = AssessmentPeriod::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.periods.destroy', $period));

        $response->assertRedirect(route('admin.periods.index'));
        $this->assertSoftDeleted('assessment_periods', ['id' => $period->id]);
    }

    /** @test */
    public function period_requires_valid_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.periods.store'), []);

        $response->assertSessionHasErrors(['name', 'status']);
    }
}
