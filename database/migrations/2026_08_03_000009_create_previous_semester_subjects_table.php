<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('previous_semester_subjects', function (Blueprint $table) {
            $table->id();
            $table->integer('original_subject_id');
            $table->integer('student_id');
            $table->integer('teacher_id');
            $table->string('subject_name', 100);
            $table->string('course_code', 50)->nullable();
            $table->string('teacher_code', 50)->nullable();
            $table->string('room_no', 50)->nullable();
            $table->integer('archived_semester');
            $table->string('archived_school_year', 20);
            $table->timestamp('archived_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('previous_semester_subjects');
    }
};