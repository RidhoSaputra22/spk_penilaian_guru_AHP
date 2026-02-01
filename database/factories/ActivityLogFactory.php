<?php

namespace Database\Factories;

use App\Models\ActivityLog;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\ActivityLog>
 */
class ActivityLogFactory extends Factory
{
    protected $model = ActivityLog::class;

    public function definition(): array
    {
        return [
            'institution_id' => Institution::factory(),
            'user_id' => User::factory(),
            'action' => $this->faker->randomElement(['form.published', 'ahp.finalized', 'assessment.finalized', 'assessment.reopened']),
            'subject_type' => null,
            'subject_id' => null,
            'properties' => ['seed' => 'factory'],
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'created_at' => now(),
        ];
    }
}
