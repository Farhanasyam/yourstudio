<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Item::with(['category', 'supplier']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhereHas('category', function($categoryQuery) use ($search) {
                      $categoryQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('supplier', function($supplierQuery) use ($search) {
                      $supplierQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Filter by stock status
        if ($request->filled('stock_status')) {
            switch ($request->stock_status) {
                case 'in_stock':
                    $query->where('stock_quantity', '>', 0);
                    break;
                case 'out_of_stock':
                    $query->where('stock_quantity', '<=', 0);
                    break;
                case 'low_stock':
                    $query->whereRaw('stock_quantity <= minimum_stock');
                    break;
            }
        }
        
        // Filter by price range
        if ($request->filled('price_min')) {
            $query->where('selling_price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('selling_price', '<=', $request->price_max);
        }
        
        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        
        $items = $query->orderBy('name')->paginate(15);
        
        // Get categories and suppliers for filters
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.items.partials.table', compact('items'))->render(),
                'pagination' => $items->links()->toHtml()
            ]);
        }
        
        return view('pages.items.index', compact('items', 'categories', 'suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('pages.items.create', compact('categories', 'suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:items',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Item::create($request->all());

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load(['category', 'supplier']);
        return view('pages.items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('pages.items.edit', compact('item', 'categories', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:100|unique:items,sku,' . $item->id,
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'minimum_stock' => 'required|integer|min:0',
            'unit' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $item->update($request->all());

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        try {
            // Delete related barcodes first
            $item->barcodes()->delete();
            
            // Delete related stock in details
            $item->stockInDetails()->delete();
            
            // Delete related sale details
            $item->saleDetails()->delete();
            
            // Delete related stock adjustments
            $item->stockAdjustments()->delete();
            
            // Now delete the item
            $item->delete();

            return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('items.index')->with('error', 'Failed to delete item: ' . $e->getMessage());
        }
    }

    /**
     * Show the import form
     */
    public function showImport()
    {
        return view('pages.items.import');
    }

    /**
     * Test import functionality with sample data
     */
    public function testImport()
    {
        try {
            // Create test data
            $testData = [
                ['Test Category', 'Test Product 1', 'Variant A', '10000', 'TEST-001'],
                ['Test Category', 'Test Product 2', 'Variant B', '20000', 'TEST-002'],
            ];
            
            $result = $this->processImportData($testData, 10000);
            
            return response()->json([
                'success' => true,
                'message' => "Test import: {$result['success']} items created, {$result['errors']} errors",
                'data' => $result
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test import failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Import items from Excel file
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            'default_purchase_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            $file = $request->file('excel_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('imports', $fileName, 'public');
            $fullPath = storage_path('app/public/' . $filePath);

            // Parse Excel file based on extension
            $extension = $file->getClientOriginalExtension();
            if ($extension === 'csv') {
                $data = $this->parseCsv($fullPath);
            } else {
                $data = $this->parseExcel($fullPath);
            }

            if (empty($data)) {
                Storage::disk('public')->delete($filePath);
                return redirect()->back()->with('error', 'No valid data found in the file. Please check the file format and ensure it has data rows after the header.');
            }

            // Debug: Log the parsed data count
            \Log::info('Import: Parsed ' . count($data) . ' rows from file: ' . $file->getClientOriginalName());

            $defaultPurchasePrice = $request->input('default_purchase_price', 10000);
            $result = $this->processImportData($data, $defaultPurchasePrice);

            // Clean up uploaded file
            Storage::disk('public')->delete($filePath);

            if ($result['errors'] > 0) {
                $message = "Import completed with {$result['success']} items imported and {$result['errors']} errors.";
                if (!empty($result['error_details'])) {
                    $errorDetails = implode("\n", array_slice($result['error_details'], 0, 5)); // Show first 5 errors
                    if (count($result['error_details']) > 5) {
                        $errorDetails .= "\n... and " . (count($result['error_details']) - 5) . " more errors";
                    }
                    session()->flash('error_details', $errorDetails);
                }
                return redirect()->route('items.index')->with('warning', $message);
            }

            return redirect()->route('items.index')->with('success', "Successfully imported {$result['success']} items.");

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Parse CSV file
     */
    private function parseCsv($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, 'r')) !== FALSE) {
            // Set locale for proper CSV parsing
            setlocale(LC_ALL, 'en_US.UTF-8');
            
            $header = fgetcsv($handle, 1000, ','); // Skip header row
            $rowNumber = 1;
            
            while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $rowNumber++;
                
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }
                
                // Ensure we have at least 5 columns, pad with empty strings if needed
                while (count($row) < 5) {
                    $row[] = '';
                }
                
                // Clean each cell data
                $cleanRow = [];
                foreach ($row as $cell) {
                    $cleanRow[] = trim(str_replace(['"', "'"], '', $cell));
                }
                
                $data[] = $cleanRow;
            }
            fclose($handle);
        }
        return $data;
    }

    /**
     * Parse Excel file (XLSX/XLS) using built-in PHP functions
     */
    private function parseExcel($filePath)
    {
        $data = [];
        
        // Simple Excel parsing using XMLReader for XLSX files
        if (pathinfo($filePath, PATHINFO_EXTENSION) === 'xlsx') {
            $zip = new \ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $worksheetData = $zip->getFromName('xl/worksheets/sheet1.xml');
                $sharedStringsData = $zip->getFromName('xl/sharedStrings.xml');
                $zip->close();

                // Parse shared strings
                $sharedStrings = [];
                if ($sharedStringsData) {
                    $xml = simplexml_load_string($sharedStringsData);
                    if ($xml) {
                        foreach ($xml->si as $si) {
                            $sharedStrings[] = (string)$si->t;
                        }
                    }
                }

                // Parse worksheet data
                if ($worksheetData) {
                    $xml = simplexml_load_string($worksheetData);
                    if ($xml) {
                        $rows = [];
                        foreach ($xml->sheetData->row as $row) {
                            $rowData = [];
                            foreach ($row->c as $cell) {
                                $value = '';
                                if ((string)$cell['t'] === 's') {
                                    // Shared string reference
                                    $index = (int)$cell->v;
                                    $value = isset($sharedStrings[$index]) ? $sharedStrings[$index] : '';
                                } else {
                                    $value = (string)$cell->v;
                                }
                                $rowData[] = $value;
                            }
                            $rows[] = $rowData;
                        }
                        
                        // Skip header row and filter rows with sufficient data
                        for ($i = 1; $i < count($rows); $i++) {
                            if (count($rows[$i]) >= 5) {
                                $data[] = $rows[$i];
                            }
                        }
                    }
                }
            }
        } else {
            // For XLS files, try to read as CSV (fallback)
            return $this->parseCsv($filePath);
        }

        return $data;
    }

    /**
     * Process imported data and create items
     */
    private function processImportData($data, $defaultPurchasePrice = 10000)
    {
        $successCount = 0;
        $errorCount = 0;
        $errors = [];
        
        foreach ($data as $rowIndex => $row) {
            DB::beginTransaction();
            
            try {
                // Expected columns: Kategori Produk, Nama Produk, Nama Varian, Harga Produk, SKU Master
                $categoryName = trim($row[0] ?? '');
                $productName = trim($row[1] ?? '');
                $variantName = trim($row[2] ?? '');
                $productPrice = trim($row[3] ?? '');
                $skuMaster = trim($row[4] ?? '');

                // Skip empty rows
                if (empty($productName) || empty($categoryName)) {
                    DB::rollback();
                    continue;
                }

                // Find or create category
                $category = Category::where('name', $categoryName)->first();
                if (!$category) {
                    // Truncate category name if too long
                    $truncatedName = strlen($categoryName) > 255 ? substr($categoryName, 0, 255) : $categoryName;
                    
                    // Generate unique code for new category
                    $code = $this->generateCategoryCode($categoryName);
                    
                    $category = Category::create([
                        'name' => $truncatedName,
                        'description' => 'Auto-created from import',
                        'code' => $code,
                    ]);
                }

                // Combine product name and variant if variant exists
                $fullName = $productName;
                if (!empty($variantName)) {
                    $fullName .= ' - ' . $variantName;
                }

                // Clean and convert price
                $price = $this->cleanPrice($productPrice);
                
                // Validate price
                if ($price <= 0) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Invalid price format";
                    $errorCount++;
                    DB::rollback();
                    continue;
                }
                
                // Generate SKU if not provided
                if (empty($skuMaster)) {
                    $skuMaster = 'SKU-' . strtoupper(substr(uniqid(), -8));
                }

                // Check if SKU already exists and make it unique
                $originalSku = $skuMaster;
                $counter = 1;
                while (Item::where('sku', $skuMaster)->exists()) {
                    $skuMaster = $originalSku . '-' . $counter;
                    $counter++;
                }

                // Validate required fields
                if (strlen($fullName) > 255) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Product name too long";
                    $errorCount++;
                    DB::rollback();
                    continue;
                }

                if (strlen($skuMaster) > 100) {
                    $errors[] = "Row " . ($rowIndex + 2) . ": SKU too long";
                    $errorCount++;
                    DB::rollback();
                    continue;
                }

                // Create item
                $item = Item::create([
                    'name' => $fullName,
                    'sku' => $skuMaster,
                    'description' => !empty($variantName) ? "Variant: {$variantName}" : null,
                    'category_id' => $category->id,
                    'purchase_price' => $defaultPurchasePrice, // Default purchase price dari form
                    'selling_price' => $price,
                    'stock_quantity' => 0,
                    'minimum_stock' => 1,
                    'unit' => 'pcs',
                    'is_active' => true,
                ]);

                if ($item) {
                    $successCount++;
                    DB::commit();
                } else {
                    $errors[] = "Row " . ($rowIndex + 2) . ": Failed to create item";
                    $errorCount++;
                    DB::rollback();
                }

            } catch (\Exception $e) {
                DB::rollback();
                $errors[] = "Row " . ($rowIndex + 2) . ": " . $e->getMessage();
                $errorCount++;
            }
        }

        return [
            'success' => $successCount,
            'errors' => $errorCount,
            'error_details' => $errors
        ];
    }

    /**
     * Clean price string and convert to number
     * Supports Indonesian format: 15.000, 1.500.000, 1.500.000,50
     * Supports English format: 15000, 1500000, 1500000.50
     */
    private function cleanPrice($priceString)
    {
        if (empty($priceString)) {
            return 0;
        }
        
        // Remove currency symbols, spaces, and other non-numeric characters except comma and dot
        $cleanPrice = preg_replace('/[^\d,.]/', '', $priceString);
        
        // Handle Indonesian number format (15.000, 1.500.000, 1.500.000,50)
        if (substr_count($cleanPrice, '.') > 1) {
            // Format: 1.500.000,50 or 1.500.000
            // Remove dots (thousands separator) and convert comma to dot for decimal
            $cleanPrice = str_replace('.', '', $cleanPrice);
            $cleanPrice = str_replace(',', '.', $cleanPrice);
        } elseif (substr_count($cleanPrice, '.') === 1 && substr_count($cleanPrice, ',') === 1) {
            // Format: 1.500,50 (dot as thousands separator, comma as decimal)
            $cleanPrice = str_replace('.', '', $cleanPrice);
            $cleanPrice = str_replace(',', '.', $cleanPrice);
        } elseif (substr_count($cleanPrice, '.') === 1 && substr_count($cleanPrice, ',') === 0) {
            // Format: 15000.50 or 15.000 (need to check if it's decimal or thousands separator)
            $parts = explode('.', $cleanPrice);
            if (strlen($parts[1]) <= 2) {
                // Likely decimal format (15000.50)
                // Keep as is
            } else {
                // Likely thousands separator format (15.000)
                $cleanPrice = str_replace('.', '', $cleanPrice);
            }
        } elseif (substr_count($cleanPrice, ',') > 1) {
            // English format with comma as thousands separator (1,500,000)
            $cleanPrice = str_replace(',', '', $cleanPrice);
        } elseif (substr_count($cleanPrice, ',') === 1 && substr_count($cleanPrice, '.') === 0) {
            // Format: 15000,50 (comma as decimal separator)
            $cleanPrice = str_replace(',', '.', $cleanPrice);
        }
        
        return (float) $cleanPrice;
    }

    /**
     * Test cleanPrice function with different formats
     */
    public function testCleanPrice()
    {
        $testCases = [
            '15.000' => 15000,
            '1.500.000' => 1500000,
            '1.500.000,50' => 1500000.50,
            '15000' => 15000,
            '1500000' => 1500000,
            '15000.50' => 15000.50,
            '15,000' => 15000,
            '1,500,000' => 1500000,
            '15000,50' => 15000.50,
            'Rp 15.000' => 15000,
            'Rp 1.500.000' => 1500000,
            '15.000 rupiah' => 15000,
        ];

        $results = [];
        foreach ($testCases as $input => $expected) {
            $actual = $this->cleanPrice($input);
            $results[] = [
                'input' => $input,
                'expected' => $expected,
                'actual' => $actual,
                'correct' => $actual == $expected
            ];
        }

        return response()->json([
            'success' => true,
            'test_results' => $results
        ]);
    }

    /**
     * Generate unique code for category
     */
    private function generateCategoryCode($categoryName)
    {
        // Remove non-alphabetic characters and get first letters of words
        $words = explode(' ', $categoryName);
        $code = '';
        
        foreach ($words as $word) {
            $cleanWord = preg_replace('/[^A-Za-z]/', '', $word);
            if (!empty($cleanWord)) {
                $code .= strtoupper(substr($cleanWord, 0, 1));
            }
        }
        
        // If code is less than 3 characters, pad with first letters
        if (strlen($code) < 3) {
            $cleanName = preg_replace('/[^A-Za-z]/', '', $categoryName);
            $code = strtoupper(substr($cleanName, 0, 3));
        }
        
        // If still less than 3, pad with 'X'
        $code = str_pad($code, 3, 'X');
        
        // Add random number to make it unique
        $baseCode = substr($code, 0, 3);
        $finalCode = $baseCode . rand(10, 99);
        
        // Ensure uniqueness
        while (Category::where('code', $finalCode)->exists()) {
            $finalCode = $baseCode . rand(10, 99);
        }
        
        return $finalCode;
    }



    /**
     * Bulk delete items
     */
    public function bulkDelete(Request $request)
    {
        try {
            $request->validate([
                'item_ids' => 'required|array',
                'item_ids.*' => 'integer|exists:items,id'
            ]);

            $itemIds = $request->input('item_ids');
            $deletedCount = 0;
            $errors = [];

            foreach ($itemIds as $itemId) {
                try {
                    $item = Item::find($itemId);
                    
                    if ($item) {
                        // Delete related barcodes first
                        $item->barcodes()->delete();
                        
                        // Delete related stock in details
                        $item->stockInDetails()->delete();
                        
                        // Delete related sale details
                        $item->saleDetails()->delete();
                        
                        // Delete related stock adjustments
                        $item->stockAdjustments()->delete();
                        
                        // Now delete the item
                        $item->delete();
                        $deletedCount++;
                    } else {
                        $errors[] = "Item with ID {$itemId} not found.";
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to delete item ID {$itemId}: " . $e->getMessage();
                }
            }

            if ($deletedCount > 0) {
                $message = "Successfully deleted {$deletedCount} item(s).";
                if (!empty($errors)) {
                    $message .= " " . count($errors) . " item(s) could not be deleted.";
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
                    'message' => 'No items were deleted. ' . implode(' ', $errors)
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing bulk delete: ' . $e->getMessage()
            ]);
        }
    }
}