<?php

namespace Database\Factories;

use App\Models\AssessmentPeriod;
use App\Models\Institution;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AssessmentPeriod>
 */
class AssessmentPeriodFactory extends Factory
{
    protected $model = AssessmentPeriod::class;

    public function definition(): array
    {
        $year1 = (int) now()->format('Y');
        $year2 = $year1 + 1;

        return [
            'institution_id' => Institution::factory(),
            'name' => 'Semester ' . $this->faker->randomElement(['Ganjil', 'Genap']) . " {$year1}/{$year2}",
            'academic_year' => "{$year1}/{$year2}",
            'semester' => $this->faker->randomElement(['ganjil', 'genap']),
            'scoring_open_at' => now()->subDays(7),
            'scoring_close_at' => now()->addDays(14),
            'status' => $this->faker->randomElement(['draft', 'open', 'closed']),
            'meta' => ['seed' => 'factory'],
        ];
    }

    public function open(): static
    {
        return $this->state(fn () => [
            'status' => 'open',
            'scoring_open_at' => now()->subDays(1),
            'scoring_close_at' => now()->addDays(30),
        ]);
    }
}
