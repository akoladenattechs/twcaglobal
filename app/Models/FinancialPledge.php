<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialPledge extends Model
{
    protected $table = 'financial_pledges';

    protected $fillable = [
        'member_id', 'campaign_id', 'pledge_amount',
        'amount_paid', 'payment_schedule', 'status',
        'pledge_date', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'pledge_date' => 'date',
            'pledge_amount' => 'decimal:2',
            'amount_paid' => 'decimal:2',
            'payment_schedule' => 'array',
        ];
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ChurchMember::class, 'member_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(FinancialCampaign::class, 'campaign_id');
    }

    public function getBalanceAttribute(): float
    {
        return max(0, $this->pledge_amount - $this->amount_paid);
    }

    public function getProgressAttribute(): float
    {
        if (! $this->pledge_amount || $this->pledge_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->amount_paid / $this->pledge_amount) * 100, 1));
    }
}
