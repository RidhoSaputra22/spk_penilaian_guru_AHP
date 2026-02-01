<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ahp_models', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_period_id')
                ->constrained('assessment_periods')
                ->cascadeOnDelete();

            $table->foreignUlid('criteria_set_id')
                ->constrained('criteria_sets')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('draft'); // draft|finalized
            $table->decimal('consistency_ratio', 12, 8)->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->foreignUlid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['assessment_period_id'], 'ahp_models_one_per_period');
            $table->index(['criteria_set_id', 'status']);
        });

        Schema::create('ahp_comparisons', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('ahp_model_id')
                ->constrained('ahp_models')
                ->cascadeOnDelete();

            // For comparing nodes under a parent (e.g., sub-criteria under a criterion).
            $table->foreignUlid('parent_node_id')
                ->nullable()
                ->constrained('criteria_nodes')
                ->nullOnDelete();

            $table->foreignUlid('node_a_id')
                ->constrained('criteria_nodes')
                ->cascadeOnDelete();

            $table->foreignUlid('node_b_id')
                ->constrained('criteria_nodes')
                ->cascadeOnDelete();

            $table->decimal('value', 14, 6); // Saaty scale (e.g., 1..9 plus fractions)
            $table->foreignUlid('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['ahp_model_id', 'parent_node_id', 'node_a_id', 'node_b_id'], 'ahp_unique_pair');
        });

        Schema::create('ahp_weights', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('ahp_model_id')
                ->constrained('ahp_models')
                ->cascadeOnDelete();

            $table->foreignUlid('criteria_node_id')
                ->constrained('criteria_nodes')
                ->cascadeOnDelete();

            $table->foreignUlid('parent_node_id')
                ->nullable()
                ->constrained('criteria_nodes')
                ->nullOnDelete();

            $table->string('level', 30)->default('criteria'); // criteria|subcriteria|indicator
            $table->decimal('weight', 16, 12);

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['ahp_model_id', 'criteria_node_id']);
            $table->index(['ahp_model_id', 'level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ahp_weights');
        Schema::dropIfExists('ahp_comparisons');
        Schema::dropIfExists('ahp_models');
    }
};
