<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_rooms', function (Blueprint $table) {
            $table->unsignedInteger('capacity')->nullable()->after('floor');
            $table->string('location')->nullable()->after('capacity');
            $table->text('equipment')->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_rooms', function (Blueprint $table) {
            $table->dropColumn(['capacity', 'location', 'equipment']);
        });
    }
};
