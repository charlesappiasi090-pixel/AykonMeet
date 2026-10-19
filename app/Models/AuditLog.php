<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(
        string $action,
        ?Model $auditable = null,
        array $oldValues = [],
        array $newValues = [],
        array $metadata = []
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => $metadata ?: null,
        ]);
    }

    public static function getActionDescriptions(): array
    {
        return [
            'user.login' => 'User logged in',
            'user.login_failed' => 'User login failed',
            'user.logout' => 'User logged out',
            'user.created' => 'User created',
            'user.updated' => 'User updated',
            'user.deleted' => 'User deleted',
            'user.password_changed' => 'Password changed',
            'user.two_factor_enabled' => 'Two-factor authentication enabled',
            'user.two_factor_disabled' => 'Two-factor authentication disabled',
            'meeting.created' => 'Meeting created',
            'meeting.updated' => 'Meeting updated',
            'meeting.deleted' => 'Meeting deleted',
            'meeting.started' => 'Meeting started',
            'meeting.ended' => 'Meeting ended',
            'meeting.cancelled' => 'Meeting cancelled',
            'meeting.locked' => 'Meeting locked',
            'meeting.unlocked' => 'Meeting unlocked',
            'meeting.rescheduled' => 'Meeting rescheduled',
            'participant.joined' => 'Participant joined meeting',
            'participant.left' => 'Participant left meeting',
            'participant.removed' => 'Participant removed',
            'participant.muted' => 'Participant muted',
            'participant.unmuted' => 'Participant unmuted',
            'participant.promoted' => 'Participant promoted to co-host',
            'participant.demoted' => 'Co-host demoted',
            'document.uploaded' => 'Document uploaded',
            'document.downloaded' => 'Document downloaded',
            'document.previewed' => 'Document previewed',
            'document.shared' => 'Document sharing updated',
            'document.revoked' => 'Document revoked',
            'document.edited' => 'Document sharing updated',
            'recording.started' => 'Recording started',
            'recording.stopped' => 'Recording stopped',
            'recording.deleted' => 'Recording deleted',
            'settings.updated' => 'Settings updated',
            'recording_permissions.changed' => 'Recording permissions changed',
        ];
    }
}
