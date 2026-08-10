<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $service_times
 * @property string|null $description
 * @property string|null $image
 * @property int $display_order
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereServiceTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CenterLocation whereUpdatedAt($value)
 *
 * @mixin Model
 */
class CenterLocation extends Model
{
    protected $table = 'center_locations';

    protected $fillable = [
        'name',
        'address',
        'phone',
        'email',
        'service_times',
        'description',
        'display_order',
        'status',
    ];
}
