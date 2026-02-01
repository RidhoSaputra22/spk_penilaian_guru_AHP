<?php

namespace Database\Factories;

use App\Models\AssessmentPeriod;
use App\Models\KpiFormAssignment;
use App\Models\KpiFormVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\KpiFormAssignment>
 */
class KpiFormAssignmentFactory extends Factory
{
    protected $model = KpiFormAssignment::class;

    public function definition(): array
    {
        return [
            'assessment_period_id' => AssessmentPeriod::factory(),
            'form_version_id' => KpiFormVersion::factory(),
            'status' => 'active',
            'assigned_at' => now(),
            'locked_at' => null,
            'assigned_by' => User::factory(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
