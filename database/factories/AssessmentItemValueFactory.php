<?php

namespace Database\Factories;

use App\Models\Assessment;
use App\Models\AssessmentItemValue;
use App\Models\KpiFormItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\AssessmentItemValue>
 */
class AssessmentItemValueFactory extends Factory
{
    protected $model = AssessmentItemValue::class;

    public function definition(): array
    {
        $v = $this->faker->numberBetween(1, 4);

        return [
            'assessment_id' => Assessment::factory(),
            'form_item_id' => KpiFormItem::factory(),
            'value_number' => $v,
            'value_string' => null,
            'value_bool' => null,
            'notes' => $this->faker->optional()->sentence(),
            'score_value' => $v,
            'meta' => ['seed' => 'factory'],
        ];
    }
}
