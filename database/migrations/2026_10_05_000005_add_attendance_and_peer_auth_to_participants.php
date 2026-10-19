<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_session_participants', function (Blueprint $table) {
            $table->foreignId('attendee_id')->nullable()->after('meeting_id')->constrained()->nullOnDelete();
            $table->string('token_hash', 64)->nullable()->after('peer_id');
            $table->boolean('hand_raised')->default(false)->after('is_muted_by_host');
            $table->timestamp('joined_at')->nullable()->after('hand_raised');
            $table->timestamp('left_at')->nullable()->after('joined_at');
        });
    }

    public function down(): void
    {
        Schema::table('online_session_participants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('attendee_id');
            $table->dropColumn(['token_hash', 'hand_raised', 'joined_at', 'left_at']);
        });
    }
};
