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
        Schema::create('settings', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('current_semester')->default(1);
            $table->string('current_school_year', 20)->default('2024-2025');
            $table->integer('max_students_per_class')->default(30);
            $table->integer('max_subjects_per_teacher')->default(8);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};