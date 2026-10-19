<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingException extends Model
{
    use HasFactory;

    protected $fillable = [
        'series_id', 'occurrence_number', 'meeting_id', 'exception_type', 'modified_fields',
        'original_start', 'original_end', 'new_start', 'new_end',
        'requires_approval', 'approval_status', 'approved_by', 'approved_at', 'approval_notes',
    ];

    protected function casts(): array
    {
        return [
            'occurrence_number' => 'integer',
            'modified_fields' => 'array',
            'original_start' => 'datetime',
            'original_end' => 'datetime',
            'new_start' => 'datetime',
            'new_end' => 'datetime',
            'requires_approval' => 'boolean',
            'approved_at' => 'datetime',
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPendingApproval(): bool
    {
        return $this->requires_approval && $this->approval_status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    public function approve(User $user, ?string $notes = null): void
    {
        $this->update([
            'approval_status' => 'approved',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // Apply the exception to the linked meeting
        if ($this->meeting_id) {
            $meeting = Meeting::find($this->meeting_id);
            if ($meeting) {
                if ($this->exception_type === 'cancelled') {
                    $meeting->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                } elseif ($this->new_start && $this->new_end) {
                    $meeting->update([
                        'start_date' => $this->new_start,
                        'end_date' => $this->new_end,
                    ]);
                }
            }
        }

        // Update occurrence status
        $occurrence = $this->series->occurrences()
            ->where('occurrence_number', $this->occurrence_number)
            ->first();
        if ($occurrence) {
            $occurrence->update(['status' => $this->exception_type === 'cancelled' ? 'cancelled' : 'modified']);
        }
    }

    public function reject(User $user, ?string $notes = null): void
    {
        $this->update([
            'approval_status' => 'rejected',
            'approved_by' => $user->id,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);

        // If there's a linked meeting that was modified, revert it
        if ($this->meeting_id && $this->original_start && $this->original_end) {
            $meeting = Meeting::find($this->meeting_id);
            if ($meeting) {
                $meeting->update([
                    'start_date' => $this->original_start,
                    'end_date' => $this->original_end,
                ]);
            }
        }
    }
}
