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
        try {
            $request->validate([
                'barcode' => 'required|string'
            ]);

            $barcode = trim($request->barcode);
            
            // Log untuk debugging
            Log::info('Searching barcode:', [
                'barcode' => $barcode,
                'request_data' => $request->all(),
                'headers' => $request->headers->all()
            ]);
            
            // Cari di tabel items terlebih dahulu (lebih cepat)
            $item = Item::where('barcode', $barcode)
                       ->where('is_active', true)
                       ->with('category')
                       ->first();

            // Log hasil pencarian di items
            Log::info('Item search result:', [
                'barcode' => $barcode,
                'found_in_items' => $item ? true : false,
                'item_data' => $item ? $item->toArray() : null
            ]);

            if (!$item) {
                // Jika tidak ditemukan di items, cari di barcodes
                $barcodeRecord = Barcode::where('barcode_value', $barcode)
                                      ->where('is_active', true)
                                      ->first();

                // Log hasil pencarian di barcodes
                Log::info('Barcode search result:', [
                    'barcode' => $barcode,
                    'found_in_barcodes' => $barcodeRecord ? true : false,
                    'barcode_data' => $barcodeRecord ? $barcodeRecord->toArray() : null
                ]);

                if ($barcodeRecord) {
                    $item = Item::where('id', $barcodeRecord->item_id)
                               ->where('is_active', true)
                               ->with('category')
                               ->first();

                    // Log hasil pencarian item dari barcode
                    Log::info('Item from barcode search result:', [
                        'barcode' => $barcode,
                        'item_found' => $item ? true : false,
                        'item_data' => $item ? $item->toArray() : null
                    ]);
                }
            }
            
            Log::info('Search result:', [
                'barcode' => $barcode,
                'item_found' => $item ? true : false,
                'item_data' => $item ? $item->toArray() : null
            ]);

            if ($item) {
                // Check stock
                if ($item->stock_quantity <= 0) {
                    Log::warning('Item found but out of stock:', [
                        'item_id' => $item->id,
                        'stock' => $item->stock_quantity
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok produk habis',
                        'type' => 'error'
                    ]);
                }

                // Prepare response data
                $responseData = [
                    'id' => $item->id,
                    'name' => $item->name,
                    'barcode' => $barcode,
                    'selling_price' => $item->selling_price,
                    'stock_quantity' => $item->stock_quantity,
                    'category' => $item->category ? $item->category->name : 'General'
                ];

                Log::info('Returning item data:', $responseData);

                return response()->json([
                    'success' => true,
                    'data' => $responseData,
                    'message' => 'Produk ditemukan'
                ]);
            } else {
                Log::info('Item not found for barcode:', ['barcode' => $barcode]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Produk tidak ditemukan! Pastikan barcode terdaftar dan produk aktif.',
                    'type' => 'error'
                ]);
            }
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
     * Store transaction
     */
    public function store(Request $request)
    {
        try {
            Log::info('=== START TRANSACTION STORE ===');
            Log::info('Request data:', $request->all());
            Log::info('Request headers:', $request->headers->all());
            
            // Handle JSON input
            if ($request->isJson()) {
                $data = $request->json()->all();
                $request->merge($data);
            }
            
            // Basic validation
            if (!$request->has('items') || empty($request->items)) {
                Log::warning('Items validation failed: items empty or missing');
                return response()->json([
                    'success' => false,
                    'message' => 'Items tidak boleh kosong'
                ], 400);
            }

            if (!$request->has('total_amount') || $request->total_amount <= 0) {
                Log::warning('Total amount validation failed:', ['total_amount' => $request->total_amount]);
                return response()->json([
                    'success' => false,
                    'message' => 'Total amount tidak valid'
                ], 400);
            }

            Log::info('Validation passed, starting database transaction');

            DB::beginTransaction();

            try {
                // Convert payment method to match enum
                $paymentMethod = $this->convertPaymentMethod($request->payment_method);
                Log::info('Payment method converted:', ['original' => $request->payment_method, 'converted' => $paymentMethod]);
                
                // Generate simple transaction code
                $transactionCode = 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);
                Log::info('Generated transaction code:', ['code' => $transactionCode]);

                // Prepare transaction data
                $transactionData = [
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
                ];

                Log::info('Transaction data prepared:', $transactionData);

                // Create transaction with minimal fields
                $transaction = Transaction::create($transactionData);

                Log::info('Transaction created successfully:', $transaction->toArray());

                // Create transaction items
                foreach ($request->items as $index => $itemData) {
                    Log::info("Creating transaction item {$index}:", $itemData);
                    
                    $transactionItemData = [
                        'transaction_id' => $transaction->id,
                        'item_id' => $itemData['id'],
                        'item_name' => $itemData['name'],
                        'item_sku' => '',
                        'barcode_scanned' => '',
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['price'],
                        'discount_per_item' => 0,
                        'subtotal' => $itemData['subtotal']
                    ];

                    Log::info("Transaction item data for item {$index}:", $transactionItemData);

                    $transactionItem = TransactionItem::create($transactionItemData);
                    Log::info("Transaction item {$index} created:", $transactionItem->toArray());

                    // Update stock safely
                    try {
                        $item = Item::find($itemData['id']);
                        if ($item) {
                            $oldStock = $item->stock_quantity;
                            $newStock = max(0, $oldStock - $itemData['quantity']);
                            $item->update(['stock_quantity' => $newStock]);
                            Log::info('Stock updated for item:', [
                                'item_id' => $item->id,
                                'old_stock' => $oldStock,
                                'new_stock' => $newStock,
                                'quantity_sold' => $itemData['quantity']
                            ]);
                        } else {
                            Log::warning('Item not found for stock update:', ['item_id' => $itemData['id']]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Failed to update stock:', [
                            'item_id' => $itemData['id'],
                            'error' => $e->getMessage()
                        ]);
                        // Continue with transaction even if stock update fails
                    }
                }

                Log::info('All transaction items created, committing transaction');
                DB::commit();

                Log::info('Transaction stored successfully:', [
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan',
                    'transaction_id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code
                ]);

            } catch (\Exception $e) {
                Log::error('Error in transaction creation:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('=== ERROR IN TRANSACTION STORE ===');
            Log::error('Error details:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan transaksi: ' . $e->getMessage(),
                'debug_info' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
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
            // Load transaction with its items and cashier
            $transaction->load(['transactionItems.item', 'cashier']);
            
            // Get system settings for receipt
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