<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_form_assignments', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_period_id')
                ->constrained('assessment_periods')
                ->cascadeOnDelete();

            $table->foreignUlid('form_version_id')
                ->constrained('kpi_form_versions')
                ->cascadeOnDelete();

            $table->string('status', 30)->default('active'); // active|inactive
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('locked_at')->nullable(); // when published/locked

            $table->foreignUlid('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['assessment_period_id', 'status']);
        });

        Schema::create('kpi_assignment_assessors', function (Blueprint $table) {
            $table->foreignUlid('assignment_id')->constrained('kpi_form_assignments')->cascadeOnDelete();
            $table->foreignUlid('assessor_profile_id')->constrained('assessor_profiles')->cascadeOnDelete();
            $table->primary(['assignment_id', 'assessor_profile_id']);
        });

        Schema::create('kpi_assignment_teacher_groups', function (Blueprint $table) {
            $table->foreignUlid('assignment_id')->constrained('kpi_form_assignments')->cascadeOnDelete();
            $table->foreignUlid('teacher_group_id')->constrained('teacher_groups')->cascadeOnDelete();
            $table->primary(['assignment_id', 'teacher_group_id']);
        });

        Schema::create('kpi_assignment_teachers', function (Blueprint $table) {
            $table->foreignUlid('assignment_id')->constrained('kpi_form_assignments')->cascadeOnDelete();
            $table->foreignUlid('teacher_profile_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->primary(['assignment_id', 'teacher_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_assignment_teachers');
        Schema::dropIfExists('kpi_assignment_teacher_groups');
        Schema::dropIfExists('kpi_assignment_assessors');
        Schema::dropIfExists('kpi_form_assignments');
    }
};
