<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Seed the singleton settings row (id = 1), matching the legacy
     * `INSERT ... ON DUPLICATE KEY UPDATE` behaviour in database_fixed.sql.
     */
    public function run(): void
    {
        DB::table('settings')->updateOrInsert(
            ['id' => 1],
            [
                'current_semester' => 1,
                'current_school_year' => '2025-2026',
                'max_students_per_class' => 30,
                'max_subjects_per_teacher' => 8,
            ]
        );
    }
}