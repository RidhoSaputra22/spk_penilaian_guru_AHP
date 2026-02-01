<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        $key = $this->faker->randomElement(['admin', 'assessor', 'teacher', 'operator', 'viewer']);

        return [
            'key' => $key . '_' . $this->faker->unique()->bothify('##'),
            'name' => ucfirst($key),
            'description' => $this->faker->sentence(),
        ];
    }
}
