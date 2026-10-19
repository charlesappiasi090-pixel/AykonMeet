<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            if (! Schema::hasColumn('meetings', 'min_attendees')) {
                $table->unsignedInteger('min_attendees')->default(0)->after('meeting_room_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('min_attendees');
        });
    }
};
