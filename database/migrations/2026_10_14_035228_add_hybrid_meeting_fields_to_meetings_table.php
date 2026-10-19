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
        Schema::table('meetings', function (Blueprint $table) {
            // Physical venue details
            $table->string('venue_name')->nullable()->after('physical_location');
            $table->text('venue_address')->nullable()->after('venue_name');
            $table->string('venue_building_room')->nullable()->after('venue_address');
            $table->text('venue_instructions')->nullable()->after('venue_building_room');
            $table->unsignedInteger('venue_capacity')->nullable()->after('venue_instructions');

            // Online meeting settings (stored in settings JSON but can be overridden)
            $table->string('timezone')->default('UTC')->after('venue_capacity');

            // Invitation/RSVP settings
            $table->boolean('require_registration')->default(false)->after('timezone');
            $table->boolean('allow_rsvp_maybe')->default(true)->after('require_registration');
            $table->boolean('allow_rsvp_another_time')->default(true)->after('allow_rsvp_maybe');

            // Document sharing settings
            $table->boolean('enable_document_sharing')->default(true)->after('allow_rsvp_another_time');
            $table->boolean('allow_participant_upload')->default(false)->after('enable_document_sharing');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn([
                'venue_name',
                'venue_address',
                'venue_building_room',
                'venue_instructions',
                'venue_capacity',
                'timezone',
                'require_registration',
                'allow_rsvp_maybe',
                'allow_rsvp_another_time',
                'enable_document_sharing',
                'allow_participant_upload',
            ]);
        });
    }
};
