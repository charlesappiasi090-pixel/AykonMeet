<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentAccessLog extends Model
{
    public const ACTION_UPLOAD = 'upload';

    public const ACTION_VIEW = 'view';

    public const ACTION_PREVIEW = 'preview';

    public const ACTION_DOWNLOAD = 'download';

    public const ACTION_SHARE_UPDATE = 'share_update';

    public const ACTION_REVOKE = 'revoke';

    public const ACTIONS = [
        self::ACTION_UPLOAD,
        self::ACTION_VIEW,
        self::ACTION_PREVIEW,
        self::ACTION_DOWNLOAD,
        self::ACTION_SHARE_UPDATE,
        self::ACTION_REVOKE,
    ];

    protected $fillable = [
        'document_share_id',
        'participant_id',
        'action',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
        ];
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentShare::class, 'document_share_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(OnlineSessionParticipant::class, 'participant_id');
    }
}
