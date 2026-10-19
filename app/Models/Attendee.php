<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Attendee extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'meeting_company_id',
        'department',
        'attendee_code',
        'full_name',
        'email',
        'phone',
        'checked_in_at',
        'checked_out_at',
        'attendance_mode',
        'invitation_token',
        'invitation_status',
        'invitation_responded_at',
        'invitation_expires_at',
        'invitation_sent_at',
        'invitation_delivered_at',
        'invitation_verified_at',
        'reminder_sent_at',
        'requires_approval',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'checked_out_at' => 'datetime',
            'invitation_responded_at' => 'datetime',
            'invitation_expires_at' => 'datetime',
            'invitation_sent_at' => 'datetime',
            'invitation_delivered_at' => 'datetime',
            'invitation_verified_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
            'requires_approval' => 'boolean',
        ];
    }

    public const INVITATION_STATUSES = ['pending', 'draft', 'sent', 'delivered', 'accepted', 'declined'];

    public const INVITATION_STATES = ['draft', 'sent', 'delivered', 'accepted', 'declined', 'expired', 'cancelled'];

    protected static function booted(): void
    {
        static::creating(function (Attendee $attendee) {
            $attendee->attendee_code ??= 'AT-'.strtoupper(Str::random(6));
            $attendee->invitation_token ??= self::generateInvitationToken();
        });
    }

    public static function generateInvitationToken(): string
    {
        return Str::random(64);
    }

    public function ensureInvitationToken(): string
    {
        if (! $this->invitation_token) {
            $this->forceFill(['invitation_token' => self::generateInvitationToken()])->save();
        }

        return $this->invitation_token;
    }

    public function invitationUrl(): string
    {
        $this->ensureInvitationToken();

        return route('invitations.show', ['attendee' => $this->invitation_token]);
    }

    public function invitationStatus(): string
    {
        return in_array($this->invitation_status, self::INVITATION_STATUSES, true)
            ? $this->invitation_status
            : 'pending';
    }

    public function invitationStatusLabel(): string
    {
        return match ($this->invitationStatus()) {
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            default => 'Pending',
        };
    }

    /**
     * The lifecycle state shown on the organizer dashboard. Draft/Sent/Delivered
     * come from delivery tracking, while Expired/Cancelled are derived from the
     * meeting so statuses stay accurate without a background job.
     */
    public function invitationState(): string
    {
        if ($this->meeting?->isCancelled()) {
            return 'cancelled';
        }

        $status = $this->invitationStatus();
        if (in_array($status, ['accepted', 'declined'], true)) {
            return $status;
        }

        if ($this->invitation_expires_at && $this->invitation_expires_at->isPast()) {
            return 'expired';
        }

        if ($this->meeting?->end_date?->isPast()) {
            return 'expired';
        }

        if ($this->invitation_delivered_at !== null) {
            return 'delivered';
        }

        if ($this->invitation_sent_at !== null || in_array($status, ['sent', 'delivered'], true)) {
            return 'sent';
        }

        return 'draft';
    }

    public function invitationStateLabel(): string
    {
        return match ($this->invitationState()) {
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'expired' => 'Expired',
            'cancelled' => 'Cancelled',
            default => 'Draft',
        };
    }

    public function invitationStateTone(): string
    {
        return match ($this->invitationState()) {
            'accepted' => 'accepted',
            'declined' => 'declined',
            'cancelled' => 'declined',
            'expired' => 'pending',
            'sent', 'delivered' => 'sent',
            default => 'pending',
        };
    }

    public function markSent(): void
    {
        if ($this->invitation_sent_at !== null) {
            return;
        }

        $attributes = ['invitation_sent_at' => now()];

        if (! in_array($this->invitationStatus(), ['accepted', 'declined'], true)) {
            $attributes['invitation_status'] = 'sent';
        }

        $this->forceFill($attributes)->save();
    }

    public function markDelivered(): void
    {
        if ($this->invitation_delivered_at !== null) {
            return;
        }

        $attributes = ['invitation_delivered_at' => now()];

        if (in_array($this->invitationStatus(), ['pending', 'draft', 'sent'], true)) {
            $attributes['invitation_status'] = 'delivered';

            if ($this->invitation_sent_at === null) {
                $attributes['invitation_sent_at'] = now();
            }
        }

        $this->forceFill($attributes)->save();
    }

    public function markVerified(): void
    {
        if ($this->invitation_verified_at !== null) {
            return;
        }

        $this->forceFill(['invitation_verified_at' => now()])->save();
    }

    public function isRegisteredRecipient(): bool
    {
        return User::where('email', $this->email)->exists();
    }

    public function isInvitationExpired(): bool
    {
        if ($this->invitation_expires_at && $this->invitation_expires_at->isPast()) {
            return true;
        }

        $meeting = $this->meeting;

        return $meeting !== null
            && ($meeting->isCancelled() || $meeting->end_date?->isPast() === true);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class, 'meeting_company_id');
    }

    public function companyName(): string
    {
        return $this->meetingCompany?->name ?? 'Guests';
    }

    public function invitingCompanyName(): string
    {
        $meeting = $this->meeting;

        if (! $meeting) {
            return 'AykonMeet host';
        }

        $hostCompany = $meeting->companies()
            ->where('is_host_company', true)
            ->value('name');

        return $hostCompany ?: ($meeting->company_name ?: $meeting->name);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function meetingCompany(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class);
    }
}
