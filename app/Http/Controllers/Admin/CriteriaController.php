<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CriteriaSet;
use App\Models\CriteriaNode;
use App\Models\ScoringScale;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CriteriaController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $criteriaSets = CriteriaSet::with(['nodes' => function($q) {
            $q->orderBy('sort_order');
        }])
        ->where('institution_id', $institution?->id)
        ->get();

        $scoringScales = ScoringScale::with('options')
            ->where('institution_id', $institution?->id)
            ->get();

        $activeCriteriaSet = $request->filled('set')
            ? $criteriaSets->firstWhere('id', $request->set)
            : $criteriaSets->first();

        return view('admin.criteria.index', compact('criteriaSets', 'activeCriteriaSet', 'scoringScales'));
    }

    public function storeSet(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $criteriaSet = CriteriaSet::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            ...$validated,
        ]);

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
            'code' => ['required', 'string', 'max:20'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scoring_scale_id' => ['nullable', 'exists:scoring_scales,id'],
        ]);

        // Get parent_id (may be null)
        $parentId = $validated['parent_id'] ?? null;

        // Get max sort order
        $maxSort = CriteriaNode::where('criteria_set_id', $validated['criteria_set_id'])
            ->where('parent_id', $parentId)
            ->max('sort_order') ?? 0;

        $node = CriteriaNode::create([
            'id' => Str::ulid(),
            'criteria_set_id' => $validated['criteria_set_id'],
            'parent_id' => $parentId,
            'code' => $validated['code'],
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'scoring_scale_id' => $validated['scoring_scale_id'] ?? null,
            'sort_order' => $maxSort + 1,
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

        return back()->with('success', 'Kriteria berhasil ditambahkan.');
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

    public function destroyNode(CriteriaNode $node)
    {
        // Check if has children
        if ($node->children()->exists()) {
            return back()->with('error', 'Tidak dapat menghapus kriteria yang memiliki sub-kriteria.');
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
