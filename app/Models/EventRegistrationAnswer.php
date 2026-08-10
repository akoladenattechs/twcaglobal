<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistrationAnswer extends Model
{
    protected $table = 'event_registration_answers';

    protected $fillable = [
        'registration_id',
        'field_id',
        'value',
    ];

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'registration_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(EventRegistrationField::class, 'field_id');
    }
}