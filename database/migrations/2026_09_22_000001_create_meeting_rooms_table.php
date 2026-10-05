<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('number');
            $table->string('floor');
            $table->timestamps();

            $table->unique(['number', 'floor']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms');
    }
};
