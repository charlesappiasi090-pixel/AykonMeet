<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_memberships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('meeting_company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->string('full_name')->nullable();
            $table->string('department')->nullable();
            $table->string('role', 20)->default('member');
            $table->string('status', 20)->default('invited');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['meeting_company_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_memberships');
    }
};
