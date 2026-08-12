<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Extends the existing contact_messages table (the Teacher/Student to
     * Admin messaging feature) with moderation fields. Anonymous "Contact Us"
     * submissions keep working: user_id / sender_role stay null for guests.
     */
    public function up(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('email');
            $table->string('sender_role', 20)->nullable()->after('user_id');
            $table->enum('status', ['pending', 'valid', 'invalid'])
                ->default('pending')
                ->after('message');
            $table->timestamp('moderated_at')->nullable()->after('status');
            $table->timestamp('expires_at')->nullable()->after('moderated_at');
            $table->timestamp('archived_at')->nullable()->after('expires_at');

            $table->index(['status', 'created_at']);
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id']);
            $table->dropColumn([
                'user_id',
                'sender_role',
                'status',
                'moderated_at',
                'expires_at',
                'archived_at',
            ]);
        });
    }
};
