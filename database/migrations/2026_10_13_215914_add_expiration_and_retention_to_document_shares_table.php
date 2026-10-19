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
        Schema::table('document_shares', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable()->after('revoked_by');
            $table->enum('retention_policy', ['keep', 'revoke_on_end', 'delete_on_end'])->default('keep')->after('expires_at');
            $table->boolean('encrypted_e2e')->default(false)->after('retention_policy');
            $table->string('encryption_key_id')->nullable()->after('encrypted_e2e');
            $table->index(['meeting_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('document_shares', function (Blueprint $table) {
            $table->dropColumn(['expires_at', 'retention_policy', 'encrypted_e2e', 'encryption_key_id']);
        });
    }
};
