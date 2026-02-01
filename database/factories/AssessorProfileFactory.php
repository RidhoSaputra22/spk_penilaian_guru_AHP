<?php

namespace Database\Factories;

use App\Models\AssessorProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AssessorProfile>
 */
class AssessorProfileFactory extends Factory
{
    protected $model = AssessorProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->randomElement(['Kepala Madrasah', 'Waka Kurikulum', 'Pengawas', 'Kepala TU']),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
