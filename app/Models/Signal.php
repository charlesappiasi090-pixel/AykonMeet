<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signal extends Model
{
    protected $fillable = ['meeting_id', 'from_peer', 'to_peer', 'type', 'payload'];

    protected $casts = ['payload' => 'array'];

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }
}
