<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'meeting_company_id',
        'email',
        'name',
        'token',
        'token_hash',
        'status',
        'expires_at',
        'sent_at',
        'responded_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'sent_at' => 'datetime',
            'responded_at' => 'datetime',
        ];
    }

    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_DECLINED = 'declined';

    public const STATUS_TENTATIVE = 'tentative';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_REVOKED = 'revoked';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_SENT,
        self::STATUS_ACCEPTED,
        self::STATUS_DECLINED,
        self::STATUS_TENTATIVE,
        self::STATUS_EXPIRED,
        self::STATUS_REVOKED,
    ];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class, 'meeting_company_id');
    }

    public function invitationUrl(): string
    {
        return route('invitations.show', $this->token);
    }

    public function isUsable(): bool
    {
        if (blank($this->token) || $this->status === self::STATUS_REVOKED) {
            return false;
        }

        if ($this->status === self::STATUS_EXPIRED) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    public function markSent(): void
    {
        $this->forceFill([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ])->save();
    }

    public function markDelivered(): void
    {
        if ($this->status !== self::STATUS_SENT) {
            return;
        }

        $this->forceFill([
            'status' => self::STATUS_SENT, // Keep as sent, but track delivery
        ])->save();
    }

    public function accept(): void
    {
        $this->forceFill([
            'status' => self::STATUS_ACCEPTED,
            'responded_at' => now(),
        ])->save();
    }

    public function decline(): void
    {
        $this->forceFill([
            'status' => self::STATUS_DECLINED,
            'responded_at' => now(),
        ])->save();
    }

    public function markTentative(): void
    {
        $this->forceFill([
            'status' => self::STATUS_TENTATIVE,
            'responded_at' => now(),
        ])->save();
    }

    public function revoke(): void
    {
        $this->forceFill([
            'status' => self::STATUS_REVOKED,
        ])->save();
    }
}
