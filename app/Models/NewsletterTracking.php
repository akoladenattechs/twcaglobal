<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $newsletter_id
 * @property int $subscriber_id
 * @property string $event (open, click, bounce, complaint)
 * @property string|null $link_url
 * @property string|null $user_agent
 * @property string|null $ip_address
 * @property string $occurred_at
 *
 * @mixin Model
 */
class NewsletterTracking extends Model
{
    protected $table = 'newsletter_tracking';

    public $timestamps = false;

    protected $fillable = [
        'newsletter_id',
        'subscriber_id',
        'event',
        'link_url',
        'user_agent',
        'ip_address',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }

    // ───── Relationships ─────

    public function newsletter(): BelongsTo
    {
        return $this->belongsTo(Newsletter::class, 'newsletter_id');
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(NewsletterSubscriber::class, 'subscriber_id');
    }

    // ───── Scopes ─────

    public function scopeOpens(Builder $query): Builder
    {
        return $query->where('event', 'open');
    }

    public function scopeClicks(Builder $query): Builder
    {
        return $query->where('event', 'click');
    }

    // ───── Logging Helpers ─────

    /**
     * Log an open event (tracking pixel).
     */
    public static function logOpen(int $newsletterId, int $subscriberId, ?string $ip = null, ?string $ua = null): self
    {
        return self::create([
            'newsletter_id' => $newsletterId,
            'subscriber_id' => $subscriberId,
            'event' => 'open',
            'ip_address' => $ip,
            'user_agent' => $ua,
            'occurred_at' => now(),
        ]);
    }

    /**
     * Log a click event.
     */
    public static function logClick(int $newsletterId, int $subscriberId, string $linkUrl, ?string $ip = null, ?string $ua = null): self
    {
        return self::create([
            'newsletter_id' => $newsletterId,
            'subscriber_id' => $subscriberId,
            'event' => 'click',
            'link_url' => $linkUrl,
            'ip_address' => $ip,
            'user_agent' => $ua,
            'occurred_at' => now(),
        ]);
    }
}
