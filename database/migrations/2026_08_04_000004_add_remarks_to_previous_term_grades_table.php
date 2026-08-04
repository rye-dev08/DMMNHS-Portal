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
        Schema::table('previous_term_grades', function (Blueprint $table) {
            $table->string('remarks', 255)->nullable()->after('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('previous_term_grades', function (Blueprint $table) {
            $table->dropColumn('remarks');
        });
    }
};