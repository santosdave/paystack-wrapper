<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SantosDave\Paystack\Support\Helpers;

class PaystackSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'subscription_code',
        'email_token',
        'customer_code',
        'plan_code',
        'amount',
        'currency',
        'status',
        'next_payment_date',
    ];

    protected $casts = [
        'amount' => 'integer',
        'next_payment_date' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedAmountAttribute(): string
    {
        return Helpers::formatAmount($this->amount, $this->currency);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
