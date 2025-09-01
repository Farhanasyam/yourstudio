<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\StockIn;
use App\Models\Barcode;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global search across multiple entities
     */
    public function globalSearch(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $results = [];
        
        // Search in items
        if ($type === 'all' || $type === 'items') {
            $items = Item::with(['category', 'supplier'])
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('sku', 'like', "%{$query}%")
                      ->orWhere('barcode', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
                
            foreach ($items as $item) {
                $results[] = [
                    'type' => 'item',
                    'id' => $item->id,
                    'title' => $item->name,
                    'subtitle' => $item->sku,
                    'description' => $item->category->name ?? 'No Category',
                    'url' => route('items.show', $item),
                    'icon' => 'ni ni-box-2',
                    'color' => 'bg-gradient-warning'
                ];
            }
        }
        
        // Search in categories
        if ($type === 'all' || $type === 'categories') {
            $categories = Category::withCount('items')
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhere('code', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
                
            foreach ($categories as $category) {
                $results[] = [
                    'type' => 'category',
                    'id' => $category->id,
                    'title' => $category->name,
                    'subtitle' => $category->code,
                    'description' => $category->items_count . ' items',
                    'url' => route('categories.show', $category),
                    'icon' => 'ni ni-tag',
                    'color' => 'bg-gradient-primary'
                ];
            }
        }
        
        // Search in suppliers
        if ($type === 'all' || $type === 'suppliers') {
            $suppliers = Supplier::withCount('items')
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                      ->orWhere('contact_person', 'like', "%{$query}%")
                      ->orWhere('phone', 'like', "%{$query}%")
                      ->orWhere('email', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
                
            foreach ($suppliers as $supplier) {
                $results[] = [
                    'type' => 'supplier',
                    'id' => $supplier->id,
                    'title' => $supplier->name,
                    'subtitle' => $supplier->contact_person ?? 'No Contact',
                    'description' => $supplier->items_count . ' items',
                    'url' => route('suppliers.show', $supplier),
                    'icon' => 'fas fa-truck',
                    'color' => 'bg-gradient-info'
                ];
            }
        }
        
        // Search in transactions
        if ($type === 'all' || $type === 'transactions') {
            $transactions = Transaction::with(['cashier'])
                ->where(function($q) use ($query) {
                    $q->where('transaction_number', 'like', "%{$query}%")
                      ->orWhere('customer_name', 'like', "%{$query}%")
                      ->orWhere('customer_phone', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
                
            foreach ($transactions as $transaction) {
                $results[] = [
                    'type' => 'transaction',
                    'id' => $transaction->id,
                    'title' => $transaction->transaction_number,
                    'subtitle' => $transaction->customer_name ?? 'Walk-in Customer',
                    'description' => 'Rp ' . number_format($transaction->total_amount, 0, ',', '.'),
                    'url' => route('transaction-history.show', $transaction),
                    'icon' => 'ni ni-money-coins',
                    'color' => 'bg-gradient-success'
                ];
            }
        }
        
        // Search in barcodes
        if ($type === 'all' || $type === 'barcodes') {
            $barcodes = Barcode::with(['item'])
                ->where(function($q) use ($query) {
                    $q->where('barcode_value', 'like', "%{$query}%")
                      ->orWhere('barcode_type', 'like', "%{$query}%");
                })
                ->limit(5)
                ->get();
                
            foreach ($barcodes as $barcode) {
                $results[] = [
                    'type' => 'barcode',
                    'id' => $barcode->id,
                    'title' => $barcode->barcode_value,
                    'subtitle' => $barcode->item->name ?? 'No Item',
                    'description' => $barcode->barcode_type,
                    'url' => route('barcodes.show', $barcode),
                    'icon' => 'fas fa-barcode',
                    'color' => 'bg-gradient-secondary'
                ];
            }
        }
        
        return response()->json($results);
    }
    
    /**
     * Quick search for items (used in kasir)
     */
    public function quickItemSearch(Request $request)
    {
        $query = $request->get('q', '');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $items = Item::with(['category'])
            ->where('is_active', true)
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('sku', 'like', "%{$query}%")
                  ->orWhere('barcode', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get();
            
        $results = [];
        foreach ($items as $item) {
            $results[] = [
                'id' => $item->id,
                'name' => $item->name,
                'sku' => $item->sku,
                'barcode' => $item->barcode,
                'price' => $item->selling_price,
                'stock' => $item->stock_quantity,
                'category' => $item->category->name ?? 'No Category',
                'is_low_stock' => $item->isLowStock()
            ];
        }
        
        return response()->json($results);
    }
}
