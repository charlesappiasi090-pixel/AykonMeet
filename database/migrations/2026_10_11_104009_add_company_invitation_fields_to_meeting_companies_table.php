<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('meeting_companies', function (Blueprint $table) {
            // Skip logo_path - already exists from 2026_10_10_000000_add_multi_company_support
            // Skip contact_name, contact_email, contact_phone, status - already exist from 2026_10_11_000000_add_meeting_details_dashboard
            // Skip invitation_token, invitation_token_hash, invitation_status, invitation_expires_at, invitation_revoked_at, invitation_last_used_at - already exist from 2026_10_15_000000_add_company_invitation_links_to_meeting_companies

            $table->string('email')->nullable()->after('contact_email');
            $table->string('phone')->nullable()->after('contact_phone');
            $table->string('contact_job_title')->nullable()->after('contact_phone');
            $table->string('contact_department')->nullable()->after('contact_job_title');
            $table->integer('expected_participants')->default(0)->after('contact_department');
            $table->text('company_agenda')->nullable()->after('expected_participants');

            // Use existing 'status' column from 2026_10_11_000000_add_meeting_details_dashboard as invitation_status

            // Additional invitation tracking fields not covered by existing migrations
            $table->timestamp('invitation_token_expires_at')->nullable()->after('notes');
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token_expires_at');
            $table->timestamp('invitation_responded_at')->nullable()->after('invitation_sent_at');
            $table->boolean('is_active')->default(true)->after('invitation_responded_at');

            // Invitation delivery tracking
            $table->enum('invitation_delivery_status', ['pending', 'sent', 'delivered', 'failed'])->default('pending')->after('is_active');
            $table->text('invitation_failure_reason')->nullable()->after('invitation_delivery_status');
            $table->timestamp('invitation_delivered_at')->nullable()->after('invitation_sent_at');
            $table->unsignedInteger('invitation_retry_count')->default(0)->after('invitation_delivered_at');
        });
    }

    public function down(): void
    {
        Schema::table('meeting_companies', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'phone',
                'contact_job_title',
                'contact_department',
                'expected_participants',
                'company_agenda',
                'invitation_token_expires_at',
                'invitation_sent_at',
                'invitation_responded_at',
                'is_active',
                'invitation_delivery_status',
                'invitation_failure_reason',
                'invitation_delivered_at',
                'invitation_retry_count',
            ]);
        });
    }
};
