<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $email
 * @property string|null $name
 * @property string $status (pending, active, unsubscribed, bounced)
 * @property string|null $verification_token
 * @property string|null $unsubscribe_token
 * @property string|null $subscribed_at
 * @property string|null $verified_at
 * @property string|null $unsubscribed_at
 * @property string|null $bounced_at
 * @property string|null $bounce_reason
 * @property string|null $complaint_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Model
 */
class NewsletterSubscriber extends Model
{
    protected $table = 'newsletter_subscribers';

    protected $fillable = [
        'email',
        'name',
        'status',
        'subscribed_at',
        'verification_token',
        'unsubscribe_token',
        'verified_at',
        'unsubscribed_at',
        'bounced_at',
        'bounce_reason',
        'complaint_at',
    ];

    protected function casts(): array
    {
        return [
            'subscribed_at' => 'datetime',
            'verified_at' => 'datetime',
            'unsubscribed_at' => 'datetime',
            'bounced_at' => 'datetime',
            'complaint_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (NewsletterSubscriber $subscriber) {
            if (empty($subscriber->unsubscribe_token)) {
                $subscriber->unsubscribe_token = Str::random(64);
            }
        });
    }

    public function getUnsubscribeTokenAttribute(?string $value): string
    {
        if (empty($value)) {
            $token = Str::random(64);
            if ($this->exists) {
                $this->forceFill(['unsubscribe_token' => $token])->saveQuietly();
            }

            return $token;
        }

        return $value;
    }

    // ───── Scopes ─────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeUnsubscribed(Builder $query): Builder
    {
        return $query->where('status', 'unsubscribed');
    }

    public function scopeBounced(Builder $query): Builder
    {
        return $query->where('status', 'bounced');
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->whereNotNull('verified_at');
    }

    // ───── Status Transitions ─────

    /**
     * Generate tokens and mark as pending (double opt-in).
     */
    public static function register(string $email, ?string $name = null): self
    {
        return self::create([
            'email' => $email,
            'name' => $name,
            'status' => 'active',
            'subscribed_at' => now(),
            'verified_at' => now(),
            'verification_token' => Str::random(64),
            'unsubscribe_token' => Str::random(64),
        ]);
    }

    /**
     * Verify the subscriber's email (completes double opt-in).
     */
    public function verify(): bool
    {
        if ($this->status !== 'pending') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'verification_token' => null,  // consume token
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Admin shortcut — mark as verified and active without consuming token.
     */
    public function markAsVerified(): bool
    {
        $this->update([
            'status' => 'active',
            'verification_token' => null,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Check if the subscriber has a verified email.
     */
    public function hasVerifiedEmail(): bool
    {
        return $this->verified_at !== null;
    }

    /**
     * Check if the subscriber is unsubscribed.
     */
    public function isUnsubscribed(): bool
    {
        return $this->status === 'unsubscribed';
    }

    /**
     * Unsubscribe using the secure token.
     */
    public function unsubscribe(): bool
    {
        if (! in_array($this->status, ['active', 'pending'])) {
            return false;
        }

        $this->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return true;
    }

    /**
     * Re-subscribe an unsubscribed or bounced subscriber.
     */
    public function resubscribe(): bool
    {
        if ($this->status === 'active') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'unsubscribed_at' => null,
            'bounced_at' => null,
            'bounce_reason' => null,
            'complaint_at' => null,
            'verified_at' => now(),
        ]);

        return true;
    }

    /**
     * Mark as bounced.
     */
    public function markAsBounced(?string $reason = null): bool
    {
        $this->update([
            'status' => 'bounced',
            'bounced_at' => now(),
            'bounce_reason' => $reason,
        ]);

        return true;
    }

    /**
     * Mark as spam complaint.
     */
    public function markAsComplaint(): bool
    {
        $this->update([
            'status' => 'unsubscribed',
            'complaint_at' => now(),
        ]);

        return true;
    }

    // ───── Token Validation ─────

    /**
     * Find a subscriber by a secure unsubscribe token.
     */
    public static function findByUnsubscribeToken(string $token): ?self
    {
        return self::where('unsubscribe_token', $token)->first();
    }

    /**
     * Find a subscriber by verification token.
     */
    public static function findByVerificationToken(string $token): ?self
    {
        return self::where('verification_token', $token)->first();
    }
}
