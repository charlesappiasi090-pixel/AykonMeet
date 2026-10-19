<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'logo_path',
        'email',
        'phone',
        'website',
        'registration_number',
        'company_type',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
        'status',
        'import_source',
        'imported_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        'imported_at' => 'datetime',
    ];

    public const STATUSES = ['active', 'inactive', 'pending_approval', 'suspended'];

    public const COMPANY_TYPES = [
        'corporation' => 'Corporation',
        'llc' => 'LLC',
        'partnership' => 'Partnership',
        'sole_proprietorship' => 'Sole Proprietorship',
        'non_profit' => 'Non-Profit',
        'government' => 'Government',
        'educational' => 'Educational Institution',
        'other' => 'Other',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function meetingCompanies(): HasMany
    {
        return $this->hasMany(MeetingCompany::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(CompanyMembership::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function initial(): string
    {
        return strtoupper(substr(trim((string) $this->name) ?: 'C', 0, 1));
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending_approval' => 'Pending Approval',
            'suspended' => 'Suspended',
            default => ucfirst($this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'active' => 'accepted',
            'inactive' => 'declined',
            'pending_approval' => 'pending',
            'suspended' => 'declined',
            default => 'pending',
        };
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name) ?: 'company';
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter++;
        }

        return $slug;
    }

    public static function findPotentialDuplicates(string $name, ?string $email = null, ?string $registrationNumber = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = static::query();

        $nameNormalized = Str::lower(trim($name));
        $nameWords = array_filter(explode(' ', $nameNormalized));

        $query->where(function ($q) use ($nameNormalized, $nameWords) {
            $q->whereRaw('LOWER(name) = ?', [$nameNormalized]);

            foreach ($nameWords as $word) {
                if (strlen($word) > 3) {
                    $q->orWhereRaw('LOWER(name) LIKE ?', ["%{$word}%"]);
                }
            }
        });

        if ($email) {
            $emailNormalized = Str::lower(trim($email));
            $query->orWhereRaw('LOWER(email) = ?', [$emailNormalized]);
        }

        if ($registrationNumber) {
            $query->orWhereRaw('LOWER(registration_number) = ?', [Str::lower(trim($registrationNumber))]);
        }

        return $query->whereNull('deleted_at')->limit(10)->get();
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            'pending_approval' => 'Pending Approval',
            'suspended' => 'Suspended',
            default => ucfirst($this->status),
        };
    }

    public function statusTone(): string
    {
        return match ($this->status) {
            'active' => 'accepted',
            'inactive' => 'declined',
            'pending_approval' => 'pending',
            'suspended' => 'declined',
            default => 'pending',
        };
    }

    public function getLogoUrl(): ?string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }

        return null;
    }

    public function getLogoPlaceholder(): string
    {
        return $this->initial();
    }
}

(End of file - total 177 lines)