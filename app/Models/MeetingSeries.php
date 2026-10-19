<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'meeting_topic', 'description', 'frequency', 'interval', 'days_of_week',
        'day_of_month', 'start_date', 'end_date', 'recurrence_end_date', 'max_occurrences',
        'occurrences_count', 'settings', 'is_active', 'host_user_id', 'timezone',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'datetime',
            'end_date' => 'datetime',
            'recurrence_end_date' => 'date',
            'interval' => 'integer',
            'max_occurrences' => 'integer',
            'occurrences_count' => 'integer',
            'days_of_week' => 'array',
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public const FREQUENCIES = ['daily', 'weekly', 'monthly', 'quarterly'];

    public function occurrences(): HasMany
    {
        return $this->hasMany(MeetingOccurrence::class)->orderBy('occurrence_number');
    }

    public function exceptions(): HasMany
    {
        return $this->hasMany(MeetingException::class)->orderBy('occurrence_number');
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id');
    }

    public function parentMeeting(): HasMany
    {
        return $this->hasMany(Meeting::class, 'series_id')->where('is_series_parent', true);
    }

    public function getFrequencyLabel(): string
    {
        return match ($this->frequency) {
            'daily' => 'Daily',
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly',
            default => ucfirst($this->frequency),
        };
    }

    public function getRecurrenceDescription(): string
    {
        $interval = $this->interval > 1 ? "every {$this->interval} " : 'every ';
        $base = match ($this->frequency) {
            'daily' => $interval.'day'.($this->interval > 1 ? 's' : ''),
            'weekly' => $interval.'week'.($this->interval > 1 ? 's' : ''),
            'monthly' => $interval.'month'.($this->interval > 1 ? 's' : ''),
            'quarterly' => $interval.'quarter'.($this->interval > 1 ? 's' : ''),
            default => $this->frequency,
        };

        if ($this->frequency === 'weekly' && $this->days_of_week) {
            $dayNames = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $days = collect($this->days_of_week)->map(fn ($d) => $dayNames[$d - 1] ?? $d)->implode(', ');
            $base .= " on {$days}";
        }

        if ($this->frequency === 'monthly' && $this->day_of_month) {
            $base .= " on day {$this->day_of_month}";
        }

        if ($this->frequency === 'quarterly' && $this->day_of_month) {
            $base .= " on day {$this->day_of_month} of each quarter";
        }

        return $base;
    }

    public function getEndConditionDescription(): string
    {
        if ($this->recurrence_end_date) {
            return "Ends on {$this->recurrence_end_date->format('M d, Y')}";
        }

        if ($this->max_occurrences) {
            return "Ends after {$this->max_occurrences} occurrences";
        }

        return 'Never ends';
    }

    public function calculateNextOccurrence(?Carbon $after = null): ?Carbon
    {
        $after = $after ?? $this->start_date;

        if ($this->recurrence_end_date && $after->gt($this->recurrence_end_date)) {
            return null;
        }

        if ($this->max_occurrences && $this->occurrences_count >= $this->max_occurrences) {
            return null;
        }

        $next = $after->copy();
        $interval = $this->interval;

        switch ($this->frequency) {
            case 'daily':
                $next->addDays($interval);
                break;
            case 'weekly':
                if ($this->days_of_week && count($this->days_of_week) > 0) {
                    $currentDayOfWeek = (int) $next->format('N'); // 1=Mon, 7=Sun
                    $validDays = collect($this->days_of_week)->sort()->values()->all();

                    // Find next valid day
                    $found = false;
                    for ($i = 0; $i < 7 * $interval; $i++) {
                        $dayOfWeek = (int) $next->format('N');
                        if (in_array($dayOfWeek, $validDays, true)) {
                            $found = true;
                            break;
                        }
                        $next->addDay();
                    }

                    if (! $found) {
                        // Move to next interval week
                        $next = $after->copy()->addWeeks($interval);
                        // Find first valid day in that week
                        for ($i = 0; $i < 7; $i++) {
                            $dayOfWeek = (int) $next->format('N');
                            if (in_array($dayOfWeek, $validDays, true)) {
                                $found = true;
                                break;
                            }
                            $next->addDay();
                        }
                    }
                } else {
                    $next->addWeeks($interval);
                }
                break;
            case 'monthly':
                $next->addMonths($interval);
                if ($this->day_of_month) {
                    $next->day($this->day_of_month > 28 ? 28 : $this->day_of_month);
                }
                break;
            case 'quarterly':
                $next->addMonths(3 * $interval);
                if ($this->day_of_month) {
                    $next->day($this->day_of_month > 28 ? 28 : $this->day_of_month);
                }
                break;
        }

        if ($this->recurrence_end_date && $next->gt($this->recurrence_end_date)) {
            return null;
        }

        return $next;
    }

    public function generateOccurrences(int $limit = 50): int
    {
        $generated = 0;
        $lastOccurrence = $this->occurrences()->latest('occurrence_number')->first();
        $nextNumber = ($lastOccurrence?->occurrence_number ?? 0) + 1;
        $nextStart = $lastOccurrence
            ? $this->calculateNextOccurrence(Carbon::parse($lastOccurrence->scheduled_start))
            : $this->start_date;

        while ($generated < $limit && $nextStart) {
            // Check if we've hit the end condition
            if ($this->recurrence_end_date && $nextStart->gt($this->recurrence_end_date)) {
                break;
            }

            if ($this->max_occurrences && $nextNumber > $this->max_occurrences) {
                break;
            }

            // Calculate end time based on duration of the series
            $duration = $this->start_date->diffInMinutes($this->end_date);
            $nextEnd = $nextStart->copy()->addMinutes($duration);

            // Check if exception exists for this occurrence
            $exception = $this->exceptions()
                ->where('occurrence_number', $nextNumber)
                ->first();

            if ($exception && $exception->exception_type === 'cancelled') {
                // Skip cancelled occurrences but still count them
                $nextNumber++;
                $nextStart = $this->calculateNextOccurrence($nextStart);

                continue;
            }

            // Create or update the occurrence record
            $occurrence = $this->occurrences()->updateOrCreate(
                ['occurrence_number' => $nextNumber],
                [
                    'scheduled_start' => $nextStart,
                    'scheduled_end' => $nextEnd,
                    'status' => $exception ? 'modified' : 'scheduled',
                ]
            );

            // If there's a meeting already linked, update it
            if ($occurrence->meeting_id) {
                $meeting = Meeting::find($occurrence->meeting_id);
                if ($meeting && ! $meeting->is_series_parent) {
                    $meeting->update([
                        'start_date' => $nextStart,
                        'end_date' => $nextEnd,
                    ]);
                }
            }

            $generated++;
            $nextNumber++;
            $nextStart = $this->calculateNextOccurrence($nextStart);
        }

        $this->increment('occurrences_count', $generated);

        return $generated;
    }

    public function getUpcomingOccurrences(int $limit = 10)
    {
        return $this->occurrences()
            ->where('status', '!=', 'cancelled')
            ->where('scheduled_start', '>=', now())
            ->orderBy('scheduled_start')
            ->limit($limit)
            ->get();
    }

    protected static function booted(): void
    {
        static::creating(function (MeetingSeries $series) {
            $series->timezone ??= config('app.timezone', 'UTC');
        });
    }
}
