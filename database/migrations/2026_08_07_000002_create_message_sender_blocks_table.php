<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tracks manually blocked message senders. A user is considered blocked
     * while a row has an unblocked_at of NULL. Blocking is intentionally
     * separate from message moderation (invalid != blocked).
     */
    public function up(): void
    {
        Schema::create('message_sender_blocks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unique();
            $table->text('reason')->nullable();
            $table->unsignedBigInteger('blocked_by')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('unblocked_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_sender_blocks');
    }
};
