<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploader_id')->constrained('online_session_participants')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('mime_type');
            $table->string('extension', 10);
            $table->unsignedBigInteger('file_size');
            $table->string('storage_path');
            $table->string('storage_disk')->default('local');
            $table->string('share_scope')->default('all'); // all, companies, individuals
            $table->json('shared_with_companies')->nullable(); // array of meeting_company_ids
            $table->json('shared_with_participants')->nullable(); // array of participant_ids
            $table->boolean('allow_download')->default(true);
            $table->boolean('allow_preview')->default(true);
            $table->string('status')->default('active'); // active, revoked, deleted
            $table->timestamp('revoked_at')->nullable();
            $table->foreignId('revoked_by')->nullable()->constrained('online_session_participants')->nullOnDelete();
            $table->timestamps();
            $table->index(['meeting_id', 'status']);
            $table->index(['uploader_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_shares');
    }
};
