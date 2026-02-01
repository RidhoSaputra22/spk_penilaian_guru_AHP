<?php

namespace Tests\Feature\Admin;

use App\Models\AssessmentPeriod;
use App\Models\PeriodResult;
use App\Models\Role;
use App\Models\TeacherPeriodResult;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResultManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create(['name' => 'admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);
    }

    /** @test */
    public function admin_can_view_results_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.results.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.results.index');
    }

    /** @test */
    public function admin_can_view_result_details(): void
    {
        $result = PeriodResult::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.results.show', $result));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_calculate_results(): void
    {
        $period = AssessmentPeriod::factory()->create(['status' => 'active']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.results.calculate'), [
                'period_id' => $period->id,
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_export_results(): void
    {
        $period = AssessmentPeriod::factory()->create();
        PeriodResult::factory()->create(['period_id' => $period->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.results.export', ['period_id' => $period->id]));

        // Should return file download or redirect
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    /** @test */
    public function admin_can_filter_results_by_period(): void
    {
        $period = AssessmentPeriod::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.results.index', ['period_id' => $period->id]));

        $response->assertStatus(200);
    }
}
