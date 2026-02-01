<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_groups', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->foreignUlid('institution_id')
                ->constrained('institutions')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->string('name');
            $table->text('description')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['institution_id', 'name']);
        });

        Schema::create('teacher_group_members', function (Blueprint $table) {
            $table->foreignUlid('teacher_group_id')->constrained('teacher_groups')->cascadeOnDelete();
            $table->foreignUlid('teacher_profile_id')->constrained('teacher_profiles')->cascadeOnDelete();
            $table->primary(['teacher_group_id', 'teacher_profile_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_group_members');
        Schema::dropIfExists('teacher_groups');
    }
};
