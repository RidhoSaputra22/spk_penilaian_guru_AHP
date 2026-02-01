<?php

namespace Tests\Feature\Admin;

use App\Models\AhpComparison;
use App\Models\AhpModel;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AhpWeightingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected CriteriaSet $criteriaSet;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::factory()->create(['name' => 'admin', 'slug' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->roles()->attach($adminRole);

        $this->criteriaSet = CriteriaSet::factory()->create();
    }

    /** @test */
    public function admin_can_view_ahp_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.ahp.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.ahp.index');
    }

    /** @test */
    public function admin_can_create_ahp_model(): void
    {
        $period = AssessmentPeriod::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ahp.create-model'), [
                'period_id' => $period->id,
                'criteria_set_id' => $this->criteriaSet->id,
                'name' => 'AHP Model Semester 1',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ahp_models', [
            'period_id' => $period->id,
            'name' => 'AHP Model Semester 1',
        ]);
    }

    /** @test */
    public function admin_can_save_ahp_comparisons(): void
    {
        $ahpModel = AhpModel::factory()->create([
            'criteria_set_id' => $this->criteriaSet->id,
        ]);

        // Create criteria nodes
        $node1 = CriteriaNode::factory()->create([
            'criteria_set_id' => $this->criteriaSet->id,
            'level' => 1,
        ]);
        $node2 = CriteriaNode::factory()->create([
            'criteria_set_id' => $this->criteriaSet->id,
            'level' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ahp.save-comparisons'), [
                'ahp_model_id' => $ahpModel->id,
                'comparisons' => [
                    [
                        'node_a_id' => $node1->id,
                        'node_b_id' => $node2->id,
                        'value' => 3, // Node A is 3x more important than Node B
                    ],
                ],
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_finalize_ahp_model(): void
    {
        $ahpModel = AhpModel::factory()->create([
            'criteria_set_id' => $this->criteriaSet->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ahp.finalize', $ahpModel));

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_reset_ahp_model(): void
    {
        $ahpModel = AhpModel::factory()->create([
            'criteria_set_id' => $this->criteriaSet->id,
            'status' => 'draft',
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ahp.reset', $ahpModel));

        $response->assertRedirect();
    }

    /** @test */
    public function ahp_comparison_value_must_be_valid(): void
    {
        $ahpModel = AhpModel::factory()->create();
        $node1 = CriteriaNode::factory()->create();
        $node2 = CriteriaNode::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.ahp.save-comparisons'), [
                'ahp_model_id' => $ahpModel->id,
                'comparisons' => [
                    [
                        'node_a_id' => $node1->id,
                        'node_b_id' => $node2->id,
                        'value' => 10, // Invalid: must be 1-9
                    ],
                ],
            ]);

        $response->assertSessionHasErrors();
    }
}
