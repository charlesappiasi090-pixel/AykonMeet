<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParticipantApproval extends Model
{
    public const TYPE_GUEST = 'guest';

    public const TYPE_MISMATCH = 'mismatch';

    public const TYPE_COMPANY_LINK = 'company_link';

    public const TYPES = [
        self::TYPE_GUEST,
        self::TYPE_MISMATCH,
        self::TYPE_COMPANY_LINK,
    ];

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    protected $fillable = [
        'meeting_id',
        'attendee_id',
        'online_session_participant_id',
        'requested_company_id',
        'current_company_id',
        'type',
        'status',
        'email',
        'display_name',
        'requested_by_user_id',
        'decided_by_user_id',
        'decided_at',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'decided_at' => 'datetime',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function attendee(): BelongsTo
    {
        return $this->belongsTo(Attendee::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class, 'online_session_participant_id');
    }

    public function requestedCompany(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class, 'requested_company_id');
    }

    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class, 'current_company_id');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isMismatch(): bool
    {
        return $this->type === self::TYPE_MISMATCH;
    }

    public function markApproved(?User $by = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_APPROVED,
            'decided_by_user_id' => $by?->id,
            'decided_at' => now(),
        ])->save();
    }

    public function markRejected(?User $by = null, ?string $note = null): void
    {
        $this->forceFill([
            'status' => self::STATUS_REJECTED,
            'decided_by_user_id' => $by?->id,
            'decided_at' => now(),
            'note' => $note ?? $this->note,
        ])->save();
    }
}
