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
        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->integer('teacher_id');
            $table->string('subject_name', 100);
            $table->string('course_code', 50)->nullable();
            $table->string('teacher_code', 50)->nullable();
            $table->string('room_no', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['teacher_id', 'subject_name'], 'unique_subject_per_teacher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_subjects');
    }
};