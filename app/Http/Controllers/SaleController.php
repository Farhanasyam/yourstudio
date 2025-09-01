<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        // Get date range filter
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        // Convert to Carbon if string
        if (is_string($startDate)) {
            $startDate = Carbon::parse($startDate);
        }
        if (is_string($endDate)) {
            $endDate = Carbon::parse($endDate);
        }

        // Get transactions data with filters
        $query = Transaction::with(['cashier', 'transactionItems.item.category'])
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('transaction_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%")
                  ->orWhereHas('cashier', function($cashierQuery) use ($search) {
                      $cashierQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('transactionItems.item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }

        // Filter by user role
        $user = auth()->user();
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(20);

        // Get statistics
        $stats = $this->getSalesStatistics($startDate, $endDate, $user);
        
        // Get top selling items
        $topSellingItems = $this->getTopSellingItems($startDate, $endDate, $user);
        
        // Get sales by category
        $salesByCategory = $this->getSalesByCategory($startDate, $endDate, $user);
        
        // Get daily sales chart data
        $dailySales = $this->getDailySalesData($startDate, $endDate, $user);

        return view('pages.sales.index', compact(
            'transactions', 
            'stats', 
            'topSellingItems', 
            'salesByCategory', 
            'dailySales',
            'startDate',
            'endDate'
        ));
    }

    private function getSalesStatistics($startDate, $endDate, $user)
    {
        $query = Transaction::whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed');
        
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }

        $totalSales = $query->sum('total_amount');
        $totalTransactions = $query->count();
        $averageTransaction = $totalTransactions > 0 ? $totalSales / $totalTransactions : 0;
        
        // Today's sales
        $todaySales = Transaction::whereDate('transaction_date', Carbon::today())
            ->where('status', 'completed');
        if ($user->isKasir()) {
            $todaySales->where('cashier_id', $user->id);
        }
        $todaySales = $todaySales->sum('total_amount');

        // Yesterday's sales
        $yesterdaySales = Transaction::whereDate('transaction_date', Carbon::yesterday())
            ->where('status', 'completed');
        if ($user->isKasir()) {
            $yesterdaySales->where('cashier_id', $user->id);
        }
        $yesterdaySales = $yesterdaySales->sum('total_amount');

        return [
            'total_sales' => $totalSales,
            'total_transactions' => $totalTransactions,
            'average_transaction' => $averageTransaction,
            'today_sales' => $todaySales,
            'yesterday_sales' => $yesterdaySales,
        ];
    }

    private function getTopSellingItems($startDate, $endDate, $user)
    {
        $query = TransactionItem::select('item_id', DB::raw('SUM(quantity) as total_sold'), DB::raw('SUM(subtotal) as total_revenue'))
            ->with('item.category')
            ->whereHas('transaction', function($q) use ($startDate, $endDate, $user) {
                $q->whereBetween('transaction_date', [$startDate, $endDate])
                  ->where('status', 'completed');
                if ($user->isKasir()) {
                    $q->where('cashier_id', $user->id);
                }
            })
            ->groupBy('item_id')
            ->orderBy('total_sold', 'desc')
            ->limit(10);

        return $query->get();
    }

    private function getSalesByCategory($startDate, $endDate, $user)
    {
        $query = TransactionItem::select('categories.name as category_name', DB::raw('SUM(transaction_items.subtotal) as total_sales'))
            ->join('items', 'transaction_items.item_id', '=', 'items.id')
            ->join('categories', 'items.category_id', '=', 'categories.id')
            ->whereHas('transaction', function($q) use ($startDate, $endDate, $user) {
                $q->whereBetween('transaction_date', [$startDate, $endDate])
                  ->where('status', 'completed');
                if ($user->isKasir()) {
                    $q->where('cashier_id', $user->id);
                }
            })
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc');

        return $query->get();
    }

    private function getDailySalesData($startDate, $endDate, $user)
    {
        $query = Transaction::select(DB::raw('DATE(transaction_date) as date'), DB::raw('SUM(total_amount) as total_sales'), DB::raw('COUNT(*) as transaction_count'))
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->where('status', 'completed');
            
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }
        
        return $query->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    public function show($id)
    {
        $transaction = Transaction::with(['cashier', 'transactionItems.item.category'])
            ->findOrFail($id);
            
        return view('pages.sales.show', compact('transaction'));
    }

    public function create()
    {
        return redirect()->route('kasir.index');
    }

    public function store(Request $request)
    {
        return redirect()->route('kasir.index');
    }

    public function edit($id)
    {
        return redirect()->route('sales.index')->with('info', 'Please use the cashier interface to modify transactions.');
    }

    public function update(Request $request, $id)
    {
        return redirect()->route('sales.index')->with('info', 'Please use the cashier interface to modify transactions.');
    }

    public function destroy($id)
    {
        return redirect()->route('sales.index')->with('info', 'Please use the cashier interface to manage transactions.');
    }
}