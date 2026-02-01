<?php

namespace Database\Factories;

use App\Models\CriteriaSet;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\CriteriaSet>
 */
class CriteriaSetFactory extends Factory
{
    protected $model = CriteriaSet::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => 'Criteria Set ' . $this->faker->unique()->bothify('##'),
            'version' => 1,
            'is_active' => true,
            'locked_at' => null,
            'created_by' => User::factory(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
