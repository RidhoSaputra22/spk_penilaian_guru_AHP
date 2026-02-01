<?php

namespace Database\Factories;

use App\Models\CriteriaNode;
use App\Models\CriteriaSet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CriteriaNode>
 */
class CriteriaNodeFactory extends Factory
{
    protected $model = CriteriaNode::class;

    public function definition(): array
    {
        return [
            'criteria_set_id' => CriteriaSet::factory(),
            'parent_id' => null,
            'node_type' => $this->faker->randomElement(['goal', 'criteria', 'subcriteria', 'indicator']),
            'code' => $this->faker->optional()->bothify('C##'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 20),
            'is_active' => true,
            'meta' => ['seed' => 'factory'],
        ];
    }

    public function criteria(): static
    {
        return $this->state(fn () => ['node_type' => 'criteria']);
    }

    public function subcriteria(): static
    {
        return $this->state(fn () => ['node_type' => 'subcriteria']);
    }

    public function indicator(): static
    {
        return $this->state(fn () => ['node_type' => 'indicator']);
    }
}
