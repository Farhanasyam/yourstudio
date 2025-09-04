<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'barcode',
        'category_id',
        'supplier_id',
        'purchase_price',
        'selling_price',
        'stock_quantity',
        'minimum_stock',
        'unit',
        'image',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Auto-generate SKU
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($item) {
            if (empty($item->sku)) {
                $item->sku = 'SKU-' . strtoupper(uniqid());
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function stockInDetails()
    {
        return $this->hasMany(StockInDetail::class);
    }

    public function saleDetails()
    {
        return $this->hasMany(SaleDetail::class);
    }

    public function stockAdjustments()
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function barcodes()
    {
        return $this->hasMany(Barcode::class)->where('is_active', true);
    }

    public function allBarcodes()
    {
        return $this->hasMany(Barcode::class);
    }

    // Check if stock is low
    public function isLowStock()
    {
        return $this->stock_quantity <= $this->minimum_stock;
    }

    // Get profit margin
    public function getProfitMargin()
    {
        if ($this->purchase_price > 0) {
            return (($this->selling_price - $this->purchase_price) / $this->purchase_price) * 100;
        }
        return 0;
    }
}