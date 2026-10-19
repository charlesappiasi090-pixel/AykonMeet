<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineSessionParticipant extends Model
{
    use HasFactory;

    public const STATUS_WAITING = 'waiting';

    public const STATUS_ADMITTED = 'admitted';

    public const STATUS_REMOVED = 'removed';

    public const ROLE_HOST = 'host';

    public const ROLE_CO_HOST = 'co_host';

    public const ROLE_PARTICIPANT = 'participant';

    public const ROLE_LABELS = [
        self::ROLE_HOST => 'Host',
        self::ROLE_CO_HOST => 'Co-host',
        self::ROLE_PARTICIPANT => 'Participant',
    ];

    public const SOURCE_INVITE = 'invite';

    public const SOURCE_COMPANY_LINK = 'company_link';

    public const SOURCE_HOST = 'host';

    public const SOURCE_GUEST = 'guest';

    /**
     * Capabilities a host may delegate to a co-host.
     *
     * @var array<string, string>
     */
    public const CO_HOST_PERMISSIONS = [
        'mute_participants' => 'Mute and unmute participants',
        'manage_waiting_room' => 'Admit or reject people in the waiting room',
        'remove_participants' => 'Remove participants from the room',
        'promote_co_hosts' => 'Promote or demote co-hosts',
    ];

    protected $fillable = [
        'meeting_id',
        'attendee_id',
        'user_id',
        'company_id',
        'peer_id',
        'token_hash',
        'display_name',
        'role',
        'assignment_source',
        'company_verified_at',
        'is_host',
        'is_muted_by_host',
        'hand_raised',
        'status',
        'is_co_host',
        'co_host_permissions',
        'joined_at',
        'requested_at',
        'admitted_at',
        'removed_at',
        'left_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'is_host' => 'boolean',
            'is_muted_by_host' => 'boolean',
            'hand_raised' => 'boolean',
            'is_co_host' => 'boolean',
            'co_host_permissions' => 'array',
            'joined_at' => 'datetime',
            'requested_at' => 'datetime',
            'admitted_at' => 'datetime',
            'removed_at' => 'datetime',
            'left_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'company_verified_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(MeetingCompany::class, 'company_id');
    }

    public function roleLabel(): string
    {
        if ($this->isHost) {
            return self::ROLE_LABELS[self::ROLE_HOST];
        }

        if ($this->is_co_host) {
            return self::ROLE_LABELS[self::ROLE_CO_HOST];
        }

        return self::ROLE_LABELS[$this->role] ?? self::ROLE_LABELS[self::ROLE_PARTICIPANT];
    }

    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    public function isAdmitted(): bool
    {
        return $this->status === self::STATUS_ADMITTED;
    }

    public function isRemoved(): bool
    {
        return $this->status === self::STATUS_REMOVED;
    }

    public function isHost(): bool
    {
        return (bool) $this->is_host;
    }

    /**
     * Whether this participant may act as a host (host or promoted co-host).
     */
    public function actsAsHost(): bool
    {
        return $this->isHost() || (bool) $this->is_co_host;
    }

    /**
     * Whether the participant holds a delegated co-host capability.
     */
    public function hasCoHostPermission(string $permission): bool
    {
        if ($this->isHost()) {
            return true;
        }

        if (! $this->is_co_host) {
            return false;
        }

        return in_array($permission, $this->co_host_permissions ?? [], true);
    }
}
