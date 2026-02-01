<?php

namespace Database\Factories;

use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\TeacherProfile>
 */
class TeacherProfileFactory extends Factory
{
    protected $model = TeacherProfile::class;

    public function definition(): array
    {
        $subjects = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'PAI', 'PKn'];

        return [
            'user_id' => User::factory(),
            'employee_no' => $this->faker->optional()->bothify('NIP##########'),
            'subject' => $this->faker->randomElement($subjects),
            'employment_status' => $this->faker->randomElement(['PNS', 'Honorer', 'GTY', 'GTT']),
            'position' => $this->faker->randomElement(['Guru', 'Wali Kelas', 'Koordinator', 'Pembina']),
            'meta' => ['seed' => 'factory'],
        ];
    }
}
