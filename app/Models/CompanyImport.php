<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyImport extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'filename',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'duplicate_rows',
        'existing_rows',
        'created_count',
        'skipped_count',
        'failed_count',
        'status',
        'errors',
        'created_by',
        'imported_at',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
        'errors' => 'array',
    ];

    public const STATUSES = ['pending', 'processing', 'completed', 'failed'];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'processing' => 'Processing',
            'completed' => 'Completed',
            'failed' => 'Failed',
            default => ucfirst($this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'pending' => 'pending',
            'processing' => 'pending',
            'completed' => 'accepted',
            'failed' => 'declined',
            default => 'pending',
        };
    }

    public function successRate(): float
    {
        if ($this->total_rows === 0) {
            return 0;
        }

        return round(($this->created_count / $this->total_rows) * 100, 1);
    }
}

(End of file - total 69 lines)