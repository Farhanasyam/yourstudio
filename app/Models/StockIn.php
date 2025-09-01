<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code',
        'supplier_id',
        'user_id',
        'transaction_date',
        'total_amount',
        'notes',
        'status',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($stockIn) {
            if (empty($stockIn->transaction_code)) {
                $stockIn->transaction_code = 'SI-' . date('Ymd') . '-' . strtoupper(uniqid());
            }
        });
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(StockInDetail::class);
    }
}