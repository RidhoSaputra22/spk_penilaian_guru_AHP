<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('period_results', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_period_id')
                ->constrained('assessment_periods')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('generated'); // generated|published|archived
            $table->timestamp('generated_at')->nullable();
            $table->timestamp('published_at')->nullable();

            $table->foreignUlid('generated_by')->nullable()->constrained('users')->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['assessment_period_id'], 'period_results_one_per_period');
        });

        Schema::create('teacher_period_results', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('period_result_id')
                ->constrained('period_results')
                ->cascadeOnDelete();

            $table->foreignUlid('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();

            $table->decimal('final_score', 14, 4)->default(0);
            $table->unsignedInteger('rank')->nullable();

            // Denormalized details for quick reporting (optional)
            $table->json('details')->nullable();

            $table->timestamps();

            $table->unique(['period_result_id', 'teacher_profile_id'], 'uniq_tpr_period_teacher');

            $table->index(['period_result_id', 'rank']);
        });

        Schema::create('teacher_criteria_scores', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('teacher_period_result_id')
                ->constrained('teacher_period_results')
                ->cascadeOnDelete();

            $table->foreignUlid('criteria_node_id')
                ->constrained('criteria_nodes')
                ->cascadeOnDelete();

            $table->decimal('raw_score', 14, 4)->nullable();
            $table->decimal('weight', 16, 12)->nullable();
            $table->decimal('weighted_score', 14, 4)->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['teacher_period_result_id', 'criteria_node_id'], 'teacher_criteria_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_criteria_scores');
        Schema::dropIfExists('teacher_period_results');
        Schema::dropIfExists('period_results');
    }
};
