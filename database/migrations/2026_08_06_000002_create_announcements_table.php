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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->string('title', 200);
            $table->string('short_summary', 255)->nullable();
            $table->text('content')->nullable();
            $table->string('attachment', 255)->nullable();
            $table->string('attachment_name', 255)->nullable();
            $table->string('priority', 20)->default('normal');
            $table->string('status', 20)->default('published');
            $table->string('target_role', 20)->default('all');
            $table->date('publish_date');
            $table->date('expiration_date')->nullable();
            $table->string('school_year', 20);
            $table->unsignedTinyInteger('term');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['school_year', 'term', 'status']);
            $table->index('publish_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
