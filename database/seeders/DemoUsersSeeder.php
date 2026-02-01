<?php

namespace Database\Seeders;

use App\Models\AssessorProfile;
use App\Models\Institution;
use App\Models\Role;
use App\Models\TeacherGroup;
use App\Models\TeacherProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoUsersSeeder extends Seeder
{
    public function run(): void
    {
        if (User::where('email', 'admin@demo.local')->exists()) {
            return; // already seeded
        }

        DB::transaction(function () {
            $institution = Institution::firstOrCreate(
                ['code' => 'DEMO'],
                ['name' => 'Madrasah Alauddin (Demo)', 'address' => 'Kabupaten Gowa']
            );

            // Roles (safe if RoleSeeder not run)
            $adminRole = Role::firstOrCreate(['key' => 'admin'], ['name' => 'Admin']);
            $assessorRole = Role::firstOrCreate(['key' => 'assessor'], ['name' => 'Assessor']);
            $teacherRole = Role::firstOrCreate(['key' => 'teacher'], ['name' => 'Teacher']);

            // Admin
            $admin = User::create([
                'institution_id' => $institution->id,
                'name' => 'Admin Demo',
                'email' => 'admin@demo.local',
                'password' => 'password',
                'status' => 'active',
            ]);
            $admin->roles()->syncWithoutDetaching([$adminRole->id]);

            // Assessors
            $assessors = collect();
            foreach (range(1, 3) as $i) {
                $u = User::create([
                    'institution_id' => $institution->id,
                    'name' => "Assessor {$i}",
                    'email' => "assessor{$i}@demo.local",
                    'password' => 'password',
                    'status' => 'active',
                ]);
                $u->roles()->syncWithoutDetaching([$assessorRole->id]);

                $assessors->push(AssessorProfile::create([
                    'user_id' => $u->id,
                    'title' => $i === 1 ? 'Kepala Madrasah' : 'Waka Kurikulum',
                    'meta' => ['demo' => true],
                ]));
            }

            // Teachers + teacher groups by subject
            $subjects = ['Matematika', 'Bahasa Indonesia', 'Bahasa Inggris', 'IPA', 'IPS', 'PAI'];
            $groups = collect();

            foreach (['Kelompok Mapel Umum', 'Kelompok Mapel Agama', 'Kelompok Bahasa'] as $gname) {
                $groups->push(TeacherGroup::firstOrCreate(
                    ['institution_id' => $institution->id, 'name' => $gname],
                    ['description' => 'Demo group']
                ));
            }

            foreach (range(1, 10) as $i) {
                $subject = $subjects[($i - 1) % count($subjects)];
                $u = User::create([
                    'institution_id' => $institution->id,
                    'name' => "Guru {$i}",
                    'email' => "guru{$i}@demo.local",
                    'password' => 'password',
                    'status' => 'active',
                ]);
                $u->roles()->syncWithoutDetaching([$teacherRole->id]);

                $teacher = TeacherProfile::create([
                    'user_id' => $u->id,
                    'employee_no' => "NIP2026{$i}000",
                    'subject' => $subject,
                    'employment_status' => $i % 2 === 0 ? 'PNS' : 'Honorer',
                    'position' => $i % 3 === 0 ? 'Wali Kelas' : 'Guru',
                    'meta' => ['demo' => true],
                ]);

                // crude grouping
                if (in_array($subject, ['PAI'])) {
                    $teacher->groups()->syncWithoutDetaching([$groups[1]->id]);
                } elseif (in_array($subject, ['Bahasa Indonesia', 'Bahasa Inggris'])) {
                    $teacher->groups()->syncWithoutDetaching([$groups[2]->id]);
                } else {
                    $teacher->groups()->syncWithoutDetaching([$groups[0]->id]);
                }
            }
        });
    }
}
