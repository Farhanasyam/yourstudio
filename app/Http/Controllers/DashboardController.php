<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Get total counts
        $totalItems = Item::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();
        $totalUsers = User::count();
        
        // Get recent transactions with details
        $recentSales = Transaction::with(['cashier', 'transactionItems.item'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent stock ins
        $recentStockIns = StockIn::with(['supplier', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent stock adjustments
        $recentAdjustments = StockAdjustment::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        // Get low stock items (items with stock_quantity less than minimum_stock)
        $lowStockItems = Item::where('stock_quantity', '<=', DB::raw('minimum_stock'))
            ->orderBy('stock_quantity', 'asc')
            ->limit(5)
            ->get();
            
        // Get sales statistics for the current month
        $currentMonthSales = Transaction::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
            
        // Get total sales
        $totalSales = Transaction::sum('total_amount');
        
        // Get daily sales for the last 7 days
        $dailySales = Transaction::select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_amount) as total'))
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Get yesterday vs today sales comparison
        $todaySales = Transaction::whereDate('created_at', today())->sum('total_amount');
        $yesterdaySales = Transaction::whereDate('created_at', today()->subDay())->sum('total_amount');
        $salesGrowth = $yesterdaySales > 0 ? (($todaySales - $yesterdaySales) / $yesterdaySales) * 100 : 0;
        
        // Get items by category for chart
        $itemsByCategory = Category::withCount('items')->get();
        
            
        // Get pending users for superadmin
        $pendingUsers = collect();
        if (auth()->user()->isSuperAdmin()) {
            $pendingUsers = User::where('approval_status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get();
        }
        
        return view('pages.dashboard.dashboard', compact(
            'totalItems',
            'totalCategories', 
            'totalSuppliers',
            'totalUsers',
            'recentSales',
            'recentStockIns',
            'recentAdjustments',
            'lowStockItems',
            'currentMonthSales',
            'totalSales',
            'itemsByCategory',
            'pendingUsers',
            'dailySales',
            'todaySales',
            'yesterdaySales',
            'salesGrowth'
        ));
    }
}