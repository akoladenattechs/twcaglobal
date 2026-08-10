<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $description
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $created_at
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 *
 * @mixin Model
 */
class ActivityLog extends Model
{
    protected $table = 'activity_logs';

    public $timestamps = false;

    protected $fillable = ['user_id', 'action', 'description', 'ip_address', 'user_agent'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Record an activity log entry.
     *
     * Failures are swallowed silently so logging never blocks the main flow.
     *
     * @param  int|null  $userId  Authenticated user id (null for anonymous).
     * @param  string  $action  Machine-friendly action key, e.g. 'login', 'logout'.
     * @param  string  $description  Human-readable description of the event.
     * @param  Request|null  $request  Request to extract IP / user-agent from.
     */
    public static function record(?int $userId, string $action, string $description, ?Request $request = null): void
    {
        try {
            self::create([
                'user_id' => $userId,
                'action' => $action,
                'description' => $description,
                'ip_address' => $request ? ($request->ip() ?? 'Unknown') : null,
                'user_agent' => $request ? ($request->userAgent() ?? 'Unknown') : null,
            ]);
        } catch (\Exception $e) {
            // Fail silently — auditing must never break the application.
        }
    }
}
