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
        Schema::create('graduated_students', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('graduation_grade');
            $table->integer('graduation_semester');
            $table->string('graduation_school_year', 20);
            $table->date('graduation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('graduated_students');
    }
};