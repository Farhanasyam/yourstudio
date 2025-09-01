<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\StockIn;
use App\Models\StockAdjustment;
use App\Models\User;
use App\Models\SaleDetail;
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
        
        // Get recent sales with details
        $recentSales = Sale::with(['user', 'details.item'])
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
        $currentMonthSales = Sale::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('total_amount');
            
        // Get total sales
        $totalSales = Sale::sum('total_amount');
        
        // Get items by category for chart
        $itemsByCategory = Category::withCount('items')->get();
        
        // Get top selling items this month
        $topSellingItems = SaleDetail::select('item_id', DB::raw('SUM(quantity) as total_sold'))
            ->with('item')
            ->whereHas('sale', function($query) {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            })
            ->groupBy('item_id')
            ->orderBy('total_sold', 'desc')
            ->limit(5)
            ->get();
            
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
            'topSellingItems',
            'pendingUsers'
        ));
    }
}