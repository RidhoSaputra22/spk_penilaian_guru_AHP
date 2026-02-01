<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\KpiFormTemplate;
use App\Models\ScoringScale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\KpiFormTemplate>
 */
class KpiFormTemplateFactory extends Factory
{
    protected $model = KpiFormTemplate::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => 'Form KPI ' . $this->faker->unique()->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'default_scoring_scale_id' => ScoringScale::factory(),
            'created_by' => User::factory(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
