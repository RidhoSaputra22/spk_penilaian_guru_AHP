<?php

namespace Database\Factories;

use App\Models\Institution;
use App\Models\TeacherGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TeacherGroup>
 */
class TeacherGroupFactory extends Factory
{
    protected $model = TeacherGroup::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->optional()->sentence(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
