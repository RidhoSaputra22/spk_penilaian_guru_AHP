<?php

namespace Database\Factories;

use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Institution>
 */
class InstitutionFactory extends Factory
{
    protected $model = Institution::class;

    public function definition(): array
    {
        $name = $this->faker->company() . ' Madrasah';

        return [
            'name' => $name,
            'code' => strtoupper($this->faker->bothify('SCH-###??')),
            'address' => $this->faker->address(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
