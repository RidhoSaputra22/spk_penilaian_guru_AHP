<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_form_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('institution_id')
                ->constrained('institutions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignUlid('default_scoring_scale_id')
                ->nullable()
                ->constrained('scoring_scales')
                ->nullOnDelete();

            $table->foreignUlid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'name']);
        });

        Schema::create('kpi_form_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('template_id')
                ->constrained('kpi_form_templates')
                ->cascadeOnDelete();

            $table->unsignedInteger('version')->default(1);
            $table->string('status', 30)->default('draft'); // draft|published|archived
            $table->timestamp('published_at')->nullable();
            $table->timestamp('locked_at')->nullable(); // lock once scoring starts / admin locks

            $table->foreignUlid('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['template_id', 'version']);
            $table->index(['template_id', 'status']);
        });

        Schema::create('kpi_form_sections', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('form_version_id')
                ->constrained('kpi_form_versions')
                ->cascadeOnDelete();

            $table->foreignUlid('criteria_node_id')
                ->nullable()
                ->constrained('criteria_nodes')
                ->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['form_version_id', 'sort_order']);
        });

        Schema::create('kpi_form_items', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('section_id')
                ->constrained('kpi_form_sections')
                ->cascadeOnDelete();

            $table->foreignUlid('criteria_node_id')
                ->nullable()
                ->constrained('criteria_nodes')
                ->nullOnDelete();

            $table->string('label');
            $table->text('help_text')->nullable();

            $table->string('field_type', 30); // numeric|dropdown|boolean|text|textarea|file|url (scalable)
            $table->boolean('is_required')->default(false);

            $table->decimal('min_value', 12, 4)->nullable();
            $table->decimal('max_value', 12, 4)->nullable();

            $table->foreignUlid('scoring_scale_id')
                ->nullable()
                ->constrained('scoring_scales')
                ->nullOnDelete();

            $table->string('default_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['section_id', 'sort_order']);
            $table->index(['field_type']);
        });

        Schema::create('kpi_form_item_options', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('item_id')
                ->constrained('kpi_form_items')
                ->cascadeOnDelete();

            $table->string('value');
            $table->string('label');
            $table->decimal('score_value', 12, 4)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->unique(['item_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_form_item_options');
        Schema::dropIfExists('kpi_form_items');
        Schema::dropIfExists('kpi_form_sections');
        Schema::dropIfExists('kpi_form_versions');
        Schema::dropIfExists('kpi_form_templates');
    }
};
