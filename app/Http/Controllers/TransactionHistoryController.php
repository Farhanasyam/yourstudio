<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

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
     * Bulk delete transactions
     */
    public function bulkDelete(Request $request)
    {
        try {
            // Deleting sales records is limited to the super admin (prevents hiding sales)
            if (!auth()->user()->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Super Admin yang dapat menghapus transaksi.'
                ], 403);
            }

            $request->validate([
                'transaction_ids' => 'required|array',
                'transaction_ids.*' => 'integer|exists:transactions,id'
            ]);

            $transactionIds = $request->input('transaction_ids');
            $deletedCount = 0;
            $errors = [];

            foreach ($transactionIds as $transactionId) {
                try {
                    $transaction = Transaction::find($transactionId);

                    if ($transaction) {
                        DB::transaction(function () use ($transaction) {
                            $this->restoreStock($transaction->id);
                            DB::table('transaction_items')->where('transaction_id', $transaction->id)->delete();
                            $transaction->delete();
                        });
                        $deletedCount++;
                    } else {
                        $errors[] = "Transaction with ID {$transactionId} not found.";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete transaction ID {$transactionId}: " . $e->getMessage();
                }
            }

            if ($deletedCount > 0) {
                $message = "Successfully deleted {$deletedCount} transaction(s) and all connected data.";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " transaction(s) could not be deleted.";
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted_count' => $deletedCount,
                    'errors' => $errors
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No transactions were deleted. ' . implode(' ', $errors)
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing bulk delete: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete ALL transactions in the entire database
     */
    public function deleteAllInDatabase(Request $request)
    {
        try {
            // Deleting sales records is limited to the super admin (prevents hiding sales)
            if (!auth()->user()->isSuperAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Hanya Super Admin yang dapat menghapus transaksi.'
                ], 403);
            }

            // Get total count of transactions
            $totalTransactions = Transaction::count();
            
            if ($totalTransactions === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No transactions found in the database.'
                ]);
            }

            // Return sold quantities to stock and delete everything atomically
            // (DELETE instead of TRUNCATE: TRUNCATE auto-commits and can't be rolled back)
            DB::transaction(function () {
                $this->restoreStock();
                DB::table('transaction_items')->delete();
                DB::table('transactions')->delete();
            });

            // Reset auto-increment counters
            DB::statement('ALTER TABLE transactions AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE transaction_items AUTO_INCREMENT = 1');

            return response()->json([
                'success' => true,
                'message' => "Successfully deleted ALL {$totalTransactions} transactions and all connected data from the database. The transaction history has been completely cleared.",
                'deleted_count' => $totalTransactions
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting all transactions: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Put the quantities of completed sales back into item stock.
     * Pass a transaction id to restore a single transaction, or null for all of them.
     */
    private function restoreStock($transactionId = null)
    {
        $sold = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->where('transactions.status', 'completed')
            ->when($transactionId, fn ($q) => $q->where('transactions.id', $transactionId))
            ->groupBy('transaction_items.item_id')
            ->select('transaction_items.item_id', DB::raw('SUM(transaction_items.quantity) as qty'))
            ->get();

        foreach ($sold as $row) {
            DB::table('items')->where('id', $row->item_id)->increment('stock_quantity', (int) $row->qty);
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
                    $transaction->cashier->name ?? '-',
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
