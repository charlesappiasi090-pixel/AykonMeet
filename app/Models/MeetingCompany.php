<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class MeetingCompany extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_id',
        'name',
        'slug',
        'logo_path',
        'email',
        'phone',
        'contact_name',
        'contact_email',
        'contact_phone',
        'contact_job_title',
        'contact_department',
        'expected_participants',
        'company_agenda',
        'status',
        'invitation_status',
        'invitation_token',
        'invitation_token_hash',
        'invitation_expires_at',
        'invitation_revoked_at',
        'invitation_last_used_at',
        'invitation_token_expires_at',
        'invitation_sent_at',
        'invitation_responded_at',
        'is_active',
        'invitation_delivery_status',
        'invitation_failure_reason',
        'invitation_sent_at',
        'invitation_delivered_at',
        'invitation_retry_count',
        'invited_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'is_host_company' => 'boolean',
        'is_active' => 'boolean',
        'invited_at' => 'datetime',
        'invitation_expires_at' => 'datetime',
        'invitation_revoked_at' => 'datetime',
        'invitation_last_used_at' => 'datetime',
        'invitation_token_expires_at' => 'datetime',
        'invitation_sent_at' => 'datetime',
        'invitation_responded_at' => 'datetime',
        'invitation_delivered_at' => 'datetime',
        'invitation_retry_count' => 'integer',
    ];

    public const STATUSES = ['pending', 'accepted', 'declined'];

    public const INVITATION_STATUSES = ['pending', 'sent', 'accepted', 'declined', 'tentative'];

    public const INVITATION_DELIVERY_STATUSES = ['pending', 'sent', 'delivered', 'failed'];

    public function statusLabel(): string
    {
        return match ($this->status) {
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            default => 'Pending',
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'accepted' => 'accepted',
            'declined' => 'declined',
            default => 'pending',
        };
    }

    public function initial(): string
    {
        return strtoupper(substr(trim((string) $this->name) ?: 'C', 0, 1));
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    public function primaryContact(): HasMany
    {
        return $this->hasMany(CompanyContact::class)->where('is_primary', true);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(MeetingInvitation::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function invitationUrl(): ?string
    {
        if (blank($this->invitation_token)) {
            return null;
        }

        return route('company-invitations.show', $this->invitation_token);
    }

    public function isInvitationUsable(): bool
    {
        if (blank($this->invitation_token) || $this->invitation_revoked_at !== null) {
            return false;
        }

        if (in_array($this->invitation_status, ['cancelled', 'expired'], true)) {
            return false;
        }

        return $this->invitation_expires_at === null || $this->invitation_expires_at->isFuture();
    }

    public function invitationState(): string
    {
        if (blank($this->invitation_token)) {
            return 'draft';
        }

        if ($this->invitation_revoked_at !== null || $this->invitation_status === 'cancelled') {
            return 'cancelled';
        }

        if ($this->invitation_expires_at !== null && $this->invitation_expires_at->isPast()) {
            return 'expired';
        }

        if ($this->invitation_last_used_at !== null) {
            return 'delivered';
        }

        return in_array($this->invitation_status, ['sent', 'delivered'], true)
            ? $this->invitation_status
            : 'draft';
    }

    public function invitationLabel(): string
    {
        return match ($this->invitationState()) {
            'sent' => 'Link active',
            'delivered' => 'Link opened',
            'cancelled' => 'Link revoked',
            'expired' => 'Link expired',
            default => 'No link yet',
        };
    }

    public function invitationTone(): string
    {
        return match ($this->invitationState()) {
            'sent', 'delivered' => 'sent',
            'cancelled', 'expired' => 'declined',
            default => 'pending',
        };
    }

    /**
     * Aggregate invitation state from all attendees of this company.
     */
    public function aggregateInvitationState(): string
    {
        $states = $this->attendees->map(fn (Attendee $attendee) => $attendee->invitationState());

        if ($states->isEmpty()) {
            return $this->invited_at !== null ? 'sent' : 'draft';
        }

        if ($states->contains('accepted')) {
            return 'accepted';
        }

        if ($states->contains('delivered')) {
            return 'delivered';
        }

        if ($states->contains('sent')) {
            return 'sent';
        }

        if ($states->every(fn (string $state) => $state === 'declined')) {
            return 'declined';
        }

        if ($states->contains('cancelled')) {
            return 'cancelled';
        }

        return 'draft';
    }

    public function aggregateInvitationLabel(): string
    {
        return match ($this->aggregateInvitationState()) {
            'sent' => 'Sent',
            'delivered' => 'Delivered',
            'accepted' => 'Accepted',
            'declined' => 'Declined',
            'cancelled' => 'Cancelled',
            default => 'Draft',
        };
    }

    public function aggregateInvitationTone(): string
    {
        return match ($this->aggregateInvitationState()) {
            'accepted' => 'accepted',
            'declined', 'cancelled' => 'declined',
            'sent', 'delivered' => 'sent',
            default => 'pending',
        };
    }

    public static function forName(Meeting $meeting, ?string $name, bool $isHost = false): self
    {
        $name = trim((string) $name);
        if ($name === '') {
            $name = $isHost ? 'Host company' : 'Guests';
        }
        $slug = Str::slug($name) ?: 'company';

        return static::firstOrCreate(
            ['meeting_id' => $meeting->id, 'slug' => $slug],
            ['name' => $name, 'is_host_company' => $isHost],
        );
    }

    public static function findByInvitationToken(string $token): ?self
    {
        $tokenHash = hash('sha256', $token);

        return static::where('invitation_token_hash', $tokenHash)
            ->orWhere('invitation_token', $token)
            ->first();
    }

    public function issueInvitation(?\DateTimeInterface $expiresAt = null): string
    {
        $token = Str::random(48);

        $this->forceFill([
            'invitation_token' => $token,
            'invitation_token_hash' => hash('sha256', $token),
            'invitation_status' => 'sent',
            'invitation_expires_at' => $expiresAt
                ? Carbon::instance($expiresAt)
                : now()->addDays(30),
            'invitation_revoked_at' => null,
        ])->save();

        if ($this->invited_at === null) {
            $this->forceFill(['invited_at' => now()])->save();
        }

        return $token;
    }

    public function revokeInvitation(): void
    {
        $this->forceFill([
            'invitation_status' => 'cancelled',
            'invitation_revoked_at' => now(),
        ])->save();
    }

    public function markInvitationUsed(): void
    {
        if ($this->invitation_token === null || $this->invitation_revoked_at !== null) {
            return;
        }

        $this->forceFill([
            'invitation_status' => 'delivered',
            'invitation_last_used_at' => now(),
        ])->save();
    }
}
