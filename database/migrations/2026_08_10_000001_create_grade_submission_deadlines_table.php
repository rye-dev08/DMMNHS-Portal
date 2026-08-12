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
        Schema::create('grade_submission_deadlines', function (Blueprint $table) {
            $table->id();
            $table->string('school_year', 20);
            $table->unsignedInteger('term');
            $table->string('subject_name', 100)->default('');
            $table->date('deadline');
            $table->timestamps();

            $table->unique(['school_year', 'term', 'subject_name'], 'unique_grade_deadline');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grade_submission_deadlines');
    }
};
