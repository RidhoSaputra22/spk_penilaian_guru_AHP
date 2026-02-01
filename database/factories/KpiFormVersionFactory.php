<?php

namespace Database\Factories;

use App\Models\KpiFormTemplate;
use App\Models\KpiFormVersion;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\KpiFormVersion>
 */
class KpiFormVersionFactory extends Factory
{
    protected $model = KpiFormVersion::class;

    public function definition(): array
    {
        $status = $this->faker->randomElement(['draft', 'published']);

        return [
            'template_id' => KpiFormTemplate::factory(),
            'version' => 1,
            'status' => $status,
            'published_at' => $status === 'published' ? now() : null,
            'locked_at' => null,
            'created_by' => User::factory(),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
