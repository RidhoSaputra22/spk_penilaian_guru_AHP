<?php

namespace Database\Factories;

use App\Models\AhpComparison;
use App\Models\AhpModel;
use App\Models\CriteriaNode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AhpComparison>
 */
class AhpComparisonFactory extends Factory
{
    protected $model = AhpComparison::class;

    public function definition(): array
    {
        return [
            'ahp_model_id' => AhpModel::factory(),
            'parent_node_id' => null,
            'node_a_id' => CriteriaNode::factory(),
            'node_b_id' => CriteriaNode::factory(),
            'value' => $this->faker->randomFloat(3, 0.111, 9.000),
            'created_by' => User::factory(),
        ];
    }
}
