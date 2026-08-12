<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Split the old monolithic "admin" role into two dedicated roles:
     *   - system_admin (portal configuration, account management)
     *   - office_admin  (academic/registrar operations)
     *
     * Existing "admin" accounts become system administrators.
     */
    public function up(): void
    {
        // MySQL validates existing rows against an enum on every MODIFY, so the
        // old 'admin' value must remain legal until the data has been migrated.
        // Step 1: widen to an intermediate enum that still accepts 'admin'.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'system_admin', 'office_admin', 'teacher', 'student'])->change();
        });

        // Step 2: rename the stored value.
        DB::table('users')->where('role', 'admin')->update(['role' => 'system_admin']);

        // Step 3: narrow to the final enum.
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['system_admin', 'office_admin', 'teacher', 'student'])->change();
        });
    }

    public function down(): void
    {
        // Best-effort restore: both staff roles collapse back to "admin".
        DB::table('users')->where('role', 'office_admin')->update(['role' => 'admin']);
        DB::table('users')->where('role', 'system_admin')->update(['role' => 'admin']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher', 'student'])->change();
        });
    }
};
