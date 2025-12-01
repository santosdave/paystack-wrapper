<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SantosDave\Paystack\Support\Helpers;

class PaystackRefund extends Model
{
    protected $fillable = [
        'transaction_id',
        'reference',
        'amount',
        'currency',
        'status',
        'merchant_note',
        'customer_note',
        'metadata',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(PaystackTransaction::class, 'transaction_id');
    }

    public function getFormattedAmountAttribute(): string
    {
        return Helpers::formatAmount($this->amount, $this->currency);
    }

    public function isProcessed(): bool
    {
        return $this->status === 'processed';
    }

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }
}
