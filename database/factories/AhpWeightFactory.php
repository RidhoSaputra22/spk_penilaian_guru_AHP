<?php

namespace Database\Factories;

use App\Models\AhpModel;
use App\Models\AhpWeight;
use App\Models\CriteriaNode;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AhpWeight>
 */
class AhpWeightFactory extends Factory
{
    protected $model = AhpWeight::class;

    public function definition(): array
    {
        return [
            'ahp_model_id' => AhpModel::factory(),
            'criteria_node_id' => CriteriaNode::factory(),
            'parent_node_id' => null,
            'level' => $this->faker->randomElement(['criteria', 'subcriteria', 'indicator']),
            'weight' => $this->faker->randomFloat(12, 0.01, 0.99),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
