<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use App\Models\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KasirController extends Controller
{
    /**
     * Display kasir interface
     */
    public function index()
    {
        return view('pages.kasir.index');
    }

    /**
     * API: Search item by barcode
     */
    public function searchByBarcode(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $barcode = $request->barcode;
        
        // Cari di tabel items (kolom barcode)
        $item = Item::where('barcode', $barcode)
                   ->where('is_active', true)
                   ->with('category')
                   ->first();

        // Jika tidak ditemukan, cari di tabel barcodes
        if (!$item) {
            $barcodeRecord = Barcode::where('barcode_value', $barcode)
                                  ->where('is_active', true)
                                  ->with('item.category')
                                  ->first();
            
            if ($barcodeRecord && $barcodeRecord->item && $barcodeRecord->item->is_active) {
                $item = $barcodeRecord->item;
            }
        }

        if ($item) {
            // Check stock
            if ($item->stock_quantity <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok barang habis!'
                ], 400);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $barcode,
                    'selling_price' => $item->selling_price,
                    'stock_quantity' => $item->stock_quantity,
                    'category' => $item->category->name ?? '',
                    'unit' => $item->unit,
                    'image' => $item->image ? asset('storage/' . $item->image) : null
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Barcode tidak ditemukan atau barang tidak aktif!'
        ], 404);
    }

    /**
     * Store new transaction
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.barcode_scanned' => 'nullable|string',
            'paid_amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:cash,card,transfer,qris'
        ]);

        try {
            DB::beginTransaction();

            // Calculate totals
            $subtotal = 0;
            $items = $request->items;

            // Validate stock and calculate subtotal
            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                
                if ($item->stock_quantity < $itemData['quantity']) {
                    return response()->json([
                        'success' => false,
                        'message' => "Stok {$item->name} tidak mencukupi! Stok tersedia: {$item->stock_quantity}"
                    ], 400);
                }

                $subtotal += $itemData['unit_price'] * $itemData['quantity'];
            }

            $discountAmount = $request->discount_amount ?? 0;
            $taxAmount = $request->tax_amount ?? 0;
            $totalAmount = $subtotal - $discountAmount + $taxAmount;

            if ($request->paid_amount < $totalAmount) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jumlah pembayaran kurang dari total!'
                ], 400);
            }

            $changeAmount = $request->paid_amount - $totalAmount;

            // Create transaction
            $transaction = Transaction::create([
                'cashier_id' => Auth::id(),
                'transaction_date' => now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount,
                'change_amount' => $changeAmount,
                'payment_method' => $request->payment_method,
                'status' => 'completed'
            ]);

            // Create transaction items and update stock
            foreach ($items as $itemData) {
                $item = Item::findOrFail($itemData['item_id']);
                
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'item_sku' => $item->sku,
                    'barcode_scanned' => $itemData['barcode_scanned'] ?? null,
                    'unit_price' => $itemData['unit_price'],
                    'quantity' => $itemData['quantity'],
                    'discount_per_item' => $itemData['discount_per_item'] ?? 0,
                ]);

                // Update stock
                $item->decrement('stock_quantity', $itemData['quantity']);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan!',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code,
                    'total_amount' => $totalAmount,
                    'paid_amount' => $request->paid_amount,
                    'change_amount' => $changeAmount
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show transaction receipt
     */
    public function receipt($id, Request $request)
    {
        $transaction = Transaction::with(['transactionItems.item', 'cashier'])
                                 ->findOrFail($id);

        $isCopy = $request->has('copy');

        return view('pages.kasir.receipt', compact('transaction', 'isCopy'));
    }

    /**
     * Get transaction history for today
     */
    public function todayTransactions()
    {
        $transactions = Transaction::with(['transactionItems', 'cashier'])
                                  ->today()
                                  ->completed()
                                  ->orderBy('created_at', 'desc')
                                  ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $transactions
        ]);
    }

    /**
     * Get items with low stock warning
     */
    public function getLowStockItems()
    {
        $lowStockItems = Item::where('is_active', true)
                            ->whereColumn('stock_quantity', '<=', 'minimum_stock')
                            ->select('id', 'name', 'sku', 'stock_quantity', 'minimum_stock')
                            ->get();

        return response()->json([
            'success' => true,
            'data' => $lowStockItems
        ]);
    }

    /**
     * API: Get popular items
     */
    public function getPopularItems()
    {
        $popularItems = Item::select('items.*')
                           ->join('transaction_items', 'items.id', '=', 'transaction_items.item_id')
                           ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                           ->where('transactions.status', 'completed')
                           ->where('transactions.created_at', '>=', now()->subDays(30))
                           ->where('items.is_active', true)
                           ->groupBy('items.id')
                           ->orderByRaw('SUM(transaction_items.quantity) DESC')
                           ->limit(10)
                           ->get();

        return response()->json([
            'success' => true,
            'data' => $popularItems
        ]);
    }
}
