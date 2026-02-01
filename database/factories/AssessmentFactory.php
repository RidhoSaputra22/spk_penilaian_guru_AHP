<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentPeriod;
use App\Models\AssessorProfile;
use App\Models\KpiFormAssignment;
use App\Models\TeacherProfile;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Assessment>
 */
class AssessmentFactory extends Factory
{
    protected $model = Assessment::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'finalized']);

        return [
            'assessment_period_id' => AssessmentPeriod::factory(),
            'assignment_id' => KpiFormAssignment::factory(),
            'teacher_profile_id' => TeacherProfile::factory(),
            'assessor_profile_id' => AssessorProfile::factory(),
            'status' => $status,
            'started_at' => now()->subDays($this->faker->numberBetween(1, 10)),
            'submitted_at' => $status === 'finalized' ? now()->subDays(1) : null,
            'finalized_at' => $status === 'finalized' ? now() : null,
            'reopened_at' => null,
            'reopened_reason' => null,
            'meta' => ['seed' => 'factory'],
        ];
    }

    public function finalized(): static
    {
        return $this->state(fn () => [
            'status' => 'finalized',
            'submitted_at' => now()->subHours(2),
            'finalized_at' => now()->subHour(),
        ]);
    }
}
