<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DocumentShare extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_REVOKED = 'revoked';

    public const STATUS_DELETED = 'deleted';

    public const STATUSES = [self::STATUS_ACTIVE, self::STATUS_REVOKED, self::STATUS_DELETED];

    public const SCOPE_ALL = 'all';

    public const SCOPE_COMPANIES = 'companies';

    public const SCOPE_INDIVIDUALS = 'individuals';

    public const SCOPES = [self::SCOPE_ALL, self::SCOPE_COMPANIES, self::SCOPE_INDIVIDUALS];

    public const RETENTION_KEEP = 'keep';

    public const RETENTION_REVOKE_ON_END = 'revoke_on_end';

    public const RETENTION_DELETE_ON_END = 'delete_on_end';

    public const RETENTION_POLICIES = [self::RETENTION_KEEP, self::RETENTION_REVOKE_ON_END, self::RETENTION_DELETE_ON_END];

    protected $fillable = [
        'meeting_id',
        'uploader_id',
        'original_name',
        'stored_name',
        'mime_type',
        'extension',
        'file_size',
        'storage_path',
        'storage_disk',
        'share_scope',
        'shared_with_companies',
        'shared_with_participants',
        'allow_download',
        'allow_preview',
        'status',
        'revoked_at',
        'revoked_by',
        'expires_at',
        'retention_policy',
        'encrypted_e2e',
        'encryption_key_id',
    ];

    protected function casts(): array
    {
        return [
            'shared_with_companies' => 'array',
            'shared_with_participants' => 'array',
            'allow_download' => 'boolean',
            'allow_preview' => 'boolean',
            'revoked_at' => 'datetime',
            'expires_at' => 'datetime',
            'file_size' => 'integer',
            'encrypted_e2e' => 'boolean',
        ];
    }

    public function meeting(): BelongsTo
    {
        return $this->belongsTo(Meeting::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class, 'uploader_id');
    }

    public function revokedBy(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class, 'revoked_by');
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(DocumentAccessLog::class, 'document_share_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return Carbon::parse($this->expires_at)->isPast();
    }

    public function isAccessible(): bool
    {
        return $this->isActive() && ! $this->isExpired();
    }

    public function isAccessibleBy(OnlineSessionParticipant $participant): bool
    {
        if (! $this->isAccessible()) {
            return false;
        }

        // Ensure participant belongs to this meeting
        if ($participant->meeting_id !== $this->meeting_id) {
            return false;
        }

        // Uploader always has access
        if ($this->uploader_id === $participant->id) {
            return true;
        }

        // Host and co-hosts have access
        if ($participant->actsAsHost()) {
            return true;
        }

        // Check share scope
        switch ($this->share_scope) {
            case self::SCOPE_ALL:
                return true;

            case self::SCOPE_COMPANIES:
                if ($participant->company_id && $this->shared_with_companies) {
                    // Verify the company is part of this meeting
                    $validCompanyIds = $this->meeting->companies()->pluck('id')->toArray();
                    $allowedCompanyIds = array_intersect($this->shared_with_companies, $validCompanyIds);

                    return in_array($participant->company_id, $allowedCompanyIds, true);
                }

                return false;

            case self::SCOPE_INDIVIDUALS:
                if ($this->shared_with_participants) {
                    // Verify the participants are part of this meeting
                    $validParticipantIds = $this->meeting->participants()->pluck('id')->toArray();
                    $allowedParticipantIds = array_intersect($this->shared_with_participants, $validParticipantIds);

                    return in_array($participant->id, $allowedParticipantIds, true);
                }

                return false;

            default:
                return false;
        }
    }

    public function canDownload(OnlineSessionParticipant $participant): bool
    {
        return $this->allow_download && $this->isAccessibleBy($participant);
    }

    public function canPreview(OnlineSessionParticipant $participant): bool
    {
        return $this->allow_preview && $this->isAccessibleBy($participant);
    }

    public function revoke(?OnlineSessionParticipant $by = null): void
    {
        $this->update([
            'status' => self::STATUS_REVOKED,
            'revoked_at' => now(),
            'revoked_by' => $by?->id,
        ]);
    }

    public function applyRetentionPolicy(): void
    {
        if (! $this->isAccessible()) {
            return;
        }

        switch ($this->retention_policy) {
            case self::RETENTION_REVOKE_ON_END:
                $this->revoke();
                break;

            case self::RETENTION_DELETE_ON_END:
                $this->revoke();
                $this->deleteFile();
                $this->update(['status' => self::STATUS_DELETED]);
                break;

            default:
                // keep - do nothing
                break;
        }
    }

    public function deleteFile(): bool
    {
        try {
            $disk = Storage::disk($this->storage_disk);
            if ($disk->exists($this->storage_path)) {
                $disk->delete($this->storage_path);
            }

            return true;
        } catch (\Throwable $e) {
            Log::warning('Failed to delete document file: '.$e->getMessage());

            return false;
        }
    }

    public function getPreviewUrl(): ?string
    {
        if (! $this->allow_preview) {
            return null;
        }

        return route('documents.preview', ['meeting' => $this->meeting_id, 'document' => $this->id]);
    }

    public function getDownloadUrl(): ?string
    {
        if (! $this->allow_download) {
            return null;
        }

        return route('documents.download', ['meeting' => $this->meeting_id, 'document' => $this->id]);
    }

    /**
     * Check if the document can be managed by the given participant.
     * Only uploader, host, or co-host with remove_participants permission can manage.
     */
    public function canManage(OnlineSessionParticipant $participant): bool
    {
        if ($this->uploader_id === $participant->id) {
            return true;
        }

        if ($participant->actsAsHost()) {
            return true;
        }

        if ($participant->is_co_host && $participant->hasCoHostPermission('remove_participants')) {
            return true;
        }

        return false;
    }
}
