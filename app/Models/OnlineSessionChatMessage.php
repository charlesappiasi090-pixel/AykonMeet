<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineSessionChatMessage extends Model
{
    protected $fillable = ['meeting_id', 'participant_id', 'message'];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class);
    }
}
