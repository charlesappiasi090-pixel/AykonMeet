<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingOccurrence extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id', 'meeting_id', 'occurrence_number', 'scheduled_start', 'scheduled_end', 'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_start' => 'datetime',
            'scheduled_end' => 'datetime',
            'occurrence_number' => 'integer',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(MeetingSeries::class);
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function exception(): ?MeetingException
    {
        return $this->series->exceptions()
            ->where('occurrence_number', $this->occurrence_number)
            ->first();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled'
            || ($this->exception && $this->exception->exception_type === 'cancelled');
    }

    public function isModified(): bool
    {
        return $this->status === 'modified' || $this->exception !== null;
    }

    public function getEffectiveStart(): Carbon
    {
        if ($this->exception && in_array($this->exception->exception_type, ['modified', 'rescheduled'], true)
            && $this->exception->new_start) {
            return Carbon::parse($this->exception->new_start);
        }

        return Carbon::parse($this->scheduled_start);
    }

    public function getEffectiveEnd(): Carbon
    {
        if ($this->exception && in_array($this->exception->exception_type, ['modified', 'rescheduled'], true)
            && $this->exception->new_end) {
            return Carbon::parse($this->exception->new_end);
        }

        return Carbon::parse($this->scheduled_end);
    }

    public function linkMeeting(Meeting $meeting): void
    {
        $this->update([
            'meeting_id' => $meeting->id,
            'status' => 'generated',
        ]);
    }

    public function unlinkMeeting(): void
    {
        $this->update([
            'meeting_id' => null,
            'status' => 'scheduled',
        ]);
    }
}
