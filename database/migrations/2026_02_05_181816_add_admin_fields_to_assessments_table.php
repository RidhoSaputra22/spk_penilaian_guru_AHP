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
        Schema::table('assessments', function (Blueprint $table) {
            $table->foreignUlid('finalized_by')
                ->nullable()
                ->after('finalized_at')
                ->constrained('users')
                ->nullOnDelete();

            $table->text('admin_notes')
                ->nullable()
                ->after('finalized_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            $table->dropForeign(['finalized_by']);
            $table->dropColumn(['finalized_by', 'admin_notes']);
        });
    }
};
