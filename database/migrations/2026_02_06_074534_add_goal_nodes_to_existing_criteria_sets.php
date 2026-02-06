<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Add Goal nodes to criteria sets that don't have one,
     * and re-parent orphan top-level criteria to the Goal node.
     */
    public function up(): void
    {
        // Get all criteria sets
        $sets = DB::table('criteria_sets')->get();

        foreach ($sets as $set) {
            // Check if this set already has a goal node
            $hasGoal = DB::table('criteria_nodes')
                ->where('criteria_set_id', $set->id)
                ->where('node_type', 'goal')
                ->exists();

            if ($hasGoal) {
                // Already has goal - make sure all top-level criteria with parent_id=null
                // (that are not the goal) are re-parented to the goal
                $goalNode = DB::table('criteria_nodes')
                    ->where('criteria_set_id', $set->id)
                    ->where('node_type', 'goal')
                    ->first();

                DB::table('criteria_nodes')
                    ->where('criteria_set_id', $set->id)
                    ->whereNull('parent_id')
                    ->where('node_type', '!=', 'goal')
                    ->update([
                        'parent_id' => $goalNode->id,
                        'node_type' => 'criteria',
                    ]);

                continue;
            }

            // Create a Goal node for this set
            $goalId = (string) Str::ulid();

            DB::table('criteria_nodes')->insert([
                'id' => $goalId,
                'criteria_set_id' => $set->id,
                'parent_id' => null,
                'node_type' => 'goal',
                'code' => 'G1',
                'name' => $set->name,
                'description' => 'Goal node untuk ' . $set->name,
                'sort_order' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Re-parent all top-level nodes (parent_id = null) to the new Goal node
            // These should become 'criteria' type nodes
            DB::table('criteria_nodes')
                ->where('criteria_set_id', $set->id)
                ->whereNull('parent_id')
                ->where('id', '!=', $goalId)
                ->update([
                    'parent_id' => $goalId,
                    'node_type' => 'criteria',
                ]);

            // Also fix nodes that are children of criteria but marked as 'criteria'
            // They should be 'subcriteria'
            $criteriaIds = DB::table('criteria_nodes')
                ->where('criteria_set_id', $set->id)
                ->where('parent_id', $goalId)
                ->pluck('id');

            if ($criteriaIds->isNotEmpty()) {
                DB::table('criteria_nodes')
                    ->where('criteria_set_id', $set->id)
                    ->whereIn('parent_id', $criteriaIds)
                    ->where('node_type', 'criteria')
                    ->update(['node_type' => 'subcriteria']);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove goal nodes and set their children's parent_id to null
        $goalNodes = DB::table('criteria_nodes')
            ->where('node_type', 'goal')
            ->get();

        foreach ($goalNodes as $goal) {
            // Re-orphan the children
            DB::table('criteria_nodes')
                ->where('parent_id', $goal->id)
                ->update(['parent_id' => null]);

            // Delete the goal node
            DB::table('criteria_nodes')
                ->where('id', $goal->id)
                ->delete();
        }
    }
};
