<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(SettingSeeder::class);

        // Initial dev admin account (the legacy system created this via account.php).
        // See memory.md -> "Initial development accounts".
        User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'System Administrator',
                'email' => 'admin@dmnhs.edu',
                'password_hash' => Hash::make('Admin123!'),
                'role' => 'system_admin',
                'status' => 'active',
            ]
        );

        // Office Administrator demo account.
        User::firstOrCreate(
            ['username' => 'office'],
            [
                'name' => 'Office Administrator',
                'email' => 'office@dmnhs.edu',
                'password_hash' => Hash::make('Office123!'),
                'role' => 'office_admin',
                'status' => 'active',
            ]
        );
    }
}
