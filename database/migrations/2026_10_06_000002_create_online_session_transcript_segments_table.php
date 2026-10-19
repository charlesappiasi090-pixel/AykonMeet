<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_session_transcript_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('online_session_participants')->cascadeOnDelete();
            $table->text('text');
            $table->timestamp('spoken_at');
            $table->timestamps();
            $table->index(['meeting_id', 'spoken_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_session_transcript_segments');
    }
};
