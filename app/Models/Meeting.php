<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Meeting extends Model
{
    use HasFactory;

    protected $fillable = [
        'meeting_code', 'name', 'email', 'phone', 'company_name', 'meeting_topic',
        'start_date', 'end_date', 'from_location', 'to_location', 'meeting_room_id',
        'status', 'notes', 'started_at', 'ended_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
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
}
