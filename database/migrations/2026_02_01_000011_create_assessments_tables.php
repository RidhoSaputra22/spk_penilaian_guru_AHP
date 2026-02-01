<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_period_id')
                ->constrained('assessment_periods')
                ->cascadeOnDelete();

            $table->foreignUlid('assignment_id')
                ->nullable()
                ->constrained('kpi_form_assignments')
                ->nullOnDelete();

            $table->foreignUlid('teacher_profile_id')
                ->constrained('teacher_profiles')
                ->cascadeOnDelete();

            $table->foreignUlid('assessor_profile_id')
                ->constrained('assessor_profiles')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('draft'); // draft|finalized|reopened

            $table->timestamp('started_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('finalized_at')->nullable();

            $table->timestamp('reopened_at')->nullable();
            $table->text('reopened_reason')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            // One assessor evaluates one teacher once per period (customize if needed)
            $table->unique(['assessment_period_id', 'teacher_profile_id', 'assessor_profile_id'], 'assessments_unique_triplet');
            $table->index(['assessment_period_id', 'status']);
        });

        Schema::create('assessment_item_values', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_id')
                ->constrained('assessments')
                ->cascadeOnDelete();

            $table->foreignUlid('form_item_id')
                ->constrained('kpi_form_items')
                ->cascadeOnDelete();

            // Flexible value storage:
            $table->decimal('value_number', 14, 4)->nullable();
            $table->text('value_string')->nullable();
            $table->boolean('value_bool')->nullable();

            $table->text('notes')->nullable();

            // Store normalized numeric score used for calculations (if applicable)
            $table->decimal('score_value', 14, 4)->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['assessment_id', 'form_item_id']);
        });

        Schema::create('evidence_uploads', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_item_value_id')
                ->constrained('assessment_item_values')
                ->cascadeOnDelete();

            $table->foreignUlid('uploaded_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->string('url')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['disk']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evidence_uploads');
        Schema::dropIfExists('assessment_item_values');
        Schema::dropIfExists('assessments');
    }
};
