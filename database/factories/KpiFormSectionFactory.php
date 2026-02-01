<?php

namespace Database\Factories;

use App\Models\CriteriaNode;
use App\Models\KpiFormSection;
use App\Models\KpiFormVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\KpiFormSection>
 */
class KpiFormSectionFactory extends Factory
{
    protected $model = KpiFormSection::class;

    public function definition(): array
    {
        return [
            'form_version_id' => KpiFormVersion::factory(),
            'criteria_node_id' => null,
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'sort_order' => $this->faker->numberBetween(0, 50),
            'meta' => ['seed' => 'factory'],
        ];
    }

    public function forCriteriaNode(CriteriaNode $node): static
    {
        return $this->state(fn () => ['criteria_node_id' => $node->id]);
    }
}
