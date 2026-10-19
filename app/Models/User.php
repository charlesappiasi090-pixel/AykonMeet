<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'can_record_sessions',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'can_record_sessions' => 'boolean',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted:array',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    /**
     * Check if the user has two-factor authentication enabled.
     */
    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    /**
     * Get the two-factor authentication secret.
     */
    public function getTwoFactorSecret(): ?string
    {
        return $this->two_factor_secret;
    }

    /**
     * Set the two-factor authentication secret.
     */
    public function setTwoFactorSecret(string $secret): void
    {
        $this->two_factor_secret = $secret;
    }

    /**
     * Get the two-factor recovery codes.
     */
    public function getTwoFactorRecoveryCodes(): array
    {
        return $this->two_factor_recovery_codes ?? [];
    }

    /**
     * Set the two-factor recovery codes.
     */
    public function setTwoFactorRecoveryCodes(array $codes): void
    {
        $this->two_factor_recovery_codes = $codes;
    }

    /**
     * Confirm two-factor authentication.
     */
    public function confirmTwoFactorAuthentication(): void
    {
        $this->forceFill(['two_factor_confirmed_at' => now()])->save();
    }

    /**
     * Disable two-factor authentication.
     */
    public function disableTwoFactorAuthentication(): void
    {
        $this->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();
    }

    /**
     * Generate new recovery codes.
     */
    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = strtoupper(Str::random(10));
        }
        $this->setTwoFactorRecoveryCodes($codes);

        return $codes;
    }
}
