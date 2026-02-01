<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_periods', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('institution_id')
                ->constrained('institutions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name'); // e.g. "Semester Ganjil 2025/2026"
            $table->string('academic_year', 20)->nullable(); // "2025/2026"
            $table->string('semester', 20)->nullable(); // "ganjil|genap" or "1|2"

            $table->timestamp('scoring_open_at')->nullable();
            $table->timestamp('scoring_close_at')->nullable();
            $table->string('status', 30)->default('draft'); // draft|open|closed|archived

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['institution_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_periods');
    }
};
