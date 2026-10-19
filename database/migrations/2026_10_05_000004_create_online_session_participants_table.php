<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_session_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('peer_id', 36);
            $table->string('display_name', 80);
            $table->boolean('is_host')->default(false);
            $table->boolean('is_muted_by_host')->default(false);
            $table->timestamp('last_seen_at')->useCurrent();
            $table->timestamps();
            $table->unique(['meeting_id', 'peer_id']);
            $table->index(['meeting_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_session_participants');
    }
};
