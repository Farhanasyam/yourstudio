<?php

namespace App\Http\Controllers;

use App\Models\Barcode;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BarcodeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Barcode::with(['item', 'creator']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('barcode_value', 'like', "%{$search}%")
                  ->orWhere('barcode_type', 'like', "%{$search}%")
                  ->orWhereHas('item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('sku', 'like', "%{$search}%");
                  })
                  ->orWhereHas('creator', function($creatorQuery) use ($search) {
                      $creatorQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by barcode type
        if ($request->filled('barcode_type')) {
            $query->where('barcode_type', $request->barcode_type);
        }
        
        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        // Filter by print status
        if ($request->filled('is_printed')) {
            $query->where('is_printed', $request->is_printed);
        }
        
        // Filter by item
        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }
        
        $barcodes = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get items for filter
        $items = Item::orderBy('name')->get();
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.barcodes.partials.table', compact('barcodes'))->render(),
                'pagination' => $barcodes->links()->toHtml()
            ]);
        }
        
        return view('pages.barcodes.index', compact('barcodes', 'items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::whereDoesntHave('barcodes', function($query) {
            $query->where('is_active', true);
        })->get();
        
        return view('pages.barcodes.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Debug: Print request data
        \Log::info('Request data:', $request->all());

        // Custom validation rules based on barcode type
        $rules = [
            'item_id' => 'required|exists:items,id',
            'barcode_number' => 'required|string|max:255',
            'barcode_type' => 'required|in:CODE128,CODE39,EAN13,QR',
        ];

        // Additional validation for EAN13
        if ($request->barcode_type === 'EAN13') {
            $rules['barcode_number'] .= '|digits:13';
        }

        $validator = Validator::make($request->all(), $rules);

        // Custom validation messages
        $validator->setCustomMessages([
            'barcode_number.digits' => 'EAN13 barcode must be exactly 13 digits.',
        ]);

        if ($validator->fails()) {
            \Log::error('Validation failed:', $validator->errors()->toArray());
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cek apakah barcode number sudah ada
        $existingBarcode = Barcode::where('barcode_value', $request->barcode_number)->first();
        if ($existingBarcode) {
            return redirect()->back()
                ->withErrors(['barcode_number' => 'Barcode number already exists.'])
                ->withInput();
        }

        // Cek apakah item sudah memiliki barcode aktif
        $existingActiveBarcode = Barcode::where('item_id', $request->item_id)
            ->where('is_active', true)
            ->first();

        // Jika akan membuat barcode aktif dan sudah ada yang aktif
        if ($request->has('is_active') && $existingActiveBarcode) {
            return redirect()->back()
                ->withErrors(['item_id' => 'Item ini sudah memiliki barcode aktif. Hanya satu barcode yang boleh aktif per item.'])
                ->withInput();
        }

        try {
            $barcode = Barcode::create([
                'item_id' => $request->item_id,
                'barcode_value' => $request->barcode_number,
                'barcode_type' => $request->barcode_type,
                'is_active' => $request->has('is_active') ? true : false,
                'created_by' => Auth::id(),
                'is_printed' => false,
            ]);

            \Log::info('Barcode created:', $barcode->toArray());

            return redirect()->route('barcodes.index')
                ->with('success', 'Barcode created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating barcode:', ['error' => $e->getMessage()]);
            return redirect()->back()
                ->withErrors(['error' => 'Failed to create barcode: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Barcode $barcode)
    {
        $barcode->load(['item', 'creator']);
        return view('pages.barcodes.show', compact('barcode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Barcode $barcode)
    {
        $items = Item::all();
        return view('pages.barcodes.edit', compact('barcode', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Barcode $barcode)
    {
        // Custom validation rules based on barcode type
        $rules = [
            'item_id' => 'required|exists:items,id',
            'barcode_number' => 'required|string|max:255',
            'barcode_type' => 'required|in:CODE128,CODE39,EAN13,QR',
        ];

        // Additional validation for EAN13
        if ($request->barcode_type === 'EAN13') {
            $rules['barcode_number'] .= '|digits:13';
        }

        $validator = Validator::make($request->all(), $rules);

        // Custom validation messages
        $validator->setCustomMessages([
            'barcode_number.digits' => 'EAN13 barcode must be exactly 13 digits.',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Cek apakah barcode number sudah ada (kecuali untuk barcode yang sedang diedit)
        $duplicateBarcode = Barcode::where('barcode_value', $request->barcode_number)
            ->where('id', '!=', $barcode->id)
            ->first();

        if ($duplicateBarcode) {
            return redirect()->back()
                ->withErrors(['barcode_number' => 'Barcode number already exists.'])
                ->withInput();
        }

        // Cek apakah item sudah memiliki barcode aktif lain
        if ($request->has('is_active')) {
            $existingActiveBarcode = Barcode::where('item_id', $request->item_id)
                ->where('is_active', true)
                ->where('id', '!=', $barcode->id)
                ->first();

            if ($existingActiveBarcode) {
                // Nonaktifkan barcode lain untuk item yang sama
                $existingActiveBarcode->update(['is_active' => false]);
            }
        }

        $barcode->update([
            'item_id' => $request->item_id,
            'barcode_value' => $request->barcode_number,
            'barcode_type' => $request->barcode_type,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('barcodes.index')
            ->with('success', 'Barcode updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barcode $barcode)
    {
        // Hapus file gambar barcode jika ada
        if ($barcode->barcode_image_path && file_exists(storage_path('app/public/' . $barcode->barcode_image_path))) {
            unlink(storage_path('app/public/' . $barcode->barcode_image_path));
        }

        $barcode->delete();

        return redirect()->route('barcodes.index')
            ->with('success', 'Barcode deleted successfully.');
    }

    /**
     * Generate barcode for an item - improved version
     */
    public function generate(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'barcode_type' => 'sometimes|in:CODE128,CODE39,EAN13,QR'
        ]);

        $barcodeType = $request->input('barcode_type', 'CODE128');
        $barcodeValue = $this->generateBarcodeValue($barcodeType);

        $barcode = Barcode::create([
            'item_id' => $request->item_id,
            'barcode_value' => $barcodeValue,
            'barcode_type' => $barcodeType,
            'is_active' => false, // Default tidak aktif
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('barcodes.edit', $barcode)
            ->with('success', 'Barcode generated successfully.');
    }

    /**
     * Generate barcode value based on type
     */
    private function generateBarcodeValue($type)
    {
        switch ($type) {
            case 'EAN13':
                // Generate 13 digit EAN13
                do {
                    // Country code (2-3 digits) + manufacturer code + product code
                    $countryCode = '899'; // Indonesia country code
                    $manufacturerCode = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
                    $productCode = str_pad(random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
                    
                    // Calculate check digit
                    $code = $countryCode . $manufacturerCode . $productCode;
                    $checkDigit = $this->calculateEAN13CheckDigit($code);
                    $barcodeValue = $code . $checkDigit;
                    
                } while (Barcode::where('barcode_value', $barcodeValue)->exists());
                
                return $barcodeValue;

            case 'QR':
                // Generate QR code content (can be URL, text, etc.)
                do {
                    $barcodeValue = 'QR-' . strtoupper(Str::random(12));
                } while (Barcode::where('barcode_value', $barcodeValue)->exists());
                
                return $barcodeValue;

            case 'CODE39':
                // CODE39 can handle alphanumeric
                do {
                    $barcodeValue = strtoupper(Str::random(10));
                } while (Barcode::where('barcode_value', $barcodeValue)->exists());
                
                return $barcodeValue;

            default: // CODE128
                do {
                    $barcodeValue = 'BC' . time() . rand(100, 999);
                } while (Barcode::where('barcode_value', $barcodeValue)->exists());
                
                return $barcodeValue;
        }
    }

    /**
     * Calculate EAN13 check digit
     */
    private function calculateEAN13CheckDigit($code)
    {
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            $digit = (int) $code[$i];
            $sum += ($i % 2 === 0) ? $digit : $digit * 3;
        }
        $remainder = $sum % 10;
        return $remainder === 0 ? 0 : 10 - $remainder;
    }

    /**
     * Generate barcode via AJAX
     */
    public function generateAjax(Request $request)
    {
        $barcodeType = $request->input('type', 'CODE128');
        $barcodeValue = $this->generateBarcodeValue($barcodeType);

        return response()->json([
            'success' => true,
            'barcode_value' => $barcodeValue,
            'barcode_type' => $barcodeType
        ]);
    }

    /**
     * Search barcode by value
     */
    public function search(Request $request)
    {
        $request->validate([
            'barcode_value' => 'required|string'
        ]);

        $barcode = Barcode::where('barcode_value', $request->barcode_value)
            ->with(['item', 'creator'])
            ->first();

        if (!$barcode) {
            return response()->json([
                'success' => false,
                'message' => 'Barcode not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $barcode
        ]);
    }

    /**
     * Mark barcode as printed
     */
    public function markAsPrinted(Barcode $barcode)
    {
        $barcode->update([
            'is_printed' => true,
            'printed_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Barcode marked as printed.');
    }

    /**
     * Print barcode
     */
    public function print(Barcode $barcode)
    {
        $barcode->load(['item', 'creator']);
        
        // Mark as printed when accessed for printing
        if (!$barcode->is_printed) {
            $barcode->update([
                'is_printed' => true,
                'printed_at' => now(),
            ]);
        }
        
        return view('pages.barcodes.print', compact('barcode'));
    }

    /**
     * Show bulk print form
     */
    public function bulkPrintForm()
    {
        $barcodes = Barcode::with(['item'])
            ->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('pages.barcodes.bulk-print', compact('barcodes'));
    }

    /**
     * Print multiple barcodes
     */
    public function bulkPrint(Request $request)
    {
        $request->validate([
            'barcode_ids' => 'required|array|min:1',
            'barcode_ids.*' => 'exists:barcodes,id',
            'copies' => 'nullable|integer|min:1|max:100'
        ]);

        $barcodes = Barcode::with(['item'])
            ->whereIn('id', $request->barcode_ids)
            ->get();

        $copies = $request->input('copies', 1);

        // Mark selected barcodes as printed
        Barcode::whereIn('id', $request->barcode_ids)
            ->where('is_printed', false)
            ->update([
                'is_printed' => true,
                'printed_at' => now(),
            ]);

        return view('pages.barcodes.bulk-print-result', compact('barcodes', 'copies'));
    }

    /**
     * Show bulk generate form
     */
    public function bulkGenerateForm()
    {
        // Get items that don't have active barcodes
        $itemsWithoutBarcodes = Item::whereDoesntHave('barcodes', function($query) {
            $query->where('is_active', true);
        })->with('category')->orderBy('name')->get();

        // Get all items for option to regenerate
        $allItems = Item::with(['category', 'barcodes' => function($query) {
            $query->where('is_active', true);
        }])->orderBy('name')->get();

        return view('pages.barcodes.bulk-generate', compact('itemsWithoutBarcodes', 'allItems'));
    }

    /**
     * Generate barcodes for multiple items
     */
    public function bulkGenerate(Request $request)
    {
        $request->validate([
            'barcode_type' => 'required|in:CODE128,CODE39,EAN13,QR',
            'generation_mode' => 'required|in:missing_only,all_items,selected_items',
            'item_ids' => 'required_if:generation_mode,selected_items|array',
            'item_ids.*' => 'exists:items,id',
            'replace_existing' => 'sometimes|boolean'
        ]);

        $barcodeType = $request->barcode_type;
        $generationMode = $request->generation_mode;
        $replaceExisting = $request->boolean('replace_existing', false);
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();

            // Determine which items to process
            $items = $this->getItemsForBulkGeneration($generationMode, $request->item_ids);

            foreach ($items as $item) {
                try {
                    // Check if item already has active barcode
                    $existingBarcode = Barcode::where('item_id', $item->id)
                        ->where('is_active', true)
                        ->first();

                    if ($existingBarcode && !$replaceExisting) {
                        continue; // Skip if already has barcode and not replacing
                    }

                    // Deactivate existing barcode if replacing
                    if ($existingBarcode && $replaceExisting) {
                        $existingBarcode->update(['is_active' => false]);
                    }

                    // Generate new barcode
                    $barcodeValue = $this->generateBarcodeValue($barcodeType);

                    Barcode::create([
                        'item_id' => $item->id,
                        'barcode_value' => $barcodeValue,
                        'barcode_type' => $barcodeType,
                        'is_active' => true,
                        'created_by' => Auth::id(),
                        'is_printed' => false,
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Item '{$item->name}': " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            // Prepare response message
            $message = "Bulk barcode generation completed. {$successCount} barcodes generated";
            if ($errorCount > 0) {
                $message .= ", {$errorCount} errors";
                session()->flash('error_details', implode("\n", array_slice($errors, 0, 10)));
                if (count($errors) > 10) {
                    session()->flash('error_details', session('error_details') . "\n... and " . (count($errors) - 10) . " more errors");
                }
                return redirect()->route('barcodes.index')->with('warning', $message);
            }

            return redirect()->route('barcodes.index')->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Bulk generation failed: ' . $e->getMessage());
        }
    }

    /**
     * Get items for bulk generation based on mode
     */
    private function getItemsForBulkGeneration($mode, $itemIds = null)
    {
        switch ($mode) {
            case 'missing_only':
                return Item::whereDoesntHave('barcodes', function($query) {
                    $query->where('is_active', true);
                })->get();

            case 'all_items':
                return Item::all();

            case 'selected_items':
                return Item::whereIn('id', $itemIds ?? [])->get();

            default:
                return collect();
        }
    }

    /**
     * Get barcode generation statistics
     */
    public function getGenerationStats()
    {
        $totalItems = Item::count();
        $itemsWithBarcodes = Item::whereHas('barcodes', function($query) {
            $query->where('is_active', true);
        })->count();
        $itemsWithoutBarcodes = $totalItems - $itemsWithBarcodes;

        return response()->json([
            'total_items' => $totalItems,
            'items_with_barcodes' => $itemsWithBarcodes,
            'items_without_barcodes' => $itemsWithoutBarcodes,
            'completion_percentage' => $totalItems > 0 ? round(($itemsWithBarcodes / $totalItems) * 100, 2) : 0
        ]);
    }
}