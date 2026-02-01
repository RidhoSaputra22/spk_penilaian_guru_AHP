<?php

namespace Tests\Feature\Admin;

use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiFormTest extends TestCase
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
    public function admin_can_view_kpi_forms_list(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.kpi-forms.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.kpi-forms.index');
    }

    /** @test */
    public function admin_can_view_create_kpi_form(): void
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.kpi-forms.create'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.kpi-forms.create');
    }

    /** @test */
    public function admin_can_create_kpi_form_template(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.kpi-forms.store'), [
                'name' => 'Form KPI Guru 2025',
                'description' => 'Template form untuk penilaian guru tahun 2025',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('kpi_form_templates', [
            'name' => 'Form KPI Guru 2025',
        ]);
    }

    /** @test */
    public function admin_can_access_form_builder(): void
    {
        $template = KpiFormTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.kpi-forms.builder', $template));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_save_form_builder(): void
    {
        $template = KpiFormTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kpi-forms.save-builder', $template), [
                'sections' => [
                    [
                        'title' => 'Section 1',
                        'description' => 'Description',
                        'order' => 1,
                        'items' => [
                            [
                                'label' => 'Item 1',
                                'type' => 'rating',
                                'order' => 1,
                            ],
                        ],
                    ],
                ],
            ]);

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_preview_kpi_form(): void
    {
        $template = KpiFormTemplate::factory()->create();
        KpiFormVersion::factory()->create(['template_id' => $template->id]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.kpi-forms.preview', $template));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_publish_kpi_form(): void
    {
        $template = KpiFormTemplate::factory()->create(['status' => 'draft']);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kpi-forms.publish', $template));

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_create_new_version(): void
    {
        $template = KpiFormTemplate::factory()->create(['status' => 'published']);
        KpiFormVersion::factory()->create([
            'template_id' => $template->id,
            'version_number' => 1,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.kpi-forms.new-version', $template));

        $response->assertRedirect();
    }

    /** @test */
    public function admin_can_delete_kpi_form(): void
    {
        $template = KpiFormTemplate::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.kpi-forms.destroy', $template));

        $response->assertRedirect(route('admin.kpi-forms.index'));
        $this->assertSoftDeleted('kpi_form_templates', ['id' => $template->id]);
    }
}
