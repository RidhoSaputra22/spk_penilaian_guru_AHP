<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            DemoUsersSeeder::class,
            DemoCriteriaSeeder::class,
            DemoFormSeeder::class,
            DemoAssignmentSeeder::class,
            DemoAhpSeeder::class,
            DemoAssessmentsSeeder::class,
            DemoResultsSeeder::class,
            SupervisionFormsSeeder::class, // Form 2, 3, 4 Supervisi
        ]);
    }
}
