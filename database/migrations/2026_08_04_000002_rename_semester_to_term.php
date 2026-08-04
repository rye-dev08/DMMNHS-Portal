<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('current_semester', 'current_term');
        });

        Schema::rename('previous_semester_subjects', 'previous_term_subjects');
        Schema::table('previous_term_subjects', function (Blueprint $table) {
            $table->renameColumn('archived_semester', 'archived_term');
        });

        Schema::rename('previous_semester_grades', 'previous_term_grades');
        Schema::table('previous_term_grades', function (Blueprint $table) {
            $table->renameColumn('archived_semester', 'archived_term');
        });

        Schema::table('graduated_students', function (Blueprint $table) {
            $table->renameColumn('graduation_semester', 'graduation_term');
        });

        DB::statement("UPDATE grades SET quarter = REPLACE(quarter, 'Sem ', 'Term ') WHERE quarter LIKE 'Sem %'");
        DB::statement("UPDATE previous_term_grades SET quarter = REPLACE(quarter, 'Sem ', 'Term ') WHERE quarter LIKE 'Sem %'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE previous_term_grades SET quarter = REPLACE(quarter, 'Term ', 'Sem ') WHERE quarter LIKE 'Term %'");
        DB::statement("UPDATE grades SET quarter = REPLACE(quarter, 'Term ', 'Sem ') WHERE quarter LIKE 'Term %'");

        Schema::table('graduated_students', function (Blueprint $table) {
            $table->renameColumn('graduation_term', 'graduation_semester');
        });

        Schema::rename('previous_term_grades', 'previous_semester_grades');
        Schema::table('previous_semester_grades', function (Blueprint $table) {
            $table->renameColumn('archived_term', 'archived_semester');
        });

        Schema::rename('previous_term_subjects', 'previous_semester_subjects');
        Schema::table('previous_semester_subjects', function (Blueprint $table) {
            $table->renameColumn('archived_term', 'archived_semester');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->renameColumn('current_term', 'current_semester');
        });
    }
};