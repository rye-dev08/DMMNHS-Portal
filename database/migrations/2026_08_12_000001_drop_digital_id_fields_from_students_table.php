<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove the Digital Student ID columns (feature removed). Guarded so it is
     * a no-op on databases created without the original add migration.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('students', 'student_id_no')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_student_id_no_unique');
            $table->dropUnique('students_id_token_unique');
            $table->dropColumn(['student_id_no', 'id_token', 'id_token_generated_at', 'photo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_id_no', 20)->nullable()->unique()->after('user_id');
            $table->string('id_token', 64)->nullable()->unique()->after('student_id_no');
            $table->timestamp('id_token_generated_at')->nullable()->after('id_token');
            $table->string('photo', 255)->nullable()->after('status');
        });
    }
};
