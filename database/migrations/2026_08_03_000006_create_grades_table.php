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
        Schema::create('grades', function (Blueprint $table) {
            $table->id();
            $table->integer('student_id');
            $table->integer('subject_id');
            $table->string('grade', 10)->default('N/A');
            $table->string('remarks', 255)->nullable();
            $table->string('quarter', 20)->nullable();
            $table->dateTime('date_submitted')->nullable();
            $table->unique(['student_id', 'subject_id', 'quarter'], 'unique_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grades');
    }
};