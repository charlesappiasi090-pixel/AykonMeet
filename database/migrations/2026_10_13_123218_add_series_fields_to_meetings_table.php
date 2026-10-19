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
            $table->foreignId('series_id')->nullable()->constrained('meeting_series')->nullOnDelete();
            $table->boolean('is_series_parent')->default(false);
            $table->unsignedInteger('occurrence_number')->nullable();
            $table->index(['series_id', 'is_series_parent']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->dropForeign(['series_id']);
            $table->dropColumn(['series_id', 'is_series_parent', 'occurrence_number']);
        });
    }
};
