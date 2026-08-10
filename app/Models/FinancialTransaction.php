<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinancialTransaction extends Model
{
    protected $table = 'financial_transactions';

    protected $fillable = [
        'type', 'category', 'amount', 'payment_method',
        'account_id', 'fund_id', 'transaction_date',
        'description', 'reference_number', 'member_id',
        'recorded_by', 'status', 'approved_by', 'approved_at',
        'reconciled', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'approved_at' => 'datetime',
            'reconciled' => 'boolean',
            'amount' => 'decimal:2',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(FinancialAccount::class, 'account_id');
    }

    public function fund(): BelongsTo
    {
        return $this->belongsTo(FinancialFund::class, 'fund_id');
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(ChurchMember::class, 'member_id');
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeInflow(Builder $query)
    {
        return $query->where('type', 'inflow');
    }

    public function scopeOutflow(Builder $query)
    {
        return $query->where('type', 'outflow');
    }

    public function scopeApproved(Builder $query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeBetweenDates(Builder $query, string $start, string $end)
    {
        return $query->whereBetween('transaction_date', [$start, $end]);
    }
}
