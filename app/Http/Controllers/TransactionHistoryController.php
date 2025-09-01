<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\User; // Added this import for the fixCashierData method

class TransactionHistoryController extends Controller
{
    /**
     * Display transaction history
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['transactionItems.item', 'cashier'])
                           ->orderBy('created_at', 'desc');

        // Filter by user role
        $user = Auth::user();
        if ($user->isKasir()) {
            // Kasir hanya bisa lihat transaksi sendiri
            $query->where('cashier_id', $user->id);
        }
        // Admin dan SuperAdmin bisa lihat semua transaksi

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Search by transaction code
        if ($request->filled('search')) {
            $query->where('transaction_code', 'LIKE', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(20);

        // Statistics
        $stats = $this->getStatistics($user);

        return view('pages.transaction-history.index', compact('transactions', 'stats'));
    }

    /**
     * Fix cashier data for transactions
     */
    public function fixCashierData()
    {
        $user = Auth::user();
        
        // Only admin and superadmin can fix data
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $fixedCount = 0;
            
            // Get transactions with missing or invalid cashier_id
            $transactions = Transaction::whereNull('cashier_id')
                                     ->orWhereNotExists(function ($query) {
                                         $query->select(DB::raw(1))
                                               ->from('users')
                                               ->whereRaw('users.id = transactions.cashier_id');
                                     })
                                     ->get();

            foreach ($transactions as $transaction) {
                // Try to find a valid user to assign as cashier
                // First try to find an admin user
                $adminUser = User::where('role', 'admin')
                                ->where('is_active', true)
                                ->where('approval_status', 'approved')
                                ->first();
                
                if ($adminUser) {
                    $transaction->update(['cashier_id' => $adminUser->id]);
                    $fixedCount++;
                } else {
                    // If no admin, try to find any active approved user
                    $anyUser = User::where('is_active', true)
                                  ->where('approval_status', 'approved')
                                  ->first();
                    
                    if ($anyUser) {
                        $transaction->update(['cashier_id' => $anyUser->id]);
                        $fixedCount++;
                    }
                }
            }

            // Also check for transactions that might have wrong cashier_id
            // This could happen if transactions were created with wrong user context
            // Look for transactions that have cashier_id pointing to "Your Studio (Main)" or similar
            $wrongCashierTransactions = Transaction::whereHas('cashier', function($query) {
                                                $query->where('name', 'like', '%Your Studio%')
                                                      ->orWhere('name', 'like', '%Main%');
                                            })
                                            ->get();
            
            // Also check for transactions that don't match current user (for admin/superadmin)
            if ($user->isAdmin() || $user->isSuperAdmin()) {
                $userMismatchTransactions = Transaction::where('cashier_id', '!=', $user->id)
                                                      ->whereNotIn('id', $wrongCashierTransactions->pluck('id'))
                                                      ->get();
                $wrongCashierTransactions = $wrongCashierTransactions->merge($userMismatchTransactions);
            }
            
            foreach ($wrongCashierTransactions as $transaction) {
                // Only fix if the current user is admin/superadmin
                if ($user->isAdmin() || $user->isSuperAdmin()) {
                    $transaction->update(['cashier_id' => $user->id]);
                    $fixedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Fixed {$fixedCount} transactions",
                'fixed_count' => $fixedCount
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fixing cashier data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fix specific transaction cashier data
     */
    public function fixSpecificTransactionCashier($transactionId)
    {
        $user = Auth::user();
        
        // Only admin and superadmin can fix data
        if (!$user->isAdmin() && !$user->isSuperAdmin()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        try {
            $transaction = Transaction::findOrFail($transactionId);
            
            // Update the cashier_id to current user
            $transaction->update(['cashier_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => "Transaction {$transaction->transaction_code} cashier updated to {$user->name}",
                'transaction_code' => $transaction->transaction_code,
                'new_cashier' => $user->name
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error fixing transaction cashier: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show transaction detail
     */
    public function show($id)
    {
        $user = Auth::user();
        
        $query = Transaction::with(['transactionItems.item', 'cashier']);
        
        // Filter by user role
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }
        
        $transaction = $query->findOrFail($id);

        return view('pages.transaction-history.show', compact('transaction'));
    }

    /**
     * Show edit transaction form
     */
    public function edit($id)
    {
        $user = Auth::user();
        
        $query = Transaction::with(['transactionItems.item', 'cashier']);
        
        // Filter by user role
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }
        
        $transaction = $query->findOrFail($id);

        return view('pages.transaction-history.edit', compact('transaction'));
    }

    /**
     * Update transaction
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        
        $query = Transaction::with(['transactionItems.item', 'cashier']);
        
        // Filter by user role
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }
        
        $transaction = $query->findOrFail($id);

        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:transaction_items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $totalAmount = 0;
            
            foreach ($request->items as $itemData) {
                $transactionItem = $transaction->transactionItems()->findOrFail($itemData['id']);
                $item = $transactionItem->item;
                
                // Calculate old and new quantities
                $oldQuantity = $transactionItem->quantity;
                $newQuantity = $itemData['quantity'];
                $quantityDifference = $newQuantity - $oldQuantity;
                
                // Check if we have enough stock for increase
                if ($quantityDifference > 0 && $item->stock_quantity < $quantityDifference) {
                    throw new \Exception("Stok tidak cukup untuk item {$item->name}. Stok tersedia: {$item->stock_quantity}");
                }
                
                // Update transaction item
                $transactionItem->update([
                    'quantity' => $newQuantity,
                    'subtotal' => $item->selling_price * $newQuantity
                ]);
                
                // Update stock
                if ($quantityDifference != 0) {
                    $item->decrement('stock_quantity', $quantityDifference);
                }
                
                $totalAmount += $transactionItem->subtotal;
            }
            
            // Update transaction total
            $transaction->update([
                'subtotal' => $totalAmount,
                'total_amount' => $totalAmount - $transaction->discount_amount + $transaction->tax_amount
            ]);
            
            DB::commit();
            
            return redirect()->route('transaction-history.show', $transaction->id)
                           ->with('success', 'Transaksi berhasil diperbarui!');
                           
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get transaction statistics
     */
    private function getStatistics($user)
    {
        $query = Transaction::where('status', 'completed');
        
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }

        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'today_count' => (clone $query)->whereDate('transaction_date', $today)->count(),
            'today_total' => (clone $query)->whereDate('transaction_date', $today)->sum('total_amount'),
            'month_count' => (clone $query)->whereDate('transaction_date', '>=', $thisMonth)->count(),
            'month_total' => (clone $query)->whereDate('transaction_date', '>=', $thisMonth)->sum('total_amount'),
            'total_count' => $query->count(),
            'total_amount' => $query->sum('total_amount'),
        ];
    }

    /**
     * Export transactions to CSV
     */
    public function export(Request $request)
    {
        $query = Transaction::with(['transactionItems.item', 'cashier'])
                           ->orderBy('created_at', 'desc');

        $user = Auth::user();
        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }

        // Apply same filters as index
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('search')) {
            $query->where('transaction_code', 'LIKE', '%' . $request->search . '%');
        }

        $transactions = $query->get();

        $filename = 'transaction-history-' . date('Y-m-d-H-i-s') . '.csv';
        
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Kode Transaksi',
                'Tanggal',
                'Kasir',
                'Total Item',
                'Subtotal',
                'Diskon',
                'Pajak',
                'Total',
                'Dibayar',
                'Kembalian',
                'Metode Bayar',
                'Status',
                'Catatan'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_code,
                    $transaction->transaction_date->format('d/m/Y H:i:s'),
                    $transaction->cashier->name,
                    $transaction->transactionItems->sum('quantity'),
                    number_format($transaction->subtotal, 0, ',', '.'),
                    number_format($transaction->discount_amount, 0, ',', '.'),
                    number_format($transaction->tax_amount, 0, ',', '.'),
                    number_format($transaction->total_amount, 0, ',', '.'),
                    number_format($transaction->paid_amount, 0, ',', '.'),
                    number_format($transaction->change_amount, 0, ',', '.'),
                    strtoupper($transaction->payment_method),
                    ucfirst($transaction->status),
                    $transaction->notes ?? '-'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * API: Get recent transactions for dashboard widget
     */
    public function getRecentTransactions()
    {
        $user = Auth::user();
        
        $query = Transaction::with(['transactionItems', 'cashier'])
                           ->where('status', 'completed')
                           ->orderBy('created_at', 'desc')
                           ->limit(10);

        if ($user->isKasir()) {
            $query->where('cashier_id', $user->id);
        }

        $transactions = $query->get();

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }
}
