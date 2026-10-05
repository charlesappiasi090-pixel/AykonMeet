<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('meeting_code')->unique();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('company_name')->nullable();
            $table->string('meeting_topic');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('from_location')->nullable();
            $table->string('to_location')->nullable();
            $table->foreignId('meeting_room_id')->nullable()->constrained('meeting_rooms')->nullOnDelete();
            $table->string('status')->default('scheduled');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meetings');
    }
};
