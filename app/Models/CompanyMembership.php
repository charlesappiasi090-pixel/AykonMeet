<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyMembership extends Model
{
    public const ROLE_MEMBER = 'member';

    public const ROLE_REPRESENTATIVE = 'representative';

    public const ROLE_ADMIN = 'admin';

    public const ROLES = [
        self::ROLE_MEMBER,
        self::ROLE_REPRESENTATIVE,
        self::ROLE_ADMIN,
    ];

    public const STATUSES = ['invited', 'verified', 'revoked'];

    protected $fillable = [
        'meeting_company_id',
        'user_id',
        'email',
        'full_name',
        'department',
        'role',
        'status',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
    }

    public function meetingCompany(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function markVerified(?User $user = null): void
    {
        $this->forceFill([
            'status' => 'verified',
            'verified_at' => now(),
            'user_id' => $user?->id ?? $this->user_id,
        ])->save();
    }
}
