<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class VerificationCode extends Model
{
    protected $fillable = [
        'user_id',
        'email',
        'phone',
        'email_code',
        'phone_code',
        'email_expires_at',
        'phone_expires_at',
        'email_verified_at',
        'phone_verified_at',
        'email_attempts',
        'phone_attempts',
        'status',
    ];

    protected $casts = [
        'email_expires_at' => 'datetime',
        'phone_expires_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    protected $hidden = [
        'email_code',
        'phone_code',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateCode(): string
    {
        return str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    public static function createForUser(User $user, string $email, string $phone): self
    {
        return static::create([
            'user_id' => $user->id,
            'email' => $email,
            'phone' => $phone,
            'email_code' => static::generateCode(),
            'phone_code' => static::generateCode(),
            'email_expires_at' => now()->addMinutes(10),
            'phone_expires_at' => now()->addMinutes(10),
            'status' => 'pending',
        ]);
    }

    public function isEmailExpired(): bool
    {
        return $this->email_expires_at && $this->email_expires_at->isPast();
    }

    public function isPhoneExpired(): bool
    {
        return $this->phone_expires_at && $this->phone_expires_at->isPast();
    }

    public function isEmailVerified(): bool
    {
        return $this->email_verified_at !== null;
    }

    public function isPhoneVerified(): bool
    {
        return $this->phone_verified_at !== null;
    }

    public function isFullyVerified(): bool
    {
        return $this->isEmailVerified() && $this->isPhoneVerified();
    }

    public function verifyEmail(string $code): bool
    {
        if ($this->isEmailVerified()) {
            return true;
        }

        if ($this->isEmailExpired()) {
            return false;
        }

        if ($this->email_attempts >= 5) {
            return false;
        }

        $this->increment('email_attempts');

        if (hash_equals($this->email_code, $code)) {
            $this->update([
                'email_verified_at' => now(),
                'email_code' => null,
            ]);
            return true;
        }

        return false;
    }

    public function verifyPhone(string $code): bool
    {
        if ($this->isPhoneVerified()) {
            return true;
        }

        if ($this->isPhoneExpired()) {
            return false;
        }

        if ($this->phone_attempts >= 5) {
            return false;
        }

        $this->increment('phone_attempts');

        if (hash_equals($this->phone_code, $code)) {
            $this->update([
                'phone_verified_at' => now(),
                'phone_code' => null,
            ]);
            return true;
        }

        return false;
    }

    public function markComplete(): void
    {
        if ($this->isFullyVerified()) {
            $this->update(['status' => 'verified']);
        }
    }

    public function resendEmailCode(): void
    {
        $this->update([
            'email_code' => static::generateCode(),
            'email_expires_at' => now()->addMinutes(10),
            'email_verified_at' => null,
            'email_attempts' => 0,
        ]);
    }

    public function resendPhoneCode(): void
    {
        $this->update([
            'phone_code' => static::generateCode(),
            'phone_expires_at' => now()->addMinutes(10),
            'phone_verified_at' => null,
            'phone_attempts' => 0,
        ]);
    }
}
