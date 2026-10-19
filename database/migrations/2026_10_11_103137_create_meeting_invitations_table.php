<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->foreignId('meeting_company_id')->nullable()->constrained('meeting_companies')->cascadeOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('token')->unique();
            $table->string('token_hash')->unique();
            $table->enum('status', ['pending', 'sent', 'accepted', 'declined', 'tentative', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['meeting_id', 'status']);
            $table->index(['email', 'meeting_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_invitations');
    }
};
