<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criteria_sets', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('institution_id')
                ->constrained('institutions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->unsignedInteger('version')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('locked_at')->nullable();

            $table->foreignUlid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['institution_id', 'name', 'version']);
        });

        Schema::create('criteria_nodes', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('criteria_set_id')
                ->constrained('criteria_sets')
                ->cascadeOnDelete();

            $table->foreignUlid('parent_id')
                ->nullable()
                ->constrained('criteria_nodes')
                ->nullOnDelete();

            $table->string('node_type', 30)->default('criteria'); // goal|criteria|subcriteria|indicator (scalable)
            $table->string('code', 50)->nullable();
            $table->string('name');
            $table->text('description')->nullable();

            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['criteria_set_id', 'node_type']);
            $table->index(['criteria_set_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criteria_nodes');
        Schema::dropIfExists('criteria_sets');
    }
};
