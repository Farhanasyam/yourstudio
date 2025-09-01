<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'cashier_id',
        'transaction_date',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'notes',
    ];

    /**
     * Validation rules
     */
    public static $rules = [
        'cashier_id' => 'required|exists:users,id',
        'transaction_date' => 'required|date',
        'total_amount' => 'required|numeric|min:0',
        'paid_amount' => 'required|numeric|min:0',
        'payment_method' => 'required|in:cash,card,transfer,qris',
        'status' => 'required|in:pending,completed,cancelled',
    ];

    protected $casts = [
        'transaction_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change_amount' => 'decimal:2',
    ];

    /**
     * The relationships that should always be loaded.
     */
    protected $with = [];

    /**
     * Generate transaction code automatically
     */
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_code)) {
                $transaction->transaction_code = 'TXN-' . date('Ymd') . '-' . str_pad(
                    static::whereDate('created_at', today())->count() + 1, 
                    4, 
                    '0', 
                    STR_PAD_LEFT
                );
            }
            
            // Ensure cashier_id is set if not provided
            if (empty($transaction->cashier_id) && auth()->check()) {
                $transaction->cashier_id = auth()->id();
            }
        });
    }

    /**
     * Relasi ke kasir (User)
     */
    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id')->withDefault([
            'name' => 'Kasir tidak ditemukan',
            'id' => null
        ]);
    }

    /**
     * Relasi ke transaction items
     */
    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Scope untuk transaksi hari ini
     */
    public function scopeToday($query)
    {
        return $query->whereDate('transaction_date', today());
    }

    /**
     * Scope untuk transaksi completed
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope untuk transaksi pending
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Get total items dalam transaksi
     */
    public function getTotalItemsAttribute()
    {
        return $this->transactionItems()->sum('quantity');
    }

    /**
     * Format transaction code untuk display
     */
    public function getFormattedCodeAttribute()
    {
        return $this->transaction_code;
    }

    /**
     * Check if transaction can be modified
     */
    public function canBeModified()
    {
        return $this->status === 'pending';
    }

    /**
     * Calculate change amount
     */
    public function calculateChange($paidAmount)
    {
        return max(0, $paidAmount - $this->total_amount);
    }

    /**
     * Check if transaction has valid cashier
     */
    public function hasValidCashier()
    {
        return $this->cashier_id && $this->cashier && $this->cashier->id;
    }

    /**
     * Get cashier name safely
     */
    public function getCashierNameAttribute()
    {
        return $this->hasValidCashier() ? $this->cashier->name : 'Kasir tidak ditemukan';
    }
}
