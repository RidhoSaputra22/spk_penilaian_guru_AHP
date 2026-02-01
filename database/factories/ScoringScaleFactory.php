<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\ScoringScale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ScoringScale>
 */
class ScoringScaleFactory extends Factory
{
    protected $model = ScoringScale::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => 'Scale ' . $this->faker->unique()->bothify('##'),
            'scale_type' => 'numeric',
            'min_value' => 1,
            'max_value' => 4,
            'step' => 1,
            'meta' => ['seed' => 'factory'],
        ];
    }
}
