<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meetings', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('is_multi_company');
        });

        Schema::table('meeting_companies', function (Blueprint $table) {
            $table->string('contact_name')->nullable()->after('logo_path');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
            $table->string('status')->default('pending')->after('is_host_company');
            $table->timestamp('invited_at')->nullable()->after('status');
            $table->text('notes')->nullable()->after('invited_at');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_companies', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name',
                'contact_email',
                'contact_phone',
                'status',
                'invited_at',
                'notes',
            ]);
        });

        Schema::table('meetings', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
    }
};
