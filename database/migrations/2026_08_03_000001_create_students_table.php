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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->enum('sex', ['M', 'F'])->nullable();
            $table->date('birthday')->nullable();
            $table->integer('age')->nullable();
            $table->integer('grade_level')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('needs_reenrollment', ['yes', 'no'])->default('no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
