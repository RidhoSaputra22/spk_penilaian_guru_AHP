<?php

namespace Database\Factories;

use App\Models\CriteriaNode;
use App\Models\TeacherCriteriaScore;
use App\Models\TeacherPeriodResult;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TeacherCriteriaScore>
 */
class TeacherCriteriaScoreFactory extends Factory
{
    protected $model = TeacherCriteriaScore::class;

    public function definition(): array
    {
        return [
            'teacher_period_result_id' => TeacherPeriodResult::factory(),
            'criteria_node_id' => CriteriaNode::factory(),
            'raw_score' => $this->faker->randomFloat(4, 1, 4),
            'weight' => $this->faker->randomFloat(12, 0.01, 1),
            'weighted_score' => $this->faker->randomFloat(4, 0.1, 100),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
