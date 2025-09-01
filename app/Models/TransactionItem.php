<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'item_id',
        'item_name',
        'item_sku',
        'barcode_scanned',
        'unit_price',
        'quantity',
        'discount_per_item',
        'subtotal',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'discount_per_item' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Calculate subtotal automatically
     */
    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($transactionItem) {
            $transactionItem->subtotal = ($transactionItem->unit_price * $transactionItem->quantity) - $transactionItem->discount_per_item;
        });
    }

    /**
     * Relasi ke transaction
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Relasi ke item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get total before discount
     */
    public function getTotalBeforeDiscountAttribute()
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Get discount percentage
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->total_before_discount > 0) {
            return ($this->discount_per_item / $this->total_before_discount) * 100;
        }
        return 0;
    }

    /**
     * Format currency
     */
    public function getFormattedSubtotalAttribute()
    {
        return 'Rp ' . number_format($this->subtotal, 0, ',', '.');
    }

    public function getFormattedUnitPriceAttribute()
    {
        return 'Rp ' . number_format($this->unit_price, 0, ',', '.');
    }
}
