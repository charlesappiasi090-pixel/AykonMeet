<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->string('attendance_mode', 20)->nullable()->after('checked_out_at');
        });

        DB::table('attendees')
            ->whereNotNull('checked_in_at')
            ->update(['attendance_mode' => 'in_person']);
        DB::table('attendees')
            ->whereExists(function ($query) {
                $query->selectRaw('1')
                    ->from('online_session_participants')
                    ->whereColumn('online_session_participants.attendee_id', 'attendees.id');
            })
            ->update(['attendance_mode' => 'online']);
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropColumn('attendance_mode');
        });
    }
};
