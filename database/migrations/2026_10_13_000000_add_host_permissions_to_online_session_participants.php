<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_session_participants', function (Blueprint $table) {
            $table->string('status')->default('admitted')->after('hand_raised');
            $table->boolean('is_co_host')->default(false)->after('status');
            $table->json('co_host_permissions')->nullable()->after('is_co_host');
            $table->timestamp('requested_at')->nullable()->after('joined_at');
            $table->timestamp('admitted_at')->nullable()->after('requested_at');
            $table->timestamp('removed_at')->nullable()->after('admitted_at');
            $table->index(['meeting_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('online_session_participants', function (Blueprint $table) {
            $table->dropIndex(['meeting_id', 'status']);
            $table->dropColumn([
                'status',
                'is_co_host',
                'co_host_permissions',
                'requested_at',
                'admitted_at',
                'removed_at',
            ]);
        });
    }
};
