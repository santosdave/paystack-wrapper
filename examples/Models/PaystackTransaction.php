<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use SantosDave\Paystack\Support\Helpers;

class PaystackTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'reference',
        'paystack_reference',
        'amount',
        'currency',
        'channel',
        'status',
        'authorization_code',
        'customer_code',
        'customer_email',
        'metadata',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get refunds for this transaction.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(PaystackRefund::class, 'transaction_id');
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return Helpers::formatAmount($this->amount, $this->currency);
    }

    /**
     * Get amount in main currency unit.
     */
    public function getAmountInMainUnitAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Check if transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Check if transaction is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if transaction has failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark transaction as successful.
     */
    public function markAsSuccessful(array $data = []): void
    {
        $this->update([
            'status' => 'success',
            'paid_at' => now(),
            'paystack_reference' => $data['reference'] ?? $this->paystack_reference,
            'authorization_code' => $data['authorization']['authorization_code'] ?? null,
            'customer_code' => $data['customer']['customer_code'] ?? null,
            'channel' => $data['channel'] ?? $this->channel,
        ]);
    }

    /**
     * Mark transaction as failed.
     */
    public function markAsFailed(): void
    {
        $this->update([
            'status' => 'failed',
        ]);
    }

    /**
     * Scope successful transactions.
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope pending transactions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope failed transactions.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}

// PaystackRefund.php
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

// PaystackSubscription.php
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

// PaystackWebhook.php
class PaystackWebhook extends Model
{
    protected $fillable = [
        'event_type',
        'paystack_id',
        'reference',
        'payload',
        'status',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];

    public function markAsProcessed(): void
    {
        $this->update([
            'status' => 'processed',
            'processed_at' => now(),
        ]);
    }

    public function markAsFailed(string $error): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
