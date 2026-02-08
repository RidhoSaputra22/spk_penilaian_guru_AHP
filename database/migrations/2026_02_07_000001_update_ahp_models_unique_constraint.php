<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow multiple AHP models per period (one per criteria_set).
     * Previously the unique constraint only covered assessment_period_id,
     * which blocks having Form 2, 3, 4 each with their own AHP model
     * in the same assessment period.
     */
    public function up(): void
    {
        // MySQL won't let us drop a unique index used by a FK.
        // Strategy: add new composite unique first, then drop the FK,
        // drop the old unique, and re-add the FK.
        Schema::table('ahp_models', function (Blueprint $table) {
            // 1. Add the new composite unique
            $table->unique(
                ['assessment_period_id', 'criteria_set_id'],
                'ahp_models_one_per_period_set'
            );
        });

        Schema::table('ahp_models', function (Blueprint $table) {
            // 2. Drop the FK that depends on the old unique index
            $table->dropForeign(['assessment_period_id']);
        });

        Schema::table('ahp_models', function (Blueprint $table) {
            // 3. Now we can safely drop the old unique
            $table->dropUnique('ahp_models_one_per_period');
        });

        Schema::table('ahp_models', function (Blueprint $table) {
            // 4. Re-add the FK (it will use the new composite unique)
            $table->foreign('assessment_period_id')
                ->references('id')
                ->on('assessment_periods')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ahp_models', function (Blueprint $table) {
            $table->dropForeign(['assessment_period_id']);
        });

        Schema::table('ahp_models', function (Blueprint $table) {
            $table->dropUnique('ahp_models_one_per_period_set');
            $table->unique(['assessment_period_id'], 'ahp_models_one_per_period');
        });

        Schema::table('ahp_models', function (Blueprint $table) {
            $table->foreign('assessment_period_id')
                ->references('id')
                ->on('assessment_periods')
                ->cascadeOnDelete();
        });
    }
};
