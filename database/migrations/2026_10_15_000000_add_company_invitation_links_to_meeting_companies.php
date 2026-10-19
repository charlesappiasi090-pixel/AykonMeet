<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_companies', function (Blueprint $table) {
            $table->string('invitation_token', 64)->nullable()->unique()->after('invited_at');
            $table->string('invitation_token_hash', 64)->nullable()->after('invitation_token');
            $table->string('invitation_status', 20)->default('draft')->after('invitation_token_hash');
            $table->timestamp('invitation_expires_at')->nullable()->after('invitation_status');
            $table->timestamp('invitation_revoked_at')->nullable()->after('invitation_expires_at');
            $table->timestamp('invitation_last_used_at')->nullable()->after('invitation_revoked_at');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_companies', function (Blueprint $table) {
            $table->dropUnique(['invitation_token']);
            $table->dropColumn([
                'invitation_token',
                'invitation_token_hash',
                'invitation_status',
                'invitation_expires_at',
                'invitation_revoked_at',
                'invitation_last_used_at',
            ]);
        });
    }
};
