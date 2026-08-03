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
        Schema::create('previous_semester_grades', function (Blueprint $table) {
            $table->id();
            $table->integer('original_grade_id');
            $table->integer('student_id');
            $table->integer('subject_id');
            $table->string('grade', 10)->nullable();
            $table->string('quarter', 20)->nullable();
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
        Schema::dropIfExists('previous_semester_grades');
    }
};