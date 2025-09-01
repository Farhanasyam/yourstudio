<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'parameters',
        'data',
        'generated_by',
        'generated_at',
        'file_path',
    ];

    protected $casts = [
        'parameters' => 'array',
        'data' => 'array',
        'generated_at' => 'datetime',
    ];

    /**
     * Report types
     */
    const TYPE_DAILY_SALES = 'daily_sales';
    const TYPE_MONTHLY_SALES = 'monthly_sales';
    const TYPE_STOCK_REPORT = 'stock_report';
    const TYPE_LOW_STOCK = 'low_stock';
    const TYPE_ITEM_TRENDS = 'item_trends';
    const TYPE_CASHIER_PERFORMANCE = 'cashier_performance';
    const TYPE_SALES_BY_CATEGORY = 'sales_by_category';
    const TYPE_PROFIT_ANALYSIS = 'profit_analysis';

    /**
     * Get all report types
     */
    public static function getTypes()
    {
        return [
            self::TYPE_DAILY_SALES => 'Daily Sales Report',
            self::TYPE_MONTHLY_SALES => 'Monthly Sales Report',
            self::TYPE_STOCK_REPORT => 'Stock Report',
            self::TYPE_LOW_STOCK => 'Low Stock Report',
            self::TYPE_ITEM_TRENDS => 'Item Trends Report',
            self::TYPE_CASHIER_PERFORMANCE => 'Cashier Performance Report',
            self::TYPE_SALES_BY_CATEGORY => 'Sales by Category Report',
            self::TYPE_PROFIT_ANALYSIS => 'Profit Analysis Report',
        ];
    }

    /**
     * Relationship to user who generated the report
     */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /**
     * Scope for recent reports
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('generated_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for specific type
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Check if report is expired (older than 24 hours)
     */
    public function isExpired()
    {
        return $this->generated_at->diffInHours(now()) > 24;
    }

    /**
     * Get formatted file size
     */
    public function getFileSizeAttribute()
    {
        if (!$this->file_path || !file_exists(storage_path('app/' . $this->file_path))) {
            return '0 KB';
        }

        $size = filesize(storage_path('app/' . $this->file_path));
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }
        
        return round($size, 2) . ' ' . $units[$i];
    }
}
