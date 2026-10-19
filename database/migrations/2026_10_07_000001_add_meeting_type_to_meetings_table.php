<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->string('meeting_type', 20)->nullable()->after('is_online');
        });

        DB::table('meetings')->where('is_online', true)->update(['meeting_type' => 'online']);
        DB::table('meetings')
            ->where('is_online', true)
            ->whereNotNull('meeting_room_id')
            ->update(['meeting_type' => 'hybrid']);
        DB::table('meetings')->where('is_online', false)->update(['meeting_type' => 'in_person']);
    }

    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('meeting_type');
        });
    }
};
