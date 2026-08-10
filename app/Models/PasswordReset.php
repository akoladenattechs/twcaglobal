<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

/**
 * @property int $id
 * @property string $email
 * @property string $token
 * @property Carbon $expires_at
 * @property Carbon|null $used_at
 * @property int $attempts
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereExpiresAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereUsedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereAttempts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordReset whereUpdatedAt($value)
 *
 * @mixin Model
 */
class PasswordReset extends Model
{
    protected $table = 'password_resets';

    protected $fillable = [
        'email',
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

    /**
     * Check if this reset token is still valid (not expired, not used).
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
