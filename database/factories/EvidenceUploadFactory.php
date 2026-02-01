<?php

namespace Database\Factories;

use App\Models\AssessmentItemValue;
use App\Models\EvidenceUpload;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\EvidenceUpload>
 */
class EvidenceUploadFactory extends Factory
{
    protected $model = EvidenceUpload::class;

    public function definition(): array
    {
        $filename = Str::random(12) . '.pdf';

        return [
            'assessment_item_value_id' => AssessmentItemValue::factory(),
            'uploaded_by' => User::factory(),
            'disk' => 'public',
            'path' => 'evidence/' . $filename,
            'original_name' => $filename,
            'mime_type' => 'application/pdf',
            'size' => $this->faker->numberBetween(10_000, 2_000_000),
            'url' => null,
            'meta' => ['seed' => 'factory'],
        ];
    }
}
