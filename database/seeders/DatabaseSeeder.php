<?php

namespace Database\Seeders;

use App\Enum\RoleType;
use App\Models\Person;
use App\Models\User;
use App\Models\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{


    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Call DemoDataSeeder which will seed all demo data including assessor panel data
        $this->call([
            DemoDataSeeder::class,
        ]);

        $this->command->info('✅ Demo data berhasil di-seed!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin    : admin@demo.local / password');
        $this->command->info('  Assessor : assessor1@demo.local / password');
        $this->command->info('  Teacher  : guru1@demo.local / password');
    }
}
