<?php

namespace Database\Factories;

use App\Models\CriteriaNode;
use App\Models\KpiFormItem;
use App\Models\KpiFormSection;
use App\Models\ScoringScale;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\KpiFormItem>
 */
class KpiFormItemFactory extends Factory
{
    protected $model = KpiFormItem::class;

    public function definition(): array
    {
        $field = $this->faker->randomElement(['numeric', 'dropdown', 'boolean', 'text']);

        return [
            'section_id' => KpiFormSection::factory(),
            'criteria_node_id' => null,
            'label' => $this->faker->sentence(6),
            'help_text' => $this->faker->optional()->sentence(),
            'field_type' => $field,
            'is_required' => $this->faker->boolean(70),
            'min_value' => $field === 'numeric' ? 1 : null,
            'max_value' => $field === 'numeric' ? 4 : null,
            'scoring_scale_id' => $field === 'numeric' ? ScoringScale::factory() : null,
            'default_value' => null,
            'sort_order' => $this->faker->numberBetween(0, 100),
            'meta' => ['seed' => 'factory'],
        ];
    }

    public function numeric(?ScoringScale $scale = null): static
    {
        return $this->state(function () use ($scale) {
            return [
                'field_type' => 'numeric',
                'min_value' => 1,
                'max_value' => 4,
                'scoring_scale_id' => $scale?->id,
            ];
        });
    }

    public function forCriteriaNode(CriteriaNode $node): static
    {
        return $this->state(fn () => ['criteria_node_id' => $node->id]);
    }
}
