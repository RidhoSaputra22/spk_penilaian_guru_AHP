<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kpi_form_templates', function (Blueprint $table) {
            $table->foreignUlid('criteria_set_id')
                ->nullable()
                ->after('description')
                ->constrained('criteria_sets')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kpi_form_templates', function (Blueprint $table) {
            $table->dropForeign(['criteria_set_id']);
            $table->dropColumn('criteria_set_id');
        });
    }
};
