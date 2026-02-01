<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scoring_scales', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('institution_id')
                ->constrained('institutions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->string('scale_type', 30)->default('numeric'); // numeric|categorical
            $table->decimal('min_value', 12, 4)->nullable();
            $table->decimal('max_value', 12, 4)->nullable();
            $table->decimal('step', 12, 4)->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['institution_id', 'scale_type']);
        });

        Schema::create('scoring_scale_options', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('scoring_scale_id')
                ->constrained('scoring_scales')
                ->cascadeOnDelete();

            $table->string('value'); // stored value from UI (e.g. "A" / "yes" / "3")
            $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('score_value', 12, 4)->nullable(); // optional numeric equivalent
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['scoring_scale_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scoring_scale_options');
        Schema::dropIfExists('scoring_scales');
    }
};
