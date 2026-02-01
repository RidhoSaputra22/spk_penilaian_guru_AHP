<?php

namespace Database\Factories;

use App\Models\ScoringScale;
use App\Models\ScoringScaleOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ScoringScaleOption>
 */
class ScoringScaleOptionFactory extends Factory
{
    protected $model = ScoringScaleOption::class;

    public function definition(): array
    {
        $v = $this->faker->numberBetween(1, 4);

        return [
            'scoring_scale_id' => ScoringScale::factory(),
            'value' => (string) $v,
            'label' => (string) $v,
            'description' => null,
            'score_value' => $v,
            'sort_order' => $v,
            'meta' => ['seed' => 'factory'],
        ];
    }
}
