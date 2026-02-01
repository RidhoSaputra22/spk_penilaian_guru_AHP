<?php

namespace Database\Factories;

use App\Models\AhpModel;
use App\Models\AssessmentPeriod;
use App\Models\CriteriaSet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AhpModel>
 */
class AhpModelFactory extends Factory
{
    protected $model = AhpModel::class;

    public function definition(): array
    {
        return [
            'assessment_period_id' => AssessmentPeriod::factory(),
            'criteria_set_id' => CriteriaSet::factory(),
            'status' => 'draft',
            'consistency_ratio' => null,
            'finalized_at' => null,
            'created_by' => User::factory(),
            'meta' => ['seed' => 'factory'],
        ];
    }

    public function finalized(): static
    {
        return $this->state(fn () => [
            'status' => 'finalized',
            'consistency_ratio' => 0.05,
            'finalized_at' => now(),
        ]);
    }
}
