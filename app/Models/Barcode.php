<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barcode extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'barcode_type',
        'barcode_value',
        'barcode_image_path',
        'is_active',        // Tambahkan field ini
        'is_printed',
        'printed_at',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',   // Tambahkan cast ini
        'is_printed' => 'boolean',
        'printed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke model Item
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Relasi ke model User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope untuk barcode yang aktif
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope untuk barcode yang tidak aktif
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope untuk barcode yang sudah dicetak
     */
    public function scopePrinted($query)
    {
        return $query->where('is_printed', true);
    }

    /**
     * Scope untuk barcode yang belum dicetak
     */
    public function scopeUnprinted($query)
    {
        return $query->where('is_printed', false);
    }

    /**
     * Scope untuk mencari berdasarkan nilai barcode
     */
    public function scopeByBarcodeValue($query, $barcodeValue)
    {
        return $query->where('barcode_value', $barcodeValue);
    }

    /**
     * Get barcode display name
     */
    public function getDisplayNameAttribute()
    {
        return $this->item->name ?? 'Unknown Item';
    }

    /**
     * Check if barcode has image
     */
    public function hasImage()
    {
        return !is_null($this->barcode_image_path) && 
               file_exists(storage_path('app/public/' . $this->barcode_image_path));
    }
}