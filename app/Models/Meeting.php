<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_code', 'name', 'email', 'phone', 'company_name', 'meeting_topic', 'description', 'topic_category',
        'start_date', 'end_date', 'from_location', 'to_location', 'meeting_room_id', 'min_attendees', 'physical_location',
        'status', 'notes', 'started_at', 'ended_at', 'cancelled_at', 'is_online',
        'session_provider', 'session_url', 'host_user_id', 'meeting_type', 'is_multi_company', 'settings',
        'series_id', 'is_series_parent', 'occurrence_number',
        'venue_name', 'venue_address', 'venue_building_room', 'venue_instructions', 'venue_capacity',
        'timezone', 'require_registration', 'allow_rsvp_maybe', 'allow_rsvp_another_time',
        'enable_document_sharing', 'allow_participant_upload',
    ];

    public const SETTINGS_DEFAULTS = [
        'password' => null,
        'waiting_room' => true,
        'waiting_room_message' => null,
        'mute_on_entry' => false,
        'allow_participant_unmute' => true,
        'allow_participant_camera' => true,
        'default_mic_on' => true,
        'default_camera_on' => true,
        'allow_screen_share' => true,
        'allow_chat' => true,
        'allow_participant_hand' => true,
        'co_host' => null,
        'co_host_permissions' => [],
        'allow_recording' => false,
        'max_participants' => null,
        'company_self_invite' => false,
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'is_online' => 'boolean',
            'is_multi_company' => 'boolean',
            'is_series_parent' => 'boolean',
            'settings' => 'array',
            'occurrence_number' => 'integer',
            'venue_capacity' => 'integer',
            'require_registration' => 'boolean',
            'allow_rsvp_maybe' => 'boolean',
            'allow_rsvp_another_time' => 'boolean',
            'enable_document_sharing' => 'boolean',
            'allow_participant_upload' => 'boolean',
        ];
    }

    public function isMultiCompany(): bool
    {
        return (bool) $this->is_multi_company;
    }

    /**
     * Per-meeting session configuration merged over the defaults.
     *
     * @return array<string, mixed>
     */
    public function sessionSettings(): array
    {
        return array_merge(self::SETTINGS_DEFAULTS, is_array($this->settings) ? $this->settings : []);
    }

    public function setting(string $key): mixed
    {
        return $this->sessionSettings()[$key] ?? null;
    }

    public static function durationLabel(\DateTimeInterface $start, \DateTimeInterface $end): string
    {
        $minutes = (int) $start->getTimestamp() === (int) $end->getTimestamp()
            ? 0
            : (int) ceil(($end->getTimestamp() - $start->getTimestamp()) / 60);

        if ($minutes <= 0) {
            return '—';
        }

        if ($minutes >= 60) {
            $hours = intdiv($minutes, 60);
            $mins = $minutes % 60;

            return $hours.'h'.($mins > 0 ? ' '.$mins.'m' : '');
        }

        return $minutes.' min';
    }

    /**
     * Capabilities delegated to a co-host for this meeting.
     *
     * @return array<int, string>
     */
    public function coHostPermissions(): array
    {
        $permissions = $this->setting('co_host_permissions');

        if (! is_array($permissions)) {
            return [];
        }

        $allowed = array_keys(OnlineSessionParticipant::CO_HOST_PERMISSIONS);

        return array_values(array_intersect($allowed, $permissions));
    }

    /**
     * The permission flags exposed to a regular participant in the live room.
     *
     * @return array<string, bool>
     */
    public function participantPermissions(): array
    {
        return [
            'chat' => (bool) $this->setting('allow_chat'),
            'unmute' => (bool) $this->setting('allow_participant_unmute'),
            'camera' => (bool) $this->setting('allow_participant_camera'),
            'screen_share' => (bool) $this->setting('allow_screen_share'),
            'hand' => (bool) $this->setting('allow_participant_hand'),
            'record' => (bool) $this->setting('allow_recording'),
            'mute_on_entry' => (bool) $this->setting('mute_on_entry'),
        ];
    }

    /**
     * Human friendly status used by the meeting details dashboard.
     */
    public function dashboardStatus(): string
    {
        if ($this->isCancelled()) {
            return 'cancelled';
        }
        if (in_array($this->status, ['completed'], true) || $this->ended_at !== null) {
            return 'completed';
        }
        if ($this->status === 'in_progress' || $this->started_at !== null) {
            return 'live';
        }
        if ($this->start_date !== null && $this->start_date->lte(now()->addMinutes(15))) {
            return 'waiting';
        }

        return 'scheduled';
    }

    public function canStart(): bool
    {
        return ! in_array($this->dashboardStatus(), ['cancelled', 'completed', 'live'], true);
    }

    /**
     * Whether an invited participant may enter the online room yet.
     *
     * Returns "closed" (ended/cancelled), "waiting" (host has not started and
     * the waiting room is enabled) or "open" (entry permitted).
     */
    public function inviteeJoinState(): string
    {
        if (! $this->is_online || $this->isCancelled() || $this->ended_at !== null
            || in_array($this->status, ['completed'], true)) {
            return 'closed';
        }

        if ($this->status === 'in_progress' || $this->started_at !== null) {
            return 'open';
        }

        return $this->setting('waiting_room') ? 'waiting' : 'open';
    }

    public function isWaitingForHost(): bool
    {
        return $this->inviteeJoinState() === 'waiting';
    }

    public function joinUrl(): ?string
    {
        if ($this->is_online) {
            return $this->session_url ?: URL::signedRoute('sessions.join', ['meeting' => $this]);
        }

        return route('meetings.show', $this->meeting_code);
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isLive(): bool
    {
        return $this->status === 'in_progress' || $this->started_at !== null;
    }

    public function hasEnded(): bool
    {
        return in_array($this->status, ['completed'], true) || $this->ended_at !== null;
    }

    public function isHybrid(): bool
    {
        return $this->meeting_type === 'hybrid'
            || ($this->meeting_type === null && $this->is_online && $this->meeting_room_id !== null);
    }

    protected static function booted(): void
    {
        static::creating(function (Meeting $meeting) {
            $meeting->meeting_code ??= 'AYK-'.strtoupper(Str::random(6));
        });
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'meeting_room_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }

    public function companies(): HasMany
    {
        return $this->hasMany(MeetingCompany::class);
    }

    /**
     * Get the host company for this meeting.
     */
    public function hostCompany(): HasMany
    {
        return $this->hasMany(MeetingCompany::class)->where('is_host_company', true);
    }

    /**
     * Check if the meeting is locked.
     */
    public function isLocked(): bool
    {
        return $this->locked_at !== null;
    }

    /**
     * Lock the meeting.
     */
    public function lock(?User $user = null): void
    {
        $this->update([
            'locked_at' => now(),
            'locked_by' => $user?->id,
        ]);
    }

    /**
     * Unlock the meeting.
     */
    public function unlock(): void
    {
        $this->update([
            'locked_at' => null,
            'locked_by' => null,
        ]);
    }

    /**
     * Get the user who locked the meeting.
     */
    public function lockedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(OnlineSessionParticipant::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(MeetingSeries::class);
    }

    public function occurrence(): HasMany
    {
        return $this->hasMany(MeetingOccurrence::class);
    }

    public function isSeriesParent(): bool
    {
        return (bool) $this->is_series_parent;
    }

    public function isOccurrence(): bool
    {
        return $this->series_id !== null && ! $this->is_series_parent;
    }

    /**
     * Get the full physical venue address for display.
     */
    public function getFullVenueAddress(): ?string
    {
        $parts = [];
        if ($this->venue_name) {
            $parts[] = $this->venue_name;
        }
        if ($this->venue_address) {
            $parts[] = $this->venue_address;
        }
        if ($this->venue_building_room) {
            $parts[] = $this->venue_building_room;
        }

        return $parts ? implode(', ', $parts) : null;
    }

    /**
     * Check if venue has unlimited capacity.
     */
    public function hasUnlimitedVenueCapacity(): bool
    {
        return $this->venue_capacity === null || $this->venue_capacity === 0;
    }

    /**
     * Get effective venue capacity (null = unlimited).
     */
    public function getEffectiveVenueCapacity(): ?int
    {
        return $this->hasUnlimitedVenueCapacity() ? null : $this->venue_capacity;
    }

    /**
     * Get RSVP options available for this meeting.
     */
    public function getRsvpOptions(): array
    {
        $options = [
            'accepted' => 'Accept',
            'declined' => 'Decline',
        ];

        if ($this->allow_rsvp_maybe) {
            $options['maybe'] = 'Maybe';
        }

        if ($this->allow_rsvp_another_time) {
            $options['another_time'] = 'Request Another Time';
        }

        return $options;
    }
}
