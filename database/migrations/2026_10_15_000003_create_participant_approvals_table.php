<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participant_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendee_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('online_session_participant_id')->nullable()->constrained('online_session_participants')->cascadeOnDelete();
            $table->foreignId('requested_company_id')->nullable()->constrained('meeting_companies')->nullOnDelete();
            $table->foreignId('current_company_id')->nullable()->constrained('meeting_companies')->nullOnDelete();
            $table->string('type', 20);
            $table->string('status', 20)->default('pending');
            $table->string('email')->nullable();
            $table->string('display_name')->nullable();
            $table->foreignId('requested_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('decided_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('decided_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['meeting_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_approvals');
    }
};
