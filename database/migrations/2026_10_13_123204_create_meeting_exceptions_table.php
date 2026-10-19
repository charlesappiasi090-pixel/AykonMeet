<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meeting_exceptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained('meeting_series')->cascadeOnDelete();
            $table->unsignedInteger('occurrence_number');
            $table->foreignId('meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->enum('exception_type', ['modified', 'cancelled', 'rescheduled'])->default('modified');
            $table->json('modified_fields')->nullable(); // stores the fields that were changed
            $table->dateTime('original_start')->nullable();
            $table->dateTime('original_end')->nullable();
            $table->dateTime('new_start')->nullable();
            $table->dateTime('new_end')->nullable();
            $table->boolean('requires_approval')->default(false);
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();

            $table->unique(['series_id', 'occurrence_number']);
            $table->index(['series_id', 'exception_type']);
            $table->index(['approval_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_exceptions');
    }
};
