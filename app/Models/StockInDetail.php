<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_id',
        'item_id',
        'quantity',
        'purchase_price',
        'subtotal',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}