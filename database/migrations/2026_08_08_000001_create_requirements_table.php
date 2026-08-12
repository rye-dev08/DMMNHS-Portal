<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Requirements created by teachers for their approved/enrolled students.
     */
    public function up(): void
    {
        Schema::create('requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('teacher_id');
            $table->string('title', 200);
            $table->enum('requirement_type', ['legal_document', 'school_form', 'academic', 'activity', 'project', 'other'])
                ->default('other');
            $table->text('description');
            $table->date('due_date')->nullable();
            $table->boolean('submission_required')->default(true);
            $table->string('attachment', 255)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->string('section', 100)->nullable();
            $table->string('school_year', 20);
            $table->unsignedTinyInteger('term');
            $table->string('status', 20)->default('active');
            $table->timestamp('last_bumped_at')->nullable();
            $table->unsignedBigInteger('last_bumped_by')->nullable();
            $table->unsignedInteger('bump_count')->default(0);
            $table->timestamps();

            $table->index(['teacher_id', 'school_year', 'term']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requirements');
    }
};
