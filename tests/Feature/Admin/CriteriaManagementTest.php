<?php

namespace Tests\Feature\Admin;

use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CriteriaManagementTest extends TestCase
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
    public function admin_can_view_criteria_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.criteria.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.criteria.index');
    }

    /** @test */
    public function admin_can_create_criteria_set(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.criteria.store-set'), [
                'name' => 'Kriteria Penilaian 2025',
                'description' => 'Set kriteria untuk penilaian tahun 2025',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('criteria_sets', [
            'name' => 'Kriteria Penilaian 2025',
        ]);
    }

    /** @test */
    public function admin_can_update_criteria_set(): void
    {
        $criteriaSet = CriteriaSet::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.criteria.update-set', $criteriaSet), [
                'name' => 'Updated Criteria Set',
                'description' => 'Updated description',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('criteria_sets', [
            'id' => $criteriaSet->id,
            'name' => 'Updated Criteria Set',
        ]);
    }

    /** @test */
    public function admin_can_delete_criteria_set(): void
    {
        $criteriaSet = CriteriaSet::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.criteria.destroy-set', $criteriaSet));

        $response->assertRedirect();
        $this->assertDatabaseMissing('criteria_sets', ['id' => $criteriaSet->id]);
    }

    /** @test */
    public function admin_can_create_criteria_node(): void
    {
        $criteriaSet = CriteriaSet::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.criteria.store-node'), [
                'criteria_set_id' => $criteriaSet->id,
                'name' => 'Pedagogik',
                'code' => 'K1',
                'description' => 'Kriteria Pedagogik',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('criteria_nodes', [
            'name' => 'Pedagogik',
            'code' => 'K1',
        ]);
    }

    /** @test */
    public function admin_can_create_subcriteria_node(): void
    {
        $criteriaSet = CriteriaSet::factory()->create();
        $parentNode = CriteriaNode::factory()->create([
            'criteria_set_id' => $criteriaSet->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.criteria.store-node'), [
                'criteria_set_id' => $criteriaSet->id,
                'parent_id' => $parentNode->id,
                'name' => 'Perencanaan Pembelajaran',
                'code' => 'K1.1',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('criteria_nodes', [
            'name' => 'Perencanaan Pembelajaran',
            'parent_id' => $parentNode->id,
        ]);
    }

    /** @test */
    public function admin_can_update_criteria_node(): void
    {
        $node = CriteriaNode::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put(route('admin.criteria.update-node', $node), [
                'name' => 'Updated Node Name',
                'code' => $node->code,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('criteria_nodes', [
            'id' => $node->id,
            'name' => 'Updated Node Name',
        ]);
    }

    /** @test */
    public function admin_can_delete_criteria_node(): void
    {
        $node = CriteriaNode::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.criteria.destroy-node', $node));

        $response->assertRedirect();
        $this->assertDatabaseMissing('criteria_nodes', ['id' => $node->id]);
    }
}
