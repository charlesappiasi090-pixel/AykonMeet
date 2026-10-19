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
        Schema::create('meeting_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('series_id')->constrained('meeting_series')->cascadeOnDelete();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->unsignedInteger('occurrence_number'); // 1, 2, 3...
            $table->dateTime('scheduled_start');
            $table->dateTime('scheduled_end');
            $table->enum('status', ['scheduled', 'generated', 'cancelled', 'modified'])->default('scheduled');
            $table->timestamps();

            $table->unique(['series_id', 'occurrence_number']);
            $table->index(['meeting_id']);
            $table->index(['series_id', 'status']);
            $table->index(['scheduled_start']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_occurrences');
    }
};
