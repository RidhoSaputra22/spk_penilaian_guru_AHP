<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use App\Models\ScoringScale;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CriteriaController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $criteriaSets = CriteriaSet::with(['nodes' => function ($q) {
            $q->orderBy('sort_order');
        }])
            ->where('institution_id', $institution?->id)
            ->get();

        $scoringScales = ScoringScale::with('options')
            ->where('institution_id', $institution?->id)
            ->get();

        $currentSet = $request->filled('set')
            ? $criteriaSets->firstWhere('id', $request->set)
            : $criteriaSets->first();

        $criteriaNodes = collect();
        $goalNode = null;
        if ($currentSet) {
            $criteriaNodes = $currentSet->nodes()
                ->where('node_type', '!=', 'goal')
                ->orderBy('sort_order')
                ->get();
            $goalNode = $currentSet->nodes()
                ->where('node_type', 'goal')
                ->first();
        }

        return view('admin.criteria.index', compact('criteriaSets', 'currentSet', 'criteriaNodes', 'scoringScales', 'goalNode'));
    }

    public function create()
    {
        $institution = auth()->user()->institution;

        $scoringScales = ScoringScale::with('options')
            ->where('institution_id', $institution?->id)
            ->get();

        return view('admin.criteria.create', compact('scoringScales'));
    }

    public function add(Request $request)
    {
        $institution = auth()->user()->institution;

        // Get scoring scales
        $scoringScales = ScoringScale::with('options')
            ->where('institution_id', $institution?->id)
            ->get();

        // Get current criteria set
        $currentSet = null;
        if ($request->filled('set')) {
            $currentSet = CriteriaSet::where('id', $request->set)
                ->where('institution_id', $institution?->id)
                ->first();
        }

        // Get parent node if adding sub-criteria
        $parentNode = null;
        if ($request->filled('parent')) {
            $parentNode = CriteriaNode::where('id', $request->parent)
                ->whereHas('set', function ($q) use ($institution) {
                    $q->where('institution_id', $institution?->id);
                })
                ->first();
        }

        // validate currentSet isLocked
        if ($currentSet && $currentSet->locked_at) {
            return redirect()->route('admin.criteria.index', ['set' => $currentSet->id])
                ->with('error', 'Set kriteria yang dipilih telah dikunci dan tidak dapat diubah.');
        }

        // dd($parentNode);

        return view('admin.criteria.add', compact('scoringScales', 'currentSet', 'parentNode'));
    }

    /**
     * Generate a code prefix from a criteria set name.
     * Takes the first 3 alphabetic uppercase characters.
     * e.g. "Kriteria Penilaian Guru" => "KRI", "Arpeggio" => "ARP"
     */
    protected function generateCodePrefix(string $name): string
    {
        $clean = preg_replace('/[^a-zA-Z]/', '', $name);
        $prefix = strtoupper(substr($clean, 0, 3));

        // Fallback if name has fewer than 3 letters
        return str_pad($prefix, 3, 'X');
    }

    public function storeSet(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'criteria' => ['nullable', 'array'],
            'criteria.*.name' => ['required_with:criteria', 'string', 'max:255'],
            'criteria.*.description' => ['nullable', 'string'],
            'criteria.*.sub_criteria' => ['nullable', 'array'],
            'criteria.*.sub_criteria.*.name' => ['nullable', 'string', 'max:255'],
        ]);

        $criteriaSet = CriteriaSet::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ]);

        // Generate code prefix from set name (e.g. "Kriteria Penilaian" => "KRI")
        $prefix = $this->generateCodePrefix($criteriaSet->name);

        // Auto-create Goal node for AHP hierarchy
        $goalNode = CriteriaNode::create([
            'id' => Str::ulid(),
            'criteria_set_id' => $criteriaSet->id,
            'parent_id' => null,
            'node_type' => 'goal',
            'code' => $prefix,
            'name' => $criteriaSet->name,
            'description' => 'Goal node untuk ' . $criteriaSet->name,
            'sort_order' => 0,
            'is_active' => true,
        ]);

        // Create criteria and sub-criteria nodes from form data
        if (! empty($validated['criteria'])) {
            $criteriaSortOrder = 1;

            foreach ($validated['criteria'] as $criteriaData) {
                if (empty($criteriaData['name'])) {
                    continue;
                }

                $criteriaCode = $prefix . '-' . $criteriaSortOrder;

                $criteriaNode = CriteriaNode::create([
                    'id' => Str::ulid(),
                    'criteria_set_id' => $criteriaSet->id,
                    'parent_id' => $goalNode->id,
                    'node_type' => 'criteria',
                    'code' => $criteriaCode,
                    'name' => $criteriaData['name'],
                    'description' => $criteriaData['description'] ?? null,
                    'sort_order' => $criteriaSortOrder++,
                    'is_active' => true,
                ]);

                // Create sub-criteria
                if (! empty($criteriaData['sub_criteria'])) {
                    $subSortOrder = 1;

                    foreach ($criteriaData['sub_criteria'] as $subData) {
                        if (empty($subData['name'])) {
                            continue;
                        }

                        $subCode = $criteriaCode . '.' . $subSortOrder;

                        CriteriaNode::create([
                            'id' => Str::ulid(),
                            'criteria_set_id' => $criteriaSet->id,
                            'parent_id' => $criteriaNode->id,
                            'node_type' => 'subcriteria',
                            'code' => $subCode,
                            'name' => $subData['name'],
                            'sort_order' => $subSortOrder++,
                            'is_active' => true,
                        ]);
                    }
                }
            }
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_criteria_set',
            'entity_type' => CriteriaSet::class,
            'entity_id' => $criteriaSet->id,
            'description' => "Created criteria set: {$criteriaSet->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.criteria.index', ['set' => $criteriaSet->id])
            ->with('success', 'Set kriteria berhasil dibuat.');
    }

    public function updateSet(Request $request, CriteriaSet $criteriaSet)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $criteriaSet->update($validated);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_criteria_set',
            'entity_type' => CriteriaSet::class,
            'entity_id' => $criteriaSet->id,
            'description' => "Updated criteria set: {$criteriaSet->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.criteria.index', ['set' => $criteriaSet->id])
            ->with('success', 'Set kriteria berhasil diperbarui.');
    }

    public function editSet(CriteriaSet $criteriaSet)
    {
        $institution = auth()->user()->institution;

        $scoringScales = ScoringScale::with('options')
            ->where('institution_id', $institution?->id)
            ->get();

        return view('admin.criteria.edit-set', compact('criteriaSet', 'scoringScales'));
    }

    public function lockSet(CriteriaSet $criteriaSet)
    {
        $criteriaSet->update([
            'locked_at' => now(),
            'locked_by' => auth()->id(),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'lock_criteria_set',
            'entity_type' => CriteriaSet::class,
            'entity_id' => $criteriaSet->id,
            'description' => "Locked criteria set: {$criteriaSet->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', 'Set kriteria berhasil dikunci.');
    }

    public function destroySet(CriteriaSet $criteriaSet)
    {
        $setName = $criteriaSet->name;
        $criteriaSet->delete();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'delete_criteria_set',
            'entity_type' => CriteriaSet::class,
            'entity_id' => $criteriaSet->id,
            'description' => "Deleted criteria set: {$setName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.criteria.index')
            ->with('success', 'Set kriteria berhasil dihapus.');
    }

    public function storeNode(Request $request)
    {
        $validated = $request->validate([
            'criteria_set_id' => ['required', 'exists:criteria_sets,id'],
            'parent_id' => ['nullable', 'exists:criteria_nodes,id'],
            'node_type' => ['nullable', 'string', 'in:criteria,subcriteria,indicator'],
            'code' => ['nullable', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scoring_scale_id' => ['nullable', 'exists:scoring_scales,id'],
        ]);

        // Get the criteria set for prefix generation
        $criteriaSetForCode = CriteriaSet::findOrFail($validated['criteria_set_id']);
        $prefix = $this->generateCodePrefix($criteriaSetForCode->name);

        // Get parent_id - if null, auto-assign to Goal node
        $parentId = $validated['parent_id'] ?? null;

        if (! $parentId) {
            // Find or create Goal node for this criteria set
            $goalNode = CriteriaNode::where('criteria_set_id', $validated['criteria_set_id'])
                ->where('node_type', 'goal')
                ->first();

            if (! $goalNode) {
                $goalNode = CriteriaNode::create([
                    'id' => Str::ulid(),
                    'criteria_set_id' => $validated['criteria_set_id'],
                    'parent_id' => null,
                    'node_type' => 'goal',
                    'code' => $prefix,
                    'name' => $criteriaSetForCode->name,
                    'description' => 'Goal node untuk ' . $criteriaSetForCode->name,
                    'sort_order' => 0,
                    'is_active' => true,
                ]);
            }

            $parentId = $goalNode->id;
        }

        // Auto-determine node_type based on parent hierarchy
        $parentNode = CriteriaNode::find($parentId);
        if ($parentNode && $parentNode->node_type === 'goal') {
            $nodeType = 'criteria';
        } elseif ($parentNode && $parentNode->node_type === 'criteria') {
            $nodeType = 'subcriteria';
        } else {
            $nodeType = $validated['node_type'] ?? 'indicator';
        }

        // Get max sort order (used for both ordering and auto-code)
        $maxSort = CriteriaNode::where('criteria_set_id', $validated['criteria_set_id'])
            ->where('parent_id', $parentId)
            ->max('sort_order') ?? 0;

        $nextNumber = $maxSort + 1;

        // Auto-generate code if not provided
        if (empty($validated['code'])) {
            if ($nodeType === 'criteria') {
                $autoCode = $prefix . '-' . $nextNumber;
            } elseif ($nodeType === 'subcriteria' && $parentNode) {
                $autoCode = $parentNode->code . '.' . $nextNumber;
            } else {
                $autoCode = $prefix . '-' . $nextNumber;
            }
        } else {
            $autoCode = $validated['code'];
        }

        $node = CriteriaNode::create([
            'id' => Str::ulid(),
            'criteria_set_id' => $validated['criteria_set_id'],
            'parent_id' => $parentId,
            'node_type' => $nodeType,
            'code' => $autoCode,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'scoring_scale_id' => $validated['scoring_scale_id'] ?? null,
            'sort_order' => $nextNumber,
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_criteria_node',
            'entity_type' => CriteriaNode::class,
            'entity_id' => $node->id,
            'description' => "Created criteria node: {$node->code} - {$node->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.criteria.index', ['set' => $validated['criteria_set_id']])
            ->with('success', 'Kriteria berhasil ditambahkan.');
    }

    public function updateNode(Request $request, CriteriaNode $node)
    {
        // Simplified validation to avoid unique conflicts
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scoring_scale_id' => ['nullable'],
        ]);

        $node->update($validated);

        return back()->with('success', 'Kriteria berhasil diperbarui.');
    }

    public function editNode(CriteriaNode $node)
    {
        return view('admin.criteria.edit', compact('node'));
    }

    public function destroyNode(CriteriaNode $node)
    {
        // Check if has children

        // dd($node->children()->exists());

        // destroy all children
        if ($node->children()->exists()) {

            // write to activity log for each child deleted
            foreach ($node->children as $child) {
                ActivityLog::create([
                    'id' => Str::ulid(),
                    'user_id' => auth()->id(),
                    'action' => 'delete_criteria_node',
                    'entity_type' => CriteriaNode::class,
                    'entity_id' => $child->id,
                    'description' => "Deleted criteria node: {$child->code} - {$child->name}",
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }

            $node->children()->delete();

        }

        $nodeName = "{$node->code} - {$node->name}";
        $criteriaSetId = $node->criteria_set_id;
        $node->delete();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'delete_criteria_node',
            'entity_type' => CriteriaNode::class,
            'entity_id' => $node->id,
            'description' => "Deleted criteria node: {$nodeName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.criteria.index', ['set' => $criteriaSetId])
            ->with('success', 'Kriteria berhasil dihapus.');
    }

    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'nodes' => ['required', 'array'],
            'nodes.*.id' => ['required', 'exists:criteria_nodes,id'],
            'nodes.*.sort_order' => ['required', 'integer', 'min:0'],
            'nodes.*.parent_id' => ['nullable', 'exists:criteria_nodes,id'],
        ]);

        foreach ($validated['nodes'] as $nodeData) {
            $node = CriteriaNode::find($nodeData['id']);

            // Calculate new level based on parent
            $level = 0;
            if ($nodeData['parent_id']) {
                $parent = CriteriaNode::find($nodeData['parent_id']);
                $level = $parent->level + 1;
            }

            $node->update([
                'sort_order' => $nodeData['sort_order'],
                'parent_id' => $nodeData['parent_id'],
                'level' => $level,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
