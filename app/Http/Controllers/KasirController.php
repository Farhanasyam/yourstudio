<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Item;
use App\Models\Barcode;
use Illuminate\Http\Request;
use App\Models\User;
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

            if (!$request->has('total_amount') || $request->total_amount <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Total amount tidak valid'
                ], 400);
            }

            DB::beginTransaction();

            try {
                $paymentMethod = $this->convertPaymentMethod($request->payment_method ?? 'cash');
                $transactionCode = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);

                $transaction = Transaction::create([
                    'transaction_code' => $transactionCode,
                    'transaction_date' => now(),
                    'subtotal' => $request->total_amount,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $request->total_amount,
                    'paid_amount' => $request->paid_amount ?? $request->total_amount,
                    'change_amount' => $request->change_amount ?? 0,
                    'payment_method' => $paymentMethod,
                    'cashier_id' => Auth::id(),
                    'status' => 'completed'
                ]);

                $itemIds = array_column($request->items, 'id');
                $items = Item::whereIn('id', $itemIds)->get(['id', 'barcode'])->keyBy('id');
                $barcodesByItem = Barcode::whereIn('item_id', $itemIds)->where('is_active', true)->get(['item_id', 'barcode_value'])->groupBy('item_id');

                foreach ($request->items as $itemData) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'item_id' => $itemData['id'],
                        'item_name' => $itemData['name'],
                        'item_sku' => '',
                        'barcode_scanned' => '',
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['price'],
                        'discount_per_item' => 0,
                        'subtotal' => $itemData['subtotal']
                    ]);

                    $item = $items->get($itemData['id']);
                    if ($item) {
                        $item->decrement('stock_quantity', (int) $itemData['quantity']);
                        if ($item->barcode) {
                            Cache::forget('kasir_barcode_' . $item->barcode);
                        }
                        foreach ($barcodesByItem->get($item->id, []) as $b) {
                            Cache::forget('kasir_barcode_' . $b->barcode_value);
                        }
                    }
                }

                DB::commit();

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