<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialFund extends Model
{
    protected $table = 'financial_funds';

    protected $fillable = [
        'name', 'description', 'target_amount',
        'current_amount', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
        ];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinancialTransaction::class, 'fund_id');
    }

    public function getProgressAttribute(): float
    {
        if (! $this->target_amount || $this->target_amount <= 0) {
            return 0;
        }

        return min(100, round(($this->current_amount / $this->target_amount) * 100, 1));
    }
}
