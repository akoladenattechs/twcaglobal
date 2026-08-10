<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property int $attempts
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property-read User|null $user
 *
 * @mixin Model
 */
class TwoFactorCode extends Model
{
    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'used_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this code is still valid (not expired, not used).
     */
    public function isValid(): bool
    {
        return $this->used_at === null && $this->expires_at->isFuture();
    }

    /**
     * Check if the given plain-text OTP matches the stored hash.
     */
    public function checkOtp(string $plainOtp): bool
    {
        return Hash::check($plainOtp, $this->token);
    }
}
