<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use App\Models\Barcode;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\LowStockNotification;
use App\Notifications\NewTransactionNotification;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

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
     * API: Search item by barcode (optimized for online kasir: cache + minimal columns)
     */
    public function searchByBarcode(Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|string'
            ]);

            $barcode = trim($request->barcode);
            $cacheKey = 'kasir_barcode_' . $barcode;

            // Cache 45 detik agar scan berulang/online tidak selalu hit DB
            $responseData = Cache::remember($cacheKey, 45, function () use ($barcode) {
                $item = Item::where('barcode', $barcode)
                    ->where('is_active', true)
                    ->select(['id', 'name', 'barcode', 'selling_price', 'stock_quantity', 'category_id'])
                    ->with('category:id,name')
                    ->first();

                if (!$item) {
                    $barcodeRecord = Barcode::where('barcode_value', $barcode)
                        ->where('is_active', true)
                        ->select('item_id')
                        ->first();

                    if ($barcodeRecord) {
                        $item = Item::where('id', $barcodeRecord->item_id)
                            ->where('is_active', true)
                            ->select(['id', 'name', 'barcode', 'selling_price', 'stock_quantity', 'category_id'])
                            ->with('category:id,name')
                            ->first();
                    }
                }

                if (!$item) {
                    return null;
                }

                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'barcode' => $item->barcode ?? $barcode,
                    'selling_price' => (float) $item->selling_price,
                    'stock_quantity' => (int) $item->stock_quantity,
                    'category' => $item->category ? $item->category->name : 'General',
                ];
            });

            if ($responseData !== null) {
                if ($responseData['selling_price'] <= 0) {
                    Cache::forget($cacheKey);
                    return response()->json([
                        'success' => false,
                        'message' => "Harga jual {$responseData['name']} belum diatur. Atur harga di menu Items.",
                        'type' => 'error'
                    ]);
                }
                if ($responseData['stock_quantity'] <= 0) {
                    Cache::forget($cacheKey);
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok produk habis',
                        'type' => 'error'
                    ]);
                }
                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Produk ditemukan'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan! Pastikan barcode terdaftar dan produk aktif.',
                'type' => 'error'
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchByBarcode:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari produk: ' . $e->getMessage(),
                'type' => 'error'
            ]);
        }
    }

    /**
     * Store transaction (optimized: 1x load items, batch stock update, invalidate barcode cache)
     */
    public function store(Request $request)
    {
        try {
            if ($request->isJson()) {
                $data = $request->json()->all();
                $request->merge($data);
            }

            if (!$request->has('items') || empty($request->items)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Items tidak boleh kosong'
                ], 400);
            }

            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            // Merge duplicate lines so stock is checked against the combined quantity
            $quantities = [];
            foreach ($request->items as $itemData) {
                $quantities[$itemData['id']] = ($quantities[$itemData['id']] ?? 0) + (int) $itemData['quantity'];
            }

            DB::beginTransaction();

            try {
                // Lock the rows so two cashiers can't sell the same last unit at once
                $items = Item::whereIn('id', array_keys($quantities))
                    ->where('is_active', true)
                    ->lockForUpdate()
                    ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock_quantity', 'minimum_stock', 'unit'])
                    ->keyBy('id');

                // Prices and stock always come from the database, never from the browser
                $total = 0;
                foreach ($quantities as $itemId => $qty) {
                    $item = $items->get($itemId);
                    if (!$item) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan atau tidak aktif'], 422);
                    }
                    if ((float) $item->selling_price <= 0) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Harga jual {$item->name} belum diatur. Atur harga di menu Items."
                        ], 422);
                    }
                    if ($item->stock_quantity < $qty) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => "Stok {$item->name} tidak cukup. Tersedia: {$item->stock_quantity}, diminta: {$qty}"
                        ], 422);
                    }
                    $total += (float) $item->selling_price * $qty;
                }

                $paid = (float) ($request->paid_amount ?? $total);
                if ($paid < $total) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => 'Pembayaran kurang dari total belanja (Rp ' . number_format($total, 0, ',', '.') . ')'
                    ], 422);
                }

                $paymentMethod = $this->convertPaymentMethod($request->payment_method ?? 'cash');
                $transactionCode = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);

                $transaction = Transaction::create([
                    'transaction_code' => $transactionCode,
                    'transaction_date' => now(),
                    'subtotal' => $total,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'change_amount' => $paid - $total,
                    'payment_method' => $paymentMethod,
                    'cashier_id' => Auth::id(),
                    'status' => 'completed'
                ]);

                $barcodesByItem = Barcode::whereIn('item_id', $items->keys())->where('is_active', true)->get(['item_id', 'barcode_value'])->groupBy('item_id');
                $stockBefore = [];

                foreach ($quantities as $itemId => $qty) {
                    $item = $items->get($itemId);
                    $stockBefore[$item->id] = $item->stock_quantity;

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'item_id' => $item->id,
                        'item_name' => $item->name,
                        'item_sku' => $item->sku ?? '',
                        'barcode_scanned' => '',
                        'quantity' => $qty,
                        'unit_price' => $item->selling_price,
                        'discount_per_item' => 0,
                        'subtotal' => (float) $item->selling_price * $qty
                    ]);

                    $item->decrement('stock_quantity', $qty);
                    if ($item->barcode) {
                        Cache::forget('kasir_barcode_' . $item->barcode);
                    }
                    foreach ($barcodesByItem->get($item->id, []) as $b) {
                        Cache::forget('kasir_barcode_' . $b->barcode_value);
                    }
                }

                DB::commit();

                // After commit, so a rolled-back sale never produces a notification
                $this->notifyLowStock($items, $stockBefore);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan',
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                Log::error('Transaction store error:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
                'debug_info' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Notify admins about items whose stock just dropped to (or below) their limit in this sale.
     * Only the sale that crosses the limit notifies, so later sales don't repeat the alert.
     * The limit is the item's minimum stock, or the "Batas Stok Minimum" setting when the item has none.
     */
    private function notifyLowStock($items, array $stockBefore)
    {
        try {
            if (!SystemSetting::get('enable_low_stock_notification', true)) {
                return;
            }

            $defaultLimit = (int) SystemSetting::get('low_stock_threshold', 10);

            $crossed = $items->filter(function ($item) use ($stockBefore, $defaultLimit) {
                $limit = $item->minimum_stock > 0 ? $item->minimum_stock : $defaultLimit;
                return $stockBefore[$item->id] > $limit && $item->stock_quantity <= $limit;
            });

            if ($crossed->isEmpty()) {
                return;
            }

            $recipients = User::whereIn('role', ['admin', 'superadmin'])
                ->where('is_active', true)
                ->where('approval_status', 'approved')
                ->get();

            foreach ($crossed as $item) {
                foreach ($recipients as $user) {
                    $user->notify(new LowStockNotification($item));
                }
            }
        } catch (\Exception $e) {
            // The sale is already saved; a notification problem must not turn it into an error
            Log::error('Low stock notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Convert payment method to match enum
     */
    private function convertPaymentMethod($method)
    {
        $methodMap = [
            'Tunai' => 'cash',
            'Cash' => 'cash',
            'Kartu' => 'card',
            'Card' => 'card',
            'Transfer' => 'transfer',
            'QRIS' => 'qris',
            'Qris' => 'qris'
        ];

        return $methodMap[$method] ?? 'cash';
    }

    /**
     * Test connection method
     */
    public function testConnection()
    {
        try {
            // Test database connection
            $itemCount = Item::count();
            $barcodeCount = Barcode::count();
            
            return response()->json([
                'success' => true,
                'message' => 'Connection test successful',
                'data' => [
                    'items_count' => $itemCount,
                    'barcodes_count' => $barcodeCount,
                    'timestamp' => now()->toISOString()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test transaction model
     */
    public function testTransactionModel()
    {
        try {
            // Test if Transaction model can be instantiated
            $transaction = new Transaction();
            
            // Test if table exists and is accessible
            $tableExists = \Schema::hasTable('transactions');
            
            // Test if we can query the table
            $transactionCount = Transaction::count();
            
            // Test if we can create a simple record
            $testTransaction = Transaction::create([
                'transaction_code' => 'TEST-' . time(),
                'transaction_date' => now(),
                'subtotal' => 1000,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 1000,
                'paid_amount' => 1000,
                'change_amount' => 0,
                'payment_method' => 'cash',
                'cashier_id' => Auth::id(),
                'status' => 'completed'
            ]);
            
            // Delete test record
            $testTransaction->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Transaction model test successful',
                'data' => [
                    'table_exists' => $tableExists,
                    'transaction_count' => $transactionCount,
                    'model_instantiated' => true,
                    'test_create_delete' => true
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaction model test failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Test search method
     */
    public function testSearch()
    {
        try {
            // Test with a sample barcode
            $sampleItem = Item::where('is_active', true)->first();
            
            if ($sampleItem) {
                return response()->json([
                    'success' => true,
                    'message' => 'Test search successful',
                    'sample_item' => [
                        'id' => $sampleItem->id,
                        'name' => $sampleItem->name,
                        'barcode' => $sampleItem->barcode,
                        'stock' => $sampleItem->stock_quantity
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No active items found in database'
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test search failed: ' . $e->getMessage(),
                'error' => $e->getTraceAsString()
            ], 500);
        }
    }

    /**
     * Display receipt for a transaction
     */
public function receipt(Transaction $transaction, Request $request)
    {
        try {
            $transaction->load([
                'transactionItems:id,transaction_id,item_name,quantity,unit_price,subtotal',
                'cashier:id,name'
            ]);

            $systemSettings = \App\Models\SystemSetting::pluck('value', 'key')->toArray();
            
            // Check if this is a copy request
            $isCopy = $request->has('copy') && $request->get('copy') == '1';
            
            return view('pages.kasir.receipt', compact('transaction', 'systemSettings', 'isCopy'));
        } catch (\Exception $e) {
            Log::error('Error displaying receipt:', [
                'transaction_id' => $transaction->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('kasir.index')
                ->with('error', 'Gagal menampilkan struk: ' . $e->getMessage());
        }
    }
}