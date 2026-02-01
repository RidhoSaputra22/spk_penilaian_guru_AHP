<?php

namespace Database\Factories;

use App\Models\PeriodResult;
use App\Models\TeacherPeriodResult;
use App\Models\TeacherProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TeacherPeriodResult>
 */
class TeacherPeriodResultFactory extends Factory
{
    protected $model = TeacherPeriodResult::class;

    public function definition(): array
    {
        return [
            'period_result_id' => PeriodResult::factory(),
            'teacher_profile_id' => TeacherProfile::factory(),
            'final_score' => $this->faker->randomFloat(4, 40, 100),
            'rank' => null,
            'details' => ['seed' => 'factory'],
        ];
    }
}
