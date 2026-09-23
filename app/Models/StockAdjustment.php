<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'item_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'reason',
        'notes',
        'adjustment_date',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
    ];

    // Keys must match the `reason` enum column in the stock_adjustments table
    public const REASONS = [
        'correction' => 'Stock Take / System Correction',
        'damaged' => 'Damaged Items',
        'expired' => 'Expired Items',
        'lost' => 'Lost Items',
        'found' => 'Found Items',
        'other' => 'Other',
    ];

    public function getReasonLabelAttribute()
    {
        return self::REASONS[$this->reason] ?? $this->reason;
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($adjustment) {
            if (empty($adjustment->transaction_code)) {
                $adjustment->transaction_code = 'ADJ-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}