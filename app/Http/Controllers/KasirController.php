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
use App\Notifications\OfflineSaleReviewNotification;
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
     * Everything the cashier page needs to work without asking the server per scan:
     * active items with all their barcodes, the receipt settings and a fresh CSRF token.
     * The page keeps this in the browser so scanning keeps working offline.
     */
    public function catalog()
    {
        $items = Item::where('is_active', true)
            ->with(['barcodes:item_id,barcode_value'])
            ->orderBy('name')
            ->get(['id', 'name', 'barcode', 'selling_price', 'stock_quantity', 'unit'])
            ->map(function ($item) {
                $codes = $item->barcodes->pluck('barcode_value');
                if ($item->barcode) {
                    $codes->prepend($item->barcode);
                }
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'price' => (float) $item->selling_price,
                    'stock' => (int) $item->stock_quantity,
                    'unit' => $item->unit,
                    'codes' => $codes->unique()->values(),
                ];
            });

        $settings = SystemSetting::whereIn('key', [
            'store_name', 'store_address', 'store_phone', 'store_instagram',
            'receipt_header', 'receipt_footer', 'auto_print_receipt',
        ])->pluck('value', 'key');

        return response()->json([
            'items' => $items,
            'settings' => $settings,
            'cashier' => ['id' => Auth::id(), 'name' => Auth::user()->name],
            'csrf_token' => csrf_token(),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Store a sale.
     *
     * Online sales are checked strictly (price from the database, enough stock, enough payment).
     * Sales made while offline (`offline: true`, sent later from the browser queue) already happened:
     * the customer paid and took the goods. They are always recorded with the price the cashier saw;
     * anything that no longer adds up (stock going negative, a changed price) is written to the notes,
     * the sale is marked `needs_review` and admins are notified.
     *
     * Every request may carry a `client_uuid`; sending the same one again returns the sale that was
     * already saved instead of creating a duplicate (safe retries after a lost response).
     */
    public function store(Request $request)
    {
        try {
            if ($request->isJson()) {
                $request->merge($request->json()->all());
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
                'items.*.price' => 'nullable|numeric|min:0',
                'client_uuid' => 'nullable|uuid',
                'offline' => 'nullable|boolean',
                'transaction_code' => ['nullable', 'string', 'max:50', 'regex:/^OFF-[A-Z0-9-]+$/'],
                'created_at' => 'nullable|date',
            ]);

            // Same sale sent again (retry, or queue sync after a response that got lost)
            if ($request->filled('client_uuid')) {
                $existing = Transaction::where('client_uuid', $request->client_uuid)->first();
                if ($existing) {
                    if ($existing->cashier_id !== Auth::id()) {
                        return response()->json(['success' => false, 'message' => 'Kode transaksi sudah dipakai kasir lain.'], 409);
                    }
                    return $this->saleResponse($existing, true);
                }
            }

            $offline = $request->boolean('offline');

            // Merge duplicate lines so stock is checked against the combined quantity
            $quantities = [];
            $offlinePrices = [];
            foreach ($request->items as $itemData) {
                $quantities[$itemData['id']] = ($quantities[$itemData['id']] ?? 0) + (int) $itemData['quantity'];
                if (isset($itemData['price']) && !isset($offlinePrices[$itemData['id']])) {
                    $offlinePrices[$itemData['id']] = (float) $itemData['price'];
                }
            }

            DB::beginTransaction();

            try {
                // Lock the rows so two cashiers can't sell the same last unit at once.
                // Offline sales already happened, so an item deactivated meanwhile is still recorded.
                $items = Item::whereIn('id', array_keys($quantities))
                    ->when(!$offline, fn ($q) => $q->where('is_active', true))
                    ->lockForUpdate()
                    ->get(['id', 'name', 'sku', 'barcode', 'selling_price', 'stock_quantity', 'minimum_stock', 'unit'])
                    ->keyBy('id');

                $total = 0;
                $unitPrices = [];
                $issues = [];
                foreach ($quantities as $itemId => $qty) {
                    $item = $items->get($itemId);
                    if (!$item) {
                        DB::rollBack();
                        return response()->json(['success' => false, 'message' => 'Produk tidak ditemukan atau tidak aktif'], 422);
                    }

                    $dbPrice = (float) $item->selling_price;
                    if ($offline) {
                        // What the customer was actually charged
                        $unitPrice = ($offlinePrices[$itemId] ?? 0) > 0 ? $offlinePrices[$itemId] : $dbPrice;
                        if (abs($unitPrice - $dbPrice) >= 0.01) {
                            $issues[] = "Harga {$item->name} saat offline Rp " . number_format($unitPrice, 0, ',', '.')
                                . ", harga sekarang Rp " . number_format($dbPrice, 0, ',', '.');
                        }
                        if ($item->stock_quantity < $qty) {
                            $issues[] = "Stok {$item->name} menjadi " . ($item->stock_quantity - $qty)
                                . " (stok {$item->stock_quantity}, terjual {$qty})";
                        }
                    } else {
                        // Online: prices and stock always come from the database, never from the browser
                        if ($dbPrice <= 0) {
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
                        $unitPrice = $dbPrice;
                    }
                    $unitPrices[$itemId] = $unitPrice;
                    $total += $unitPrice * $qty;
                }

                $paid = (float) ($request->paid_amount ?? $total);
                if ($paid < $total) {
                    if (!$offline) {
                        DB::rollBack();
                        return response()->json([
                            'success' => false,
                            'message' => 'Pembayaran kurang dari total belanja (Rp ' . number_format($total, 0, ',', '.') . ')'
                        ], 422);
                    }
                    $issues[] = 'Pembayaran Rp ' . number_format($paid, 0, ',', '.') . ' kurang dari total Rp ' . number_format($total, 0, ',', '.');
                }

                // Offline receipts were already printed with the browser's code, keep it
                $transactionCode = null;
                if ($offline && $request->filled('transaction_code')
                    && !Transaction::where('transaction_code', $request->transaction_code)->exists()) {
                    $transactionCode = $request->transaction_code;
                }
                $transactionCode = $transactionCode ?? 'TRX-' . date('YmdHis') . '-' . rand(1000, 9999);

                // Offline sales keep the time they happened (within a sane window)
                $transactionDate = now();
                if ($offline && $request->filled('created_at')) {
                    $soldAt = Carbon::parse($request->created_at)->setTimezone(config('app.timezone'));
                    if ($soldAt->between(now()->subDays(30), now()->addMinutes(10))) {
                        $transactionDate = $soldAt;
                    }
                }

                $notes = null;
                if ($offline) {
                    $notes = 'Transaksi offline, disinkron ' . now()->format('d-m-Y H:i');
                    if ($issues) {
                        $notes .= "\nPerlu dicek: " . implode('; ', $issues);
                    }
                }

                $paymentMethod = $this->convertPaymentMethod($request->payment_method ?? 'cash');

                $transaction = Transaction::create([
                    'transaction_code' => $transactionCode,
                    'client_uuid' => $request->client_uuid,
                    'transaction_date' => $transactionDate,
                    'subtotal' => $total,
                    'discount_amount' => 0,
                    'tax_amount' => 0,
                    'total_amount' => $total,
                    'paid_amount' => $paid,
                    'change_amount' => max(0, $paid - $total),
                    'payment_method' => $paymentMethod,
                    'cashier_id' => Auth::id(),
                    'status' => 'completed',
                    'is_offline' => $offline,
                    'needs_review' => !empty($issues),
                    'notes' => $notes,
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
                        'unit_price' => $unitPrices[$itemId],
                        'discount_per_item' => 0,
                        'subtotal' => $unitPrices[$itemId] * $qty
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
            } catch (\Illuminate\Database\QueryException $e) {
                DB::rollBack();
                // Two copies of the same queued sale arriving at once: the unique client_uuid wins
                if ($request->filled('client_uuid') && ($existing = Transaction::where('client_uuid', $request->client_uuid)->first())) {
                    return $this->saleResponse($existing, true);
                }
                throw $e;
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Transaction store error:', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                throw $e;
            }

            // After commit, so a rolled-back sale never produces a notification
            $this->notifyLowStock($items, $stockBefore);
            if ($issues) {
                $this->notifyOfflineReview($transaction, $issues);
            }

            return $this->saleResponse($transaction, false);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data transaksi tidak valid: ' . collect($e->errors())->flatten()->first(),
            ], 422);
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

    private function saleResponse(Transaction $transaction, bool $duplicate)
    {
        return response()->json([
            'success' => true,
            'message' => $duplicate ? 'Transaksi sudah tersimpan sebelumnya' : 'Transaksi berhasil disimpan',
            'duplicate' => $duplicate,
            'transaction_id' => $transaction->id,
            'transaction_code' => $transaction->transaction_code,
            'needs_review' => (bool) $transaction->needs_review,
        ]);
    }

    /**
     * Tell admins about an offline sale that didn't add up when it was synced.
     */
    private function notifyOfflineReview(Transaction $transaction, array $issues)
    {
        try {
            $recipients = User::whereIn('role', ['admin', 'superadmin'])
                ->where('is_active', true)
                ->where('approval_status', 'approved')
                ->get();
            foreach ($recipients as $user) {
                $user->notify(new OfflineSaleReviewNotification($transaction, $issues));
            }
        } catch (\Exception $e) {
            Log::error('Offline review notification failed: ' . $e->getMessage());
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