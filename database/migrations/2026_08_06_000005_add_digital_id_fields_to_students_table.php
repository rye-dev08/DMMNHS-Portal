<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->string('student_id_no', 20)->nullable()->unique()->after('user_id');
            $table->string('id_token', 64)->nullable()->unique()->after('student_id_no');
            $table->timestamp('id_token_generated_at')->nullable()->after('id_token');
            $table->string('photo', 255)->nullable()->after('status');
        });

        // Backfill existing students with a stable, unique student ID number
        // derived from the current school year start and the student id.
        $prefix = (int) date('Y');
        $year = DB::table('settings')->where('id', 1)->value('current_school_year');
        if (is_string($year) && str_contains($year, '-')) {
            $prefix = (int) explode('-', $year)[0];
        }

        foreach (DB::table('students')->select('id')->whereNull('student_id_no')->get() as $row) {
            DB::table('students')->where('id', $row->id)->update([
                'student_id_no' => $prefix.'-'.str_pad((string) $row->id, 5, '0', STR_PAD_LEFT),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropUnique('students_student_id_no_unique');
            $table->dropUnique('students_id_token_unique');
            $table->dropColumn(['student_id_no', 'id_token', 'id_token_generated_at', 'photo']);
        });
    }
};
