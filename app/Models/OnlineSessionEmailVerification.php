<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineSessionEmailVerification extends Model
{
    protected $fillable = [
        'meeting_id',
        'attendee_id',
        'code_hash',
        'expires_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }
}
