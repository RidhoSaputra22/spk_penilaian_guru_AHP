<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AhpWeight;
use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\KpiFormItem;
use App\Models\KpiFormItemOption;
use App\Models\KpiFormSection;
use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\ScoringScale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class KpiFormController extends Controller
{
    public function index()
    {
        $institution = auth()->user()->institution;

        $templates = KpiFormTemplate::with(['versions' => function ($q) {
            $q->latest('version');
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
            'criteria_set_id' => ['nullable', 'exists:criteria_sets,id'],
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
            'version' => 1,
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

    public function edit(KpiFormTemplate $template)
    {
        $criteriaSets = CriteriaSet::where('institution_id', auth()->user()->institution_id)->get();

        return view('admin.kpi-forms.edit', compact('template', 'criteriaSets'));
    }

    public function update(Request $request, KpiFormTemplate $template)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'criteria_set_id' => ['nullable', 'exists:criteria_sets,id'],
        ]);

        $template->update($validated);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_kpi_template_info',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Updated KPI form template info: {$template->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.kpi-forms.index')
            ->with('success', 'Template form KPI berhasil diperbarui.');
    }

    public function clone(KpiFormTemplate $template)
    {
        $newTemplate = KpiFormTemplate::create([
            'id' => Str::ulid(),
            'institution_id' => $template->institution_id,
            'name' => $template->name.' (Copy)',
            'description' => $template->description,
        ]);

        // Create initial version
        KpiFormVersion::create([
            'id' => Str::ulid(),
            'template_id' => $newTemplate->id,
            'version' => 1,
            'status' => 'draft',
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'clone_kpi_template',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $newTemplate->id,
            'description' => "Cloned KPI form template: {$template->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.kpi-forms.index')
            ->with('success', 'Template form KPI berhasil diduplikasi.');
    }

    public function versions(KpiFormTemplate $template)
    {
        $versions = $template->versions()->with(['sections.items.options'])->latest('version')->get();

        return view('admin.kpi-forms.versions', compact('template', 'versions'));
    }

    public function builderSimple(KpiFormTemplate $template)
    {
        $version = $template->versions()->with(['sections.items.options', 'sections.items.criteriaNode', 'sections.criteriaNode'])->latest('version')->first();

        // If no version exists, create one
        if (! $version) {
            $version = KpiFormVersion::create([
                'id' => Str::ulid(),
                'template_id' => $template->id,
                'version' => '1.0',
                'status' => 'draft',
            ]);
        }

        $criteriaNodes = CriteriaNode::whereHas('set', function ($q) {
            $q->where('institution_id', auth()->user()->institution_id);
        })->get();

        // Get linked criteria set info
        $criteriaSet = $template->criteriaSet;

        // Get AHP weights if criteria set is linked to an AHP model
        $ahpWeights = collect();
        if ($criteriaSet) {
            $ahpWeights = AhpWeight::whereHas('model', function ($q) use ($criteriaSet) {
                $q->where('criteria_set_id', $criteriaSet->id)
                  ->where('status', 'finalized');
            })->with('criteriaNode')->get()->keyBy('criteria_node_id');
        }

        return view('admin.kpi-forms.builder_simple', [
            'template' => $template,
            'version' => $version,
            'criteriaNodes' => $criteriaNodes,
            'criteriaSet' => $criteriaSet,
            'ahpWeights' => $ahpWeights,
        ]);
    }

    public function preview(KpiFormTemplate $template)
    {
        $latestVersion = $template->versions()->with(['sections.items.options', 'sections.items.criteriaNode', 'sections.criteriaNode'])->latest('version')->first();

        // Get linked criteria set info
        $criteriaSet = $template->criteriaSet;

        // Get AHP weights if criteria set is linked
        $ahpWeights = collect();
        if ($criteriaSet) {
            $ahpWeights = AhpWeight::whereHas('model', function ($q) use ($criteriaSet) {
                $q->where('criteria_set_id', $criteriaSet->id)
                  ->where('status', 'finalized');
            })->with('criteriaNode')->get()->keyBy('criteria_node_id');
        }

        return view('admin.kpi-forms.preview', [
            'template' => $template,
            'latestVersion' => $latestVersion,
            'criteriaSet' => $criteriaSet,
            'ahpWeights' => $ahpWeights,
        ]);
    }

    public function publish(Request $request, KpiFormTemplate $template)
    {
        $version = $template->versions()->where('status', 'draft')->latest('version')->first();

        if ($version) {
            $version->update(['status' => 'published']);

            ActivityLog::create([
                'id' => Str::ulid(),
                'user_id' => auth()->id(),
                'action' => 'publish_kpi_form',
                'entity_type' => KpiFormVersion::class,
                'entity_id' => $version->id,
                'description' => "Published KPI form: {$template->name} v{$version->version}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return back()->with('success', 'Form KPI berhasil dipublikasi.');
    }

    public function generateFromCriteria(Request $request, KpiFormTemplate $template)
    {
        // Ensure a criteria set is linked
        if (! $template->criteria_set_id) {
            return back()->with('error', 'Template belum terhubung dengan Set Kriteria. Silakan edit template dan pilih Set Kriteria terlebih dahulu.');
        }

        $version = $template->versions()->where('status', 'draft')->latest('version')->first();

        if (! $version) {
            return back()->with('error', 'Tidak ada versi draft yang tersedia.');
        }

        // Check if version already has sections
        if ($version->sections()->count() > 0) {
            // Check if user confirmed overwrite
            if (! $request->boolean('confirm_overwrite')) {
                return back()->with('warning', 'Versi ini sudah memiliki seksi. Centang konfirmasi untuk menimpa data yang ada.');
            }

            // Delete existing sections & items
            foreach ($version->sections as $section) {
                foreach ($section->items as $item) {
                    $item->options()->delete();
                }
                $section->items()->delete();
            }
            $version->sections()->delete();
        }

        $criteriaSet = $template->criteriaSet;

        // Get the goal node
        $goalNode = $criteriaSet->nodes()
            ->where('node_type', 'goal')
            ->first();

        if (! $goalNode) {
            return back()->with('error', 'Set Kriteria tidak memiliki node Goal.');
        }

        // Get criteria nodes (children of goal)
        $criteriaNodes = CriteriaNode::where('parent_id', $goalNode->id)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        if ($criteriaNodes->isEmpty()) {
            return back()->with('error', 'Set Kriteria tidak memiliki kriteria (children of Goal).');
        }

        $sectionSortOrder = 1;

        foreach ($criteriaNodes as $criterion) {
            // Create section from criteria node
            $section = KpiFormSection::create([
                'id' => Str::ulid(),
                'form_version_id' => $version->id,
                'criteria_node_id' => $criterion->id,
                'title' => $criterion->name,
                'description' => $criterion->description,
                'sort_order' => $sectionSortOrder++,
            ]);

            // Get sub-criteria (children of this criteria)
            $subCriteria = CriteriaNode::where('parent_id', $criterion->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();

            $itemSortOrder = 1;

            if ($subCriteria->isNotEmpty()) {
                // Create items from sub-criteria nodes
                foreach ($subCriteria as $subCriterion) {
                    KpiFormItem::create([
                        'id' => Str::ulid(),
                        'section_id' => $section->id,
                        'criteria_node_id' => $subCriterion->id,
                        'label' => $subCriterion->name,
                        'help_text' => $subCriterion->description,
                        'field_type' => 'numeric',
                        'is_required' => true,
                        'min_value' => 1,
                        'max_value' => 5,
                        'sort_order' => $itemSortOrder++,
                    ]);
                }
            } else {
                // No sub-criteria, create a single item for the criteria itself
                KpiFormItem::create([
                    'id' => Str::ulid(),
                    'section_id' => $section->id,
                    'criteria_node_id' => $criterion->id,
                    'label' => $criterion->name,
                    'help_text' => $criterion->description,
                    'field_type' => 'numeric',
                    'is_required' => true,
                    'min_value' => 1,
                    'max_value' => 5,
                    'sort_order' => $itemSortOrder++,
                ]);
            }
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'generate_kpi_from_criteria',
            'entity_type' => KpiFormTemplate::class,
            'entity_id' => $template->id,
            'description' => "Generated KPI form sections/items from criteria set: {$criteriaSet->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.kpi-forms.builder', $template)
            ->with('success', "Berhasil membuat {$criteriaNodes->count()} seksi dari Set Kriteria \"{$criteriaSet->name}\". Anda dapat menyesuaikan tipe field dan detail item sesuai kebutuhan.");
    }

    public function publishVersion(Request $request, KpiFormVersion $version)
    {
        $version->update(['status' => 'published']);

        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'publish_kpi_form',
            'entity_type' => KpiFormVersion::class,
            'entity_id' => $version->id,
            'description' => "Published KPI form version: v{$version->version}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Form KPI berhasil dipublikasi.');
    }

    public function createSection(KpiFormVersion $version)
    {
        $template = $version->template;

        // Get criteria from active criteria set for this institution
        $criteriaSet = CriteriaSet::where('institution_id', auth()->user()->institution_id)
            ->where('is_active', true)
            ->first();

        $criteriaNodes = $criteriaSet
            ? $criteriaSet->nodes()->whereNull('parent_id')->orderBy('sort_order')->get()
            : collect();

        $criteriaOptions = $criteriaNodes->mapWithKeys(fn ($n) => [$n->id => "[{$n->code}] {$n->name}"])->toArray();

        return view('admin.kpi-forms.create-section', compact('template', 'version', 'criteriaOptions'));
    }

    public function addSection(Request $request, KpiFormVersion $version)
    {
        if ($version->status === 'published') {
            return back()->with('error', 'Form yang sudah dipublish tidak dapat diubah.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'criteria_node_id' => ['nullable', 'exists:criteria_nodes,id'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ]);

        $maxSortOrder = $version->sections()->max('sort_order') ?? 0;

        KpiFormSection::create([
            'id' => Str::ulid(),
            'form_version_id' => $version->id,
            'criteria_node_id' => $validated['criteria_node_id'] ?? null,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'sort_order' => $validated['sort_order'] ?? ($maxSortOrder + 1),
        ]);

        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'add_kpi_section',
            'entity_type' => KpiFormVersion::class,
            'entity_id' => $version->id,
            'description' => "Added section to KPI form: {$validated['title']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('admin.kpi-forms.builder', $version->template)
            ->with('success', 'Seksi berhasil ditambahkan.');
    }

    public function editSection(KpiFormSection $section)
    {
        $template = $section->version->template;
        $version = $section->version;

        // Get criteria from active criteria set for this institution
        $criteriaSet = CriteriaSet::where('institution_id', auth()->user()->institution_id)
            ->where('is_active', true)
            ->first();

        $criteriaNodes = $criteriaSet
            ? $criteriaSet->nodes()->whereNull('parent_id')->orderBy('sort_order')->get()
            : collect();

        $criteriaOptions = $criteriaNodes->mapWithKeys(fn ($n) => [$n->id => "[{$n->code}] {$n->name}"])->toArray();

        return view('admin.kpi-forms.edit-section', compact('template', 'version', 'section', 'criteriaOptions'));
    }

    public function updateSection(Request $request, KpiFormSection $section)
    {
        if ($section->version->status === 'published') {
            return back()->with('error', 'Form yang sudah dipublish tidak dapat diubah.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'criteria_node_id' => ['nullable', 'exists:criteria_nodes,id'],
            'sort_order' => ['nullable', 'integer', 'min:1'],
        ]);

        $section->update($validated);

        return redirect()
            ->route('admin.kpi-forms.builder', $section->version->template)
            ->with('success', 'Seksi berhasil diperbarui.');
    }

    public function deleteSection(KpiFormSection $section)
    {
        if ($section->version->status === 'published') {
            return back()->with('error', 'Form yang sudah dipublish tidak dapat diubah.');
        }

        // Delete all items and their options first
        foreach ($section->items as $item) {
            $item->options()->delete();
        }
        $section->items()->delete();
        $section->delete();

        return back()->with('success', 'Seksi berhasil dihapus.');
    }

    public function createItem(KpiFormSection $section)
    {
        $template = $section->version->template;
        $version = $section->version;

        // Get criteria from active criteria set for this institution (sub-criteria/indicators)
        $criteriaSet = CriteriaSet::where('institution_id', auth()->user()->institution_id)
            ->where('is_active', true)
            ->first();

        // Get sub-criteria (children of main criteria) for form items
        $criteriaNodes = $criteriaSet
            ? $criteriaSet->nodes()->whereNotNull('parent_id')->orderBy('sort_order')->get()
            : collect();

        $criteriaOptions = $criteriaNodes->mapWithKeys(fn ($n) => [$n->id => "[{$n->code}] {$n->name}"])->toArray();

        $fieldTypes = [
            'numeric' => 'Skor Numerik',
            'dropdown' => 'Dropdown Skala',
            'radio' => 'Radio Button',
            'yesno' => 'Ya/Tidak',
            'textarea' => 'Catatan',
        ];

        // Get scoring scales for this institution
        $scoringScales = ScoringScale::where('institution_id', auth()->user()->institution_id)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => $s->name])
            ->toArray();

        return view('admin.kpi-forms.add-item', compact('template', 'version', 'section', 'criteriaOptions', 'fieldTypes', 'scoringScales'));
    }

    public function addItem(Request $request, KpiFormVersion $version)
    {
        if ($version->status === 'published') {
            return back()->with('error', 'Form yang sudah dipublish tidak dapat diubah.');
        }

        $validated = $request->validate([
            'section_id' => ['required', 'exists:kpi_form_sections,id'],
            'label' => ['required', 'string', 'max:255'],
            'help_text' => ['nullable', 'string'],
            'criteria_node_id' => ['nullable', 'exists:criteria_nodes,id'],
            'field_type' => ['required', 'in:numeric,dropdown,radio,yesno,textarea'],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'scoring_scale_id' => ['nullable', 'exists:scoring_scales,id'],
            'default_value' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable'],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['nullable', 'string', 'max:255'],
            'options.*.value' => ['nullable', 'string', 'max:255'],
            'options.*.score_value' => ['nullable', 'numeric'],
        ]);

        // Custom validation for unique option values within the same item
        if (in_array($validated['field_type'], ['dropdown', 'radio']) && !empty($validated['options'])) {
            $optionValues = [];
            foreach ($validated['options'] as $index => $optionData) {
                if (!empty($optionData['value'])) {
                    if (in_array($optionData['value'], $optionValues)) {
                        return back()->withErrors([
                            "options.{$index}.value" => 'Nilai opsi harus unik dalam satu item.'
                        ])->withInput();
                    }
                    $optionValues[] = $optionData['value'];
                }
            }
        }

        $section = KpiFormSection::find($validated['section_id']);
        $maxSortOrder = $section->items()->max('sort_order') ?? 0;

        $item = KpiFormItem::create([
            'id' => Str::ulid(),
            'section_id' => $validated['section_id'],
            'criteria_node_id' => $validated['criteria_node_id'] ?? null,
            'label' => $validated['label'],
            'help_text' => $validated['help_text'] ?? null,
            'field_type' => $validated['field_type'],
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
            'scoring_scale_id' => $validated['scoring_scale_id'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'is_required' => isset($validated['is_required']),
            'sort_order' => $validated['sort_order'] ?? ($maxSortOrder + 1),
        ]);

        // Create options for dropdown/radio field types
        if (in_array($validated['field_type'], ['dropdown', 'radio']) && !empty($validated['options'])) {
            $sortOrder = 0;
            foreach ($validated['options'] as $optionData) {
                if (!empty($optionData['label']) && !empty($optionData['value'])) {
                    KpiFormItemOption::create([
                        'id' => Str::ulid(),
                        'item_id' => $item->id,
                        'label' => $optionData['label'],
                        'value' => $optionData['value'],
                        'score_value' => $optionData['score_value'] ?? null,
                        'sort_order' => $sortOrder++,
                    ]);
                }
            }
        }

        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'add_kpi_item',
            'entity_type' => KpiFormSection::class,
            'entity_id' => $section->id,
            'description' => "Added item to KPI form section: {$validated['label']}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()
            ->route('admin.kpi-forms.builder', $version->template)
            ->with('success', 'Item berhasil ditambahkan.');
    }

    public function editItem(KpiFormItem $item)
    {
        $template = $item->section->version->template;
        $version = $item->section->version;

        // Get criteria from active criteria set for this institution (sub-criteria/indicators)
        $criteriaSet = CriteriaSet::where('institution_id', auth()->user()->institution_id)
            ->where('is_active', true)
            ->first();

        // Get sub-criteria (children of main criteria) for form items
        $criteriaNodes = $criteriaSet
            ? $criteriaSet->nodes()->whereNotNull('parent_id')->orderBy('sort_order')->get()
            : collect();

        $criteriaOptions = $criteriaNodes->mapWithKeys(fn ($n) => [$n->id => "[{$n->code}] {$n->name}"])->toArray();

        $fieldTypes = [
            'numeric' => 'Skor Numerik',
            'dropdown' => 'Dropdown Skala',
            'radio' => 'Radio Button',
            'yesno' => 'Ya/Tidak',
            'textarea' => 'Catatan',
        ];

        // Get scoring scales for this institution
        $scoringScales = ScoringScale::where('institution_id', auth()->user()->institution_id)
            ->get()
            ->mapWithKeys(fn ($s) => [$s->id => $s->name])
            ->toArray();

        return view('admin.kpi-forms.edit-item', compact('template', 'version', 'item', 'criteriaOptions', 'fieldTypes', 'scoringScales'));
    }

    public function showItem(KpiFormItem $item)
    {
        return response()->json([
            'success' => true,
            'item' => $item->only([
                'id', 'label', 'help_text', 'field_type', 'criteria_node_id',
                'min_value', 'max_value', 'is_required', 'sort_order',
            ]),
        ]);
    }

    public function updateItem(Request $request, KpiFormItem $item)
    {
        if ($item->section->version->status === 'published') {
            return back()->with('error', 'Form yang sudah dipublish tidak dapat diubah.');
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'help_text' => ['nullable', 'string'],
            'field_type' => ['required', 'string', 'in:numeric,dropdown,radio,yesno,textarea'],
            'criteria_node_id' => ['nullable', 'exists:criteria_nodes,id'],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => ['nullable', 'numeric'],
            'scoring_scale_id' => ['nullable', 'exists:scoring_scales,id'],
            'default_value' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_required' => ['nullable'],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'string'],
            'options.*.label' => ['nullable', 'string', 'max:255'],
            'options.*.value' => ['nullable', 'string', 'max:255'],
            'options.*.score_value' => ['nullable', 'numeric'],
        ]);

        // Custom validation for unique option values within the same item
        if (in_array($validated['field_type'], ['dropdown', 'radio']) && !empty($validated['options'])) {
            $optionValues = [];
            foreach ($validated['options'] as $index => $optionData) {
                if (!empty($optionData['value'])) {
                    if (in_array($optionData['value'], $optionValues)) {
                        return back()->withErrors([
                            "options.{$index}.value" => 'Nilai opsi harus unik dalam satu item.'
                        ])->withInput();
                    }
                    $optionValues[] = $optionData['value'];
                }
            }
        }

        $item->update([
            'label' => $validated['label'],
            'help_text' => $validated['help_text'] ?? null,
            'field_type' => $validated['field_type'],
            'criteria_node_id' => $validated['criteria_node_id'] ?? null,
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
            'scoring_scale_id' => $validated['scoring_scale_id'] ?? null,
            'default_value' => $validated['default_value'] ?? null,
            'sort_order' => $validated['sort_order'] ?? $item->sort_order,
            'is_required' => isset($validated['is_required']),
        ]);

        // Handle options for dropdown/radio field types
        if (in_array($validated['field_type'], ['dropdown', 'radio'])) {
            $submittedOptionIds = [];
            $sortOrder = 0;

            if (!empty($validated['options'])) {
                foreach ($validated['options'] as $optionData) {
                    if (!empty($optionData['label']) && !empty($optionData['value'])) {
                        if (!empty($optionData['id'])) {
                            // Update existing option
                            $option = KpiFormItemOption::find($optionData['id']);
                            if ($option && $option->item_id === $item->id) {
                                $option->update([
                                    'label' => $optionData['label'],
                                    'value' => $optionData['value'],
                                    'score_value' => $optionData['score_value'] ?? null,
                                    'sort_order' => $sortOrder++,
                                ]);
                                $submittedOptionIds[] = $option->id;
                            }
                        } else {
                            // Create new option
                            $newOption = KpiFormItemOption::create([
                                'id' => Str::ulid(),
                                'item_id' => $item->id,
                                'label' => $optionData['label'],
                                'value' => $optionData['value'],
                                'score_value' => $optionData['score_value'] ?? null,
                                'sort_order' => $sortOrder++,
                            ]);
                            $submittedOptionIds[] = $newOption->id;
                        }
                    }
                }
            }

            // Delete options that were removed
            $item->options()->whereNotIn('id', $submittedOptionIds)->delete();
        } else {
            // Remove all options if field type is not dropdown/radio
            $item->options()->delete();
        }

        return redirect()
            ->route('admin.kpi-forms.builder', $item->section->version->template)
            ->with('success', 'Item berhasil diperbarui.');
    }

    public function deleteItem(KpiFormItem $item)
    {
        if ($item->section->version->status === 'published') {
            return back()->with('error', 'Form yang sudah dipublish tidak dapat diubah.');
        }

        $item->options()->delete();
        $item->delete();

        return back()->with('success', 'Item berhasil dihapus.');
    }

    public function deleteVersion(KpiFormTemplate $template, KpiFormVersion $version)
    {
        if ($version->status === 'published') {
            return back()->with('error', 'Versi yang sudah dipublish tidak dapat dihapus.');
        }

        // Delete all related data
        foreach ($version->sections as $section) {
            foreach ($section->items as $item) {
                $item->options()->delete();
            }
            $section->items()->delete();
        }
        $version->sections()->delete();
        $version->delete();

        return back()->with('success', 'Versi berhasil dihapus.');
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
