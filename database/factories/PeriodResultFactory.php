<?php

namespace Database\Factories;

use App\Models\AssessmentPeriod;
use App\Models\PeriodResult;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PeriodResult>
 */
class PeriodResultFactory extends Factory
{
    protected $model = PeriodResult::class;

    public function definition(): array
    {
        return [
            'assessment_period_id' => AssessmentPeriod::factory(),
            'status' => 'generated',
            'generated_at' => now(),
            'published_at' => null,
            'generated_by' => User::factory(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
