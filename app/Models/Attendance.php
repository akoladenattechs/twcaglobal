<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * @property int $id
 * @property int|null $center_id
 * @property string $service_date
 * @property string|null $service_type
 * @property int $males
 * @property int $females
 * @property int $first_timers
 * @property int $total
 * @property string|null $recorded_by
 * @property string|null $notes
 * @property Carbon|null $created_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCenterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereServiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereMales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereFemales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereFirstTimers($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereNotes($value)
 *
 * @mixin Model
 */
class Attendance extends Model
{
    protected $table = 'attendance';

    const UPDATED_AT = null;

    protected $fillable = [
        'center_id',
        'service_date',
        'service_type',
        'males',
        'females',
        'first_timers',
        'recorded_by',
        'notes',
    ];

    protected static function booted()
    {
        static::creating(function ($attendance) {
            if (! $attendance->recorded_by) {
                $attendance->recorded_by = Auth::check()
                    ? (Auth::user()->name ?? Auth::user()->username)
                    : (app()->runningInConsole() ? 'CLI Import' : 'System');
            }
        });
    }

    public function center()
    {
        return $this->belongsTo(CenterLocation::class, 'center_id');
    }
}
