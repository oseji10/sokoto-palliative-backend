<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Transaction extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'PENDING';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_CANCELLED = 'CANCELLED';
    public const STATUS_FAILED = 'FAILED';

    protected $fillable = [
        'reference',
        'amount',
        'terminal_serial',
        'transaction_type',
        'payment_method',
        'status',
        'response',
        'user_id',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'integer', // Stored in kobo (1/100 of NGN)
        'response' => 'array',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $appends = [
        'amount_in_ngn',
    ];

    /**
     * Get the user that owns the transaction
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for completed transactions
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for pending transactions
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope for cancelled transactions
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope for failed transactions
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Get amount in NGN (Naira)
     */
    public function getAmountInNgnAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Get formatted amount with currency
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₦' . number_format($this->amount_in_ngn, 2);
    }

    /**
     * Check if transaction is successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if transaction is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if transaction is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if transaction failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark transaction as completed
     */
    public function markAsCompleted(): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => $this->paid_at ?? now(),
        ]);
    }

    /**
     * Mark transaction as failed
     */
    public function markAsFailed(string $reason = null): bool
    {
        $response = array_merge($this->response ?? [], [
            'failed_reason' => $reason,
            'failed_at' => now()->toDateTimeString(),
        ]);

        return $this->update([
            'status' => self::STATUS_FAILED,
            'response' => $response,
        ]);
    }
}
