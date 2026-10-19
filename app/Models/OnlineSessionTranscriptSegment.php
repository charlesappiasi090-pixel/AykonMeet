<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineSessionTranscriptSegment extends Model
{
    protected $fillable = [
        'meeting_id',
        'participant_id',
        'text',
        'spoken_at',
    ];

    protected function casts(): array
    {
        return ['spoken_at' => 'datetime'];
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
