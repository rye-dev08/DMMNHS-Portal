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
        Schema::create('teacher_approval', function (Blueprint $table) {
            $table->id();
            $table->integer('teacher_id');
            $table->integer('max_students')->default(30);
            $table->integer('max_subjects')->default(8);
            $table->enum('status', ['approved', 'inactive'])->default('approved');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teacher_approval');
    }
};
