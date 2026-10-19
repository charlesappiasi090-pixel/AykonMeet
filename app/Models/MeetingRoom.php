<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'number', 'floor', 'location'];

    public function meetings(): HasMany
    {
        return $this->hasMany(Meeting::class);
    }
}
