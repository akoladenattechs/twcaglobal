<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string|null $description
 * @property string $event_date
 * @property string $event_time
 * @property string|null $location
 * @property string|null $status
 * @property bool $expires
 * @property string|null $created_at
 * @property \Illuminate\Support\Carbon|null $start_date
 * @property \Illuminate\Support\Carbon|null $end_date
 * @property string|null $updated_at
 *
 * @method static Builder<static>|Event newModelQuery()
 * @method static Builder<static>|Event newQuery()
 * @method static Builder<static>|Event query()
 * @method static Builder<static>|Event upcoming()
 * @method static Builder<static>|Event whereCreatedAt($value)
 * @method static Builder<static>|Event whereDescription($value)
 * @method static Builder<static>|Event whereEndDate($value)
 * @method static Builder<static>|Event whereEventDate($value)
 * @method static Builder<static>|Event whereEventTime($value)
 * @method static Builder<static>|Event whereExpires($value)
 * @method static Builder<static>|Event whereId($value)
 * @method static Builder<static>|Event whereLocation($value)
 * @method static Builder<static>|Event whereStartDate($value)
 * @method static Builder<static>|Event whereStatus($value)
 * @method static Builder<static>|Event whereTitle($value)
 * @method static Builder<static>|Event whereUpdatedAt($value)
 *
 * @mixin Model
 */
class Event extends Model
{
    protected $table = 'events';

    public $timestamps = false;

    protected $fillable = ['title', 'slug', 'description', 'event_date', 'event_time', 'location', 'image', 'status', 'start_date', 'end_date', 'expires', 'requires_registration', 'recurrence_days'];

    protected $casts = [
        'expires' => 'boolean',
        'requires_registration' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'recurrence_days' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->title);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('title') && ! $model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->title, $model->id);
            }
        });
    }

    public static function generateUniqueSlug(string $title, $excludeId = null)
    {
        $slug = Str::slug($title);
        $original = $slug;
        $counter = 1;

        while (true) {
            $query = static::where('slug', $slug);
            if ($excludeId) {
                $query->where('id', '!=', $excludeId);
            }
            if (! $query->exists()) {
                break;
            }
            $slug = $original.'-'.$counter++;
        }

        return $slug;
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function registrationFields(): HasMany
    {
        return $this->hasMany(EventRegistrationField::class, 'event_id')->orderBy('sort_order');
    }

    /**
     * Whether this event is a recurring (looping) event.
     * An event loops when 'expires' is false.
     */
    public function isRecurring(): bool
    {
        return ! $this->expires;
    }

    /**
     * Compute the next upcoming occurrence date for this event.
     *
     * - For expiring events: returns the start_date if it's in the future,
     *   otherwise null (the event has expired).
     * - For recurring (looping) events: returns the next weekly occurrence
     *   at or after now(), computed from the original start_date.
     */
    public function nextOccurrence(): ?Carbon
    {
        $start = $this->start_date ? Carbon::parse($this->start_date) : null;
        if (! $start) {
            return null;
        }

        $now = Carbon::now();

        // Expiring event: only show if start_date is still in the future.
        if ($this->expires) {
            return $start->greaterThanOrEqualTo($now) ? $start : null;
        }

        // Recurring event with specific days selected.
        // Find the next upcoming date that falls on one of the chosen days.
        $days = $this->recurrence_days;
        if (is_array($days) && count($days) > 0) {
            // Sort days ascending and deduplicate
            $days = array_values(array_unique(array_map('intval', $days)));
            sort($days);

            // Preserve the original time from start_date
            $timeHour = $start->hour;
            $timeMinute = $start->minute;
            $timeSecond = $start->second;

            // Start checking from start_date or now, whichever is later
            $check = $start->copy();
            if ($check->lessThan($now)) {
                // Jump to the start of the current week, then find the next matching day
                $check = $now->copy()->startOfWeek(Carbon::SUNDAY)->setTime($timeHour, $timeMinute, $timeSecond);
            }

            // Scan up to 8 weeks ahead to find the next matching day
            for ($i = 0; $i < 56; $i++) {
                $dayOfWeek = (int) $check->dayOfWeek; // 0=Sun, 1=Mon, ..., 6=Sat
                if (in_array($dayOfWeek, $days) && $check->greaterThanOrEqualTo($now)) {
                    return $check;
                }
                $check->addDay();
            }

            return null;
        }

        // Recurring event without specific days: loop weekly from start_date.
        $next = $start->copy();
        while ($next->lessThan($now)) {
            $next->addWeek();
        }

        return $next;
    }

    /**
     * Scope to get events that should currently appear on the frontend
     * (published and either upcoming-expiring or recurring).
     */
    public function scopeUpcoming(Builder $query)
    {
        return $query->where('status', 'published');
    }
}
