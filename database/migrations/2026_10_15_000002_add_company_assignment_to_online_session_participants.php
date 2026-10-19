<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('online_session_participants', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('attendee_id')->constrained()->nullOnDelete();
            $table->foreignId('company_id')->nullable()->after('user_id')->constrained('meeting_companies')->nullOnDelete();
            $table->string('role', 20)->default('participant')->after('display_name');
            $table->string('assignment_source', 20)->nullable()->after('role');
            $table->timestamp('company_verified_at')->nullable()->after('assignment_source');
        });
    }

    public function down(): void
    {
        Schema::table('online_session_participants', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropConstrainedForeignId('company_id');
            $table->dropColumn(['role', 'assignment_source', 'company_verified_at']);
        });
    }
};
