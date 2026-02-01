<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\KpiFormSection;
use App\Models\KpiFormItem;
use App\Models\KpiFormItemOption;
use App\Models\CriteriaSet;
use App\Models\CriteriaNode;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KpiFormController extends Controller
{
    public function index()
    {
        $institution = auth()->user()->institution;

        $templates = KpiFormTemplate::with(['versions' => function($q) {
            $q->latest('version_number');
        }])
        ->where('institution_id', $institution?->id)
        ->latest()
        ->get();

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
        $template->load(['versions.sections.items.options', 'versions' => function($q) {
            $q->latest('version_number');
        }]);

        $latestVersion = $template->versions->first();

        $criteriaSets = CriteriaSet::with(['criteriaNodes' => function($q) {
            $q->orderBy('level')->orderBy('sort_order');
        }])
        ->where('institution_id', auth()->user()->institution_id)
        ->get();

        return view('admin.kpi-forms.builder', compact('template', 'latestVersion', 'criteriaSets'));
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

            if (!empty($sectionData['items'])) {
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

                    if (!empty($itemData['options'])) {
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
        $template->load(['versions.sections.items.options']);
        $latestVersion = $template->versions()->latest('version_number')->first();

        return view('admin.kpi-forms.preview', compact('template', 'latestVersion'));
    }

    public function publish(Request $request, KpiFormTemplate $template)
    {
        $latestVersion = $template->versions()->latest('version_number')->first();

        if (!$latestVersion->sections()->exists()) {
            return back()->with('error', 'Form harus memiliki minimal 1 section untuk dipublikasi.');
        }

        $latestVersion->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'publish_kpi_form',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Published KPI form version {$latestVersion->version_number}: {$template->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Form KPI berhasil dipublikasi.');
    }

    public function createNewVersion(KpiFormTemplate $template)
    {
        $latestVersion = $template->versions()->latest('version_number')->first();

        // Create new version
        $newVersion = KpiFormVersion::create([
            'id' => Str::ulid(),
            'template_id' => $template->id,
            'version_number' => $latestVersion->version_number + 1,
            'status' => 'draft',
        ]);

        // Copy sections and items
        foreach ($latestVersion->sections as $section) {
            $newSection = KpiFormSection::create([
                'id' => Str::ulid(),
                'version_id' => $newVersion->id,
                'title' => $section->title,
                'description' => $section->description,
                'sort_order' => $section->sort_order,
            ]);

            foreach ($section->items as $item) {
                $newItem = KpiFormItem::create([
                    'id' => Str::ulid(),
                    'section_id' => $newSection->id,
                    'criteria_node_id' => $item->criteria_node_id,
                    'label' => $item->label,
                    'field_type' => $item->field_type,
                    'is_required' => $item->is_required,
                    'sort_order' => $item->sort_order,
                ]);

                foreach ($item->options as $option) {
                    KpiFormItemOption::create([
                        'id' => Str::ulid(),
                        'item_id' => $newItem->id,
                        'label' => $option->label,
                        'value' => $option->value,
                        'score' => $option->score,
                        'sort_order' => $option->sort_order,
                    ]);
                }
            }
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_kpi_version',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Created new version {$newVersion->version_number} of KPI form: {$template->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

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
