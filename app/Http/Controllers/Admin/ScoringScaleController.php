<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\ScoringScale;
use App\Models\ScoringScaleOption;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ScoringScaleController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = ScoringScale::with(['options'])
            ->where('institution_id', $institution?->id)
            ->withCount('options');

        // Search functionality
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%');
            });
        }

        $scoringScales = $query->latest()->paginate(10);

        return view('admin.scoring-scales.index', compact('scoringScales'));
    }

    public function create()
    {
        return view('admin.scoring-scales.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scale_type' => ['required', 'in:numeric,text'],
            'min_value' => ['nullable', 'numeric', 'required_if:scale_type,numeric'],
            'max_value' => ['nullable', 'numeric', 'required_if:scale_type,numeric', 'gt:min_value'],
            'step' => ['nullable', 'numeric', 'required_if:scale_type,numeric'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.value' => ['required'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.description' => ['nullable', 'string'],
        ]);

        $scoringScale = ScoringScale::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'scale_type' => $validated['scale_type'],
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
            'step' => $validated['step'] ?? null,
        ]);

        // Create options
        foreach ($validated['options'] as $index => $optionData) {
            ScoringScaleOption::create([
                'id' => Str::ulid(),
                'scoring_scale_id' => $scoringScale->id,
                'value' => $optionData['value'],
                'label' => $optionData['label'],
                'description' => $optionData['description'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_scoring_scale',
            'entity_type' => ScoringScale::class,
            'entity_id' => $scoringScale->id,
            'description' => "Created scoring scale: {$scoringScale->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.scoring-scales.index')
            ->with('success', 'Skala penilaian berhasil dibuat.');
    }

    public function show(ScoringScale $scoringScale)
    {
        $scoringScale->load('options');

        return view('admin.scoring-scales.show', compact('scoringScale'));
    }

    public function edit(ScoringScale $scoringScale)
    {
        $scoringScale->load('options');

        return view('admin.scoring-scales.edit', compact('scoringScale'));
    }

    public function update(Request $request, ScoringScale $scoringScale)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'scale_type' => ['required', 'in:numeric,text'],
            'min_value' => ['nullable', 'numeric', 'required_if:scale_type,numeric'],
            'max_value' => ['nullable', 'numeric', 'required_if:scale_type,numeric', 'gt:min_value'],
            'step' => ['nullable', 'numeric', 'required_if:scale_type,numeric'],
            'options' => ['required', 'array', 'min:2'],
            'options.*.value' => ['required'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.description' => ['nullable', 'string'],
        ]);

        $scoringScale->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'scale_type' => $validated['scale_type'],
            'min_value' => $validated['min_value'] ?? null,
            'max_value' => $validated['max_value'] ?? null,
            'step' => $validated['step'] ?? null,
        ]);

        // Delete existing options and recreate
        $scoringScale->options()->delete();

        foreach ($validated['options'] as $index => $optionData) {
            ScoringScaleOption::create([
                'id' => Str::ulid(),
                'scoring_scale_id' => $scoringScale->id,
                'value' => $optionData['value'],
                'label' => $optionData['label'],
                'description' => $optionData['description'] ?? null,
                'sort_order' => $index + 1,
            ]);
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_scoring_scale',
            'entity_type' => ScoringScale::class,
            'entity_id' => $scoringScale->id,
            'description' => "Updated scoring scale: {$scoringScale->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.scoring-scales.index')
            ->with('success', 'Skala penilaian berhasil diperbarui.');
    }

    public function destroy(ScoringScale $scoringScale)
    {
        $scaleName = $scoringScale->name;

        // Delete options first
        $scoringScale->options()->delete();
        $scoringScale->delete();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'delete_scoring_scale',
            'entity_type' => ScoringScale::class,
            'entity_id' => $scoringScale->id,
            'description' => "Deleted scoring scale: {$scaleName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.scoring-scales.index')
            ->with('success', 'Skala penilaian berhasil dihapus.');
    }
}
