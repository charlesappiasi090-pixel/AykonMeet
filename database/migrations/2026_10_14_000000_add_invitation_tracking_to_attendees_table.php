<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_expires_at');
            $table->timestamp('invitation_delivered_at')->nullable()->after('invitation_sent_at');
            $table->timestamp('invitation_verified_at')->nullable()->after('invitation_delivered_at');
            $table->timestamp('reminder_sent_at')->nullable()->after('invitation_verified_at');
            $table->boolean('requires_approval')->default(false)->after('reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn([
                'invitation_sent_at',
                'invitation_delivered_at',
                'invitation_verified_at',
                'reminder_sent_at',
                'requires_approval',
            ]);
        });
    }
};
