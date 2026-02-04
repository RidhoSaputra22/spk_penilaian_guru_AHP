<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Seeder khusus untuk panel assessor.
 *
 * Jalankan dengan: php artisan db:seed --class=AssessorPanelSeeder
 *
 * Seeder ini akan membuat data lengkap yang dibutuhkan untuk panel assessor:
 * - Users (admin, assessor, teachers)
 * - Assessment periods (periode penilaian)
 * - KPI forms (formulir penilaian)
 * - Form assignments (penugasan assessor ke guru)
 * - Assessments with scores (penilaian dengan skor)
 * - AHP weights & comparisons (bobot dan perbandingan AHP)
 * - Results (hasil penilaian)
 */
class AssessorPanelSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('🚀 Mulai seeding data untuk panel assessor...');
        $this->command->newLine();

        // Seed semua data demo
        $this->call([
            DemoUsersSeeder::class,      // Users, assessor, teachers
            DemoCriteriaSeeder::class,   // Criteria & sub-criteria
            DemoFormSeeder::class,       // KPI forms & templates
            DemoAssignmentSeeder::class, // Form assignments
            DemoAhpSeeder::class,        // AHP weights & comparisons
            DemoAssessmentsSeeder::class,// Assessments dengan scores
            DemoResultsSeeder::class,    // Teacher results & rankings
        ]);

        $this->command->newLine();
        $this->command->info('✅ Seeding selesai!');
        $this->command->newLine();

        $this->command->info('📊 Data yang telah dibuat:');
        $this->command->info('  • 1 Admin');
        $this->command->info('  • 3 Assessors (Tim Penilai)');
        $this->command->info('  • 10 Teachers (Guru)');
        $this->command->info('  • 3 Teacher Groups');
        $this->command->info('  • 2 Assessment Periods (1 aktif, 1 selesai)');
        $this->command->info('  • KPI Form Template dengan items');
        $this->command->info('  • Form Assignments untuk assessors');
        $this->command->info('  • Assessments dengan scores lengkap');
        $this->command->info('  • AHP Weights & Comparisons');
        $this->command->info('  • Teacher Results & Rankings');
        $this->command->newLine();

        $this->command->info('🔑 Login credentials:');
        $this->command->table(
            ['Role', 'Email', 'Password'],
            [
                ['Admin', 'admin@demo.local', 'password'],
                ['Assessor 1', 'assessor1@demo.local', 'password'],
                ['Assessor 2', 'assessor2@demo.local', 'password'],
                ['Assessor 3', 'assessor3@demo.local', 'password'],
                ['Guru 1', 'guru1@demo.local', 'password'],
                ['Guru 2-10', 'guru2@demo.local ... guru10@demo.local', 'password'],
            ]
        );

        $this->command->newLine();
        $this->command->info('🎯 Akses panel assessor di: /assessor/dashboard');
        $this->command->info('   Login menggunakan: assessor1@demo.local / password');
    }
}
