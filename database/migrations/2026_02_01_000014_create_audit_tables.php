<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('institution_id')
                ->constrained('institutions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignUlid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('action'); // e.g. "form.published", "ahp.finalized", "assessment.reopened"
            $table->string('subject_type')->nullable();
            $table->ulid('subject_id')->nullable();

            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamp('created_at')->useCurrent();

            $table->index(['institution_id', 'action']);
            $table->index(['subject_type', 'subject_id']);
        });

        Schema::create('assessment_status_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('assessment_id')
                ->constrained('assessments')
                ->cascadeOnDelete();

            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);

            $table->foreignUlid('changed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['assessment_id', 'to_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_status_logs');
        Schema::dropIfExists('activity_logs');
    }
};
