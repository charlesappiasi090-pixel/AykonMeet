<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->string('invitation_token', 64)->nullable()->unique()->after('attendance_mode');
            $table->string('invitation_status', 20)->default('pending')->after('invitation_token');
            $table->timestamp('invitation_responded_at')->nullable()->after('invitation_status');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_responded_at');
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropUnique(['invitation_token']);
            $table->dropColumn([
                'invitation_token',
                'invitation_status',
                'invitation_responded_at',
                'invitation_expires_at',
            ]);
        });
    }
};
