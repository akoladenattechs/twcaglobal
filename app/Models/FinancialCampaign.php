<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialCampaign extends Model
{
    protected $table = 'financial_campaigns';

    protected $fillable = [
        'title', 'description', 'target_amount',
        'raised_amount', 'start_date', 'end_date',
        'status', 'cover_image',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'target_amount' => 'decimal:2',
            'raised_amount' => 'decimal:2',
        ];
    }

    public function pledges(): HasMany
    {
        return $this->hasMany(FinancialPledge::class, 'campaign_id');
    }

    public function getProgressAttribute(): float
    {
        if (! $this->target_amount || $this->target_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->raised_amount / $this->target_amount) * 100, 1));
    }

    public function getRemainingDaysAttribute(): ?int
    {
        if (! $this->end_date) {
            return null;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }
}
