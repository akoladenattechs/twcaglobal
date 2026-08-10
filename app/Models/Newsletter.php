<?php

namespace App\Models;

use App\Helpers\HtmlHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $subject
 * @property string $content
 * @property string $status (draft, scheduled, sent)
 * @property string|null $sent_at
 * @property string|null $scheduled_at
 * @property string|null $test_email
 * @property int $total_sent
 * @property int $opens_count
 * @property int $clicks_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @mixin Model
 */
class Newsletter extends Model
{
    protected $table = 'newsletters';

    protected $fillable = [
        'subject',
        'content',
        'status',
        'sent_at',
        'scheduled_at',
        'test_email',
        'total_sent',
        'opens_count',
        'clicks_count',
    ];

    /**
     * Sanitize newsletter HTML on save so admin-entered rich content can be
     * rendered safely with {!! !!} in the email template. This covers every
     * write path (draft, schedule, send, test send).
     */
    public function setContentAttribute(string $value): void
    {
        $this->attributes['content'] = HtmlHelper::sanitize($value);
    }

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'scheduled_at' => 'datetime',
        ];
    }

    // ───── Scopes ─────

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', 'sent');
    }

    /**
     * Newsletters that are due to be sent (scheduled and past their schedule time).
     */
    public function scopeDueForSending(Builder $query): Builder
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }

    // ───── Template Variable Replacement ─────

    /**
     * Replace template variables in the content with subscriber-specific values.
     *
     * Supported variables:
     *   {{unsubscribe_url}}  — one-click unsubscribe link with secure token
     *   {{email}}            — subscriber's email address
     *   {{name}}             — subscriber's name (or fallback)
     *   {{tracking_pixel}}   — invisible tracking pixel for open detection
     *
     * @param  string  $html  The raw HTML content
     * @return string Processed HTML
     */
    public function replaceVariables(NewsletterSubscriber $subscriber, string $html = ''): string
    {
        $content = $html ?: $this->content;

        // Use NEWSLETTER_PUBLIC_URL if set, otherwise fall back to APP_URL
        // This is important so email clients can reach the tracking endpoints from the internet
        $publicBase = rtrim(config('app.newsletter_public_url', config('app.url')), '/');

        $unsubscribeUrl = $publicBase.'/newsletter/unsubscribe/'.$subscriber->unsubscribe_token;

        $trackingPixelUrl = $publicBase.'/newsletter/track-open/'.$this->id.'/'.$subscriber->id;

        $replacements = [
            '{{unsubscribe_url}}' => $unsubscribeUrl,
            '{{email}}' => htmlspecialchars($subscriber->email),
            '{{name}}' => htmlspecialchars($subscriber->name ?: 'Beloved'),
            '{{tracking_pixel}}' => sprintf(
                '<img src="%s" alt="" width="1" height="1" style="display:none;border:0;outline:none;" />',
                $trackingPixelUrl
            ),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Replace template variables in the subject line.
     */
    public function replaceSubjectVariables(NewsletterSubscriber $subscriber): string
    {
        $replacements = [
            '{{email}}' => htmlspecialchars($subscriber->email),
            '{{name}}' => htmlspecialchars($subscriber->name ?: 'Beloved'),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $this->subject);
    }

    // ───── Helpers ─────

    /**
     * Wrap all links in the content with the redirect tracker.
     */
    public function wrapLinksForTracking(NewsletterSubscriber $subscriber, string $html): string
    {
        $publicBase = rtrim(config('app.newsletter_public_url', config('app.url')), '/');

        // Match href="..." inside <a> tags, skip mailto:, tel:, #, javascript:, and already-wrapped tracking links
        return preg_replace_callback(
            '/<a\b([^>]*?)\bhref\s*=\s*(["\'])((?!mailto:|tel:|#|javascript:|newsletter\/track)[^"\']+)\2([^>]*)>/i',
            function (array $matches) use ($subscriber, $publicBase) {
                $before = $matches[1]; // attributes before href
                $quote = $matches[2]; // quote character used
                $url = $matches[3]; // the raw URL
                $after = $matches[4]; // attributes after href

                $trackingUrl = $publicBase.'/newsletter/track-click'
                    .'?newsletter='.$this->id
                    .'&subscriber='.$subscriber->id
                    .'&url='.rawurlencode($url);

                return '<a'.$before.' href='.$quote.$trackingUrl.$quote.$after.'>';
            },
            $html
        );
    }

    /**
     * Increment the opens counter.
     */
    public function incrementOpens(): void
    {
        $this->increment('opens_count');
    }

    /**
     * Increment the clicks counter.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }

    // ───── Relationships ─────

    public function trackingEvents(): HasMany
    {
        return $this->hasMany(NewsletterTracking::class, 'newsletter_id');
    }
}
