<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OnlineSessionSetting extends Model
{
    protected $fillable = [
        'recording_enabled',
        'allow_attendee_recording',
        'downloads_for_hosts',
    ];

    protected function casts(): array
    {
        return [
            'recording_enabled' => 'boolean',
            'allow_attendee_recording' => 'boolean',
            'downloads_for_hosts' => 'boolean',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1], [
            'recording_enabled' => true,
            'allow_attendee_recording' => true,
            'downloads_for_hosts' => false,
        ]);
    }
}
