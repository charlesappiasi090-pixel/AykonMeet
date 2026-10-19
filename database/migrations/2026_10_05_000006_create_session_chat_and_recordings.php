<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_session_chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('online_session_participants')->cascadeOnDelete();
            $table->text('message');
            $table->timestamps();
            $table->index(['meeting_id', 'id']);
        });

        Schema::create('online_session_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('online_session_participants')->cascadeOnDelete();
            $table->string('storage_path')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->string('status', 20)->default('recording');
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            $table->index(['meeting_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_session_recordings');
        Schema::dropIfExists('online_session_chat_messages');
    }
};
