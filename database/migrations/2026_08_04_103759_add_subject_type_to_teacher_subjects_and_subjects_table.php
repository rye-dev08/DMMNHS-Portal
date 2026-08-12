<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->string('subject_type', 20)->default('Major')->after('room_no');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->string('subject_type', 20)->default('Major')->after('room_no');
        });
    }

    public function down(): void
    {
        Schema::table('teacher_subjects', function (Blueprint $table) {
            $table->dropColumn('subject_type');
        });

        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('subject_type');
        });
    }
};
