<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreenShare extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_ENDED = 'ended';

    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_ENDED];

    public const TYPE_SCREEN = 'screen';

    public const TYPE_WINDOW = 'window';

    public const TYPE_TAB = 'tab';

    public const TYPES = [self::TYPE_SCREEN, self::TYPE_WINDOW, self::TYPE_TAB];

    protected $fillable = [
        'meeting_id',
        'participant_id',
        'share_type',
        'status',
        'started_at',
        'ended_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function end(): void
    {
        $this->update([
            'status' => self::STATUS_ENDED,
            'ended_at' => now(),
        ]);
    }
}
