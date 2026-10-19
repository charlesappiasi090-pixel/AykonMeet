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
        Schema::create('meeting_series', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('meeting_topic');
            $table->text('description')->nullable();
            $table->string('frequency'); // daily, weekly, monthly, quarterly
            $table->unsignedInteger('interval')->default(1); // every 1 day, 1 week, etc.
            $table->json('days_of_week')->nullable(); // for weekly: [1,3,5] = Mon/Wed/Fri
            $table->unsignedInteger('day_of_month')->nullable(); // for monthly/quarterly: 1-28, or -1 for last day
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->date('recurrence_end_date')->nullable(); // when recurrence stops
            $table->unsignedInteger('max_occurrences')->nullable(); // alternative end condition
            $table->unsignedInteger('occurrences_count')->default(0); // how many generated
            $table->json('settings')->nullable(); // meeting settings template
            $table->boolean('is_active')->default(true);
            $table->foreignId('host_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('timezone')->default('UTC');
            $table->timestamps();

            $table->index(['is_active', 'start_date']);
            $table->index(['host_user_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_series');
    }
};
