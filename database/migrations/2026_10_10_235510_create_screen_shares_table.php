<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('screen_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('online_session_participants')->cascadeOnDelete();
            $table->string('share_type')->default('screen'); // screen, window, tab
            $table->string('status')->default('active'); // active, ended
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->json('metadata')->nullable(); // resolution, frame rate, etc.
            $table->index(['meeting_id', 'status']);
            $table->index(['participant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('screen_shares');
    }
};
