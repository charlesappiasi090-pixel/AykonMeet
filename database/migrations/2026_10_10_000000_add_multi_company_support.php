<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->boolean('is_multi_company')->default(false)->after('meeting_type');
        });

        Schema::create('meeting_companies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('logo_path')->nullable();
            $table->boolean('is_host_company')->default(false);
            $table->timestamps();

            $table->unique(['meeting_id', 'slug']);
        });

        Schema::table('attendees', function (Blueprint $table) {
            $table->foreignId('meeting_company_id')->nullable()->after('meeting_id')->constrained()->nullOnDelete();
            $table->string('department')->nullable()->after('meeting_company_id');
        });
    }

    public function down(): void
    {
        Schema::table('attendees', function (Blueprint $table) {
            $table->dropConstrainedForeignId('meeting_company_id');
            $table->dropColumn('department');
        });

        Schema::dropIfExists('meeting_companies');

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('is_multi_company');
        });
    }
};
