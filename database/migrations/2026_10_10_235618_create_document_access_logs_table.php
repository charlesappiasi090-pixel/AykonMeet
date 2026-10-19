<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_share_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->nullable()->constrained('online_session_participants')->nullOnDelete();
            $table->string('action'); // upload, view, preview, download, share_update, revoke
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->json('metadata')->nullable(); // additional context
            $table->timestamps();
            $table->index(['document_share_id', 'action']);
            $table->index(['participant_id', 'action']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_access_logs');
    }
};
