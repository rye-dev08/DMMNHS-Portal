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
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->integer('teacher_id');
            $table->integer('student_id');
            $table->enum('score_type', ['activity', 'quiz', 'exam']);
            $table->integer('item_no');
            $table->decimal('score', 10, 2)->default(0);
            $table->decimal('max_score', 10, 2)->default(100);
            $table->string('remarks', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->unique(['teacher_id', 'student_id', 'score_type', 'item_no'], 'uq_teacher_student_type_item');
            $table->index(['student_id'], 'idx_student');
            $table->index(['teacher_id'], 'idx_teacher');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment_scores');
    }
};