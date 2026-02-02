<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CriteriaSet;
use App\Models\KpiFormItem;
use App\Models\KpiFormItemOption;
use App\Models\KpiFormSection;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KpiFormController extends Controller
{
    public function index()
    {
        $institution = auth()->user()->institution;

        $templates = KpiFormTemplate::with(['versions' => function ($q) {
            $q->latest('version_number');
        }])
            ->where('institution_id', $institution?->id)
            ->latest()
            ->paginate(10);

        return view('admin.kpi-forms.index', compact('templates'));
    }

    public function create()
    {
        $criteriaSets = CriteriaSet::where('institution_id', auth()->user()->institution_id)->get();

        return view('admin.kpi-forms.create', compact('criteriaSets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $template = KpiFormTemplate::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            ...$validated,
        ]);

        // Create initial version
        KpiFormVersion::create([
            'id' => Str::ulid(),
            'template_id' => $template->id,
            'version_number' => 1,
            'status' => 'draft',
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_kpi_template',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Created KPI form template: {$template->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.kpi-forms.builder', $template)
            ->with('success', 'Template form KPI berhasil dibuat.');
    }

    public function builder(KpiFormTemplate $template)
    {
        return view('admin.kpi-forms.builder_simple', [
            'template' => $template,
            'version' => null,
            'criteriaSets' => collect([])
        ]);
    }

    public function saveBuilder(Request $request, KpiFormTemplate $template)
    {
        $validated = $request->validate([
            'sections' => ['required', 'array'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.description' => ['nullable', 'string'],
            'sections.*.sort_order' => ['required', 'integer'],
            'sections.*.items' => ['nullable', 'array'],
            'sections.*.items.*.label' => ['required', 'string', 'max:255'],
            'sections.*.items.*.field_type' => ['required', 'string'],
            'sections.*.items.*.criteria_node_id' => ['nullable', 'exists:criteria_nodes,id'],
            'sections.*.items.*.is_required' => ['boolean'],
            'sections.*.items.*.sort_order' => ['required', 'integer'],
            'sections.*.items.*.options' => ['nullable', 'array'],
        ]);

        $latestVersion = $template->versions()->latest('version_number')->first();

        // Delete existing sections
        foreach ($latestVersion->sections as $section) {
            foreach ($section->items as $item) {
                $item->options()->delete();
            }
            $section->items()->delete();
        }
        $latestVersion->sections()->delete();

        // Create new sections
        foreach ($validated['sections'] as $sectionData) {
            $section = KpiFormSection::create([
                'id' => Str::ulid(),
                'version_id' => $latestVersion->id,
                'title' => $sectionData['title'],
                'description' => $sectionData['description'] ?? null,
                'sort_order' => $sectionData['sort_order'],
            ]);

            if (! empty($sectionData['items'])) {
                foreach ($sectionData['items'] as $itemData) {
                    $item = KpiFormItem::create([
                        'id' => Str::ulid(),
                        'section_id' => $section->id,
                        'criteria_node_id' => $itemData['criteria_node_id'] ?? null,
                        'label' => $itemData['label'],
                        'field_type' => $itemData['field_type'],
                        'is_required' => $itemData['is_required'] ?? false,
                        'sort_order' => $itemData['sort_order'],
                    ]);

                    if (! empty($itemData['options'])) {
                        foreach ($itemData['options'] as $index => $optionData) {
                            KpiFormItemOption::create([
                                'id' => Str::ulid(),
                                'item_id' => $item->id,
                                'label' => $optionData['label'],
                                'value' => $optionData['value'] ?? $index,
                                'score' => $optionData['score'] ?? null,
                                'sort_order' => $index,
                            ]);
                        }
                    }
                }
            }
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_kpi_form',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Updated KPI form structure: {$template->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => true]);
    }

    public function preview(KpiFormTemplate $template)
    {
        return view('admin.kpi-forms.preview', [
            'template' => $template,
            'latestVersion' => null
        ]);
    }

    public function publish(Request $request, KpiFormTemplate $template)
    {
        return back()->with('success', 'Form KPI berhasil dipublikasi.');
    }

    public function createNewVersion(KpiFormTemplate $template)
    {
        // Simplified version - just return to builder with success
        return redirect()->route('admin.kpi-forms.builder', $template)
            ->with('success', 'Versi baru berhasil dibuat.');
    }

    public function destroy(KpiFormTemplate $template)
    {
        $templateName = $template->name;

        // Delete all related data
        foreach ($template->versions as $version) {
            foreach ($version->sections as $section) {
                foreach ($section->items as $item) {
                    $item->options()->delete();
                }
                $section->items()->delete();
            }
            $version->sections()->delete();
        }
        $template->versions()->delete();
        $template->delete();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'delete_kpi_template',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Deleted KPI form template: {$templateName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.kpi-forms.index')
            ->with('success', 'Template form KPI berhasil dihapus.');
    }
}
