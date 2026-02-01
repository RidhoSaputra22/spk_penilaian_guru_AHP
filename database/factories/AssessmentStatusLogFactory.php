<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AssessmentStatusLog>
 */
class AssessmentStatusLogFactory extends Factory
{
    protected $model = AssessmentStatusLog::class;

    public function definition(): array
    {
        $to = $this->faker->randomElement(['draft', 'finalized', 'reopened']);

        return [
            'assessment_id' => Assessment::factory(),
            'from_status' => null,
            'to_status' => $to,
            'changed_by' => User::factory(),
            'reason' => $to === 'reopened' ? $this->faker->sentence() : null,
            'created_at' => now(),
        ];
    }
}
