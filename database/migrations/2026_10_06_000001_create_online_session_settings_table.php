<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_session_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('recording_enabled')->default(true);
            $table->boolean('allow_attendee_recording')->default(true);
            $table->boolean('downloads_for_hosts')->default(false);
            $table->timestamps();
        });

        DB::table('online_session_settings')->insert([
            'id' => 1,
            'recording_enabled' => true,
            'allow_attendee_recording' => true,
            'downloads_for_hosts' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('online_session_settings');
    }
};
