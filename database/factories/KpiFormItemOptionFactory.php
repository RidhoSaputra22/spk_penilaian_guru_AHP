<?php

namespace Database\Factories;

use App\Models\KpiFormItem;
use App\Models\KpiFormItemOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\KpiFormItemOption>
 */
class KpiFormItemOptionFactory extends Factory
{
    protected $model = KpiFormItemOption::class;

    public function definition(): array
    {
        $v = $this->faker->unique()->randomElement(['A', 'B', 'C', 'D', 'E']);

        return [
            'item_id' => KpiFormItem::factory(),
            'value' => $v,
            'label' => 'Option ' . $v,
            'score_value' => $this->faker->optional()->numberBetween(1, 4),
            'sort_order' => $this->faker->numberBetween(0, 10),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
