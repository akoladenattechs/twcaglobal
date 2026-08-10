<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $service_date
 * @property string $service_type
 * @property numeric $amount
 * @property string $offering_type
 * @property string $payment_method
 * @property int $recorded_by
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $recordedBy
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereOfferingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering wherePaymentMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereRecordedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereServiceDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Offering whereUpdatedAt($value)
 *
 * @mixin Model
 */
class Offering extends Model
{
    protected $table = 'offerings';

    public $timestamps = true;

    protected $fillable = ['service_date', 'service_type', 'amount', 'offering_type', 'payment_method', 'recorded_by', 'notes'];

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
