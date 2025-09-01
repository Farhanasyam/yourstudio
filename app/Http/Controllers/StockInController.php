<?php

namespace App\Http\Controllers;

use App\Models\StockIn;
use App\Models\StockInDetail;
use App\Models\Supplier;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockInController extends Controller
{
    public function index(Request $request)
    {
        $query = StockIn::with(['supplier', 'user']);
        
        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('notes', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($supplierQuery) use ($search) {
                      $supplierQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('details.item', function($itemQuery) use ($search) {
                      $itemQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('sku', 'like', "%{$search}%");
                  });
            });
        }
        
        // Filter by supplier
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('transaction_date', '<=', $request->end_date);
        }
        
        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        $stockIns = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Get suppliers for filter
        $suppliers = Supplier::orderBy('name')->get();
        
        if ($request->ajax()) {
            return response()->json([
                'html' => view('pages.stock-in.partials.table', compact('stockIns'))->render(),
                'pagination' => $stockIns->links()->toHtml()
            ]);
        }
        
        return view('pages.stock-in.index', compact('stockIns', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('name')->get();
        return view('pages.stock-in.create', compact('suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            // Create Stock In
            $stockIn = StockIn::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => Auth::id(),
                'transaction_date' => $request->transaction_date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
                'status' => 'completed',
            ]);

            // Create Stock In Details and update item stock
            foreach ($request->items as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['purchase_price'];
                
                StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'purchase_price' => $itemData['purchase_price'],
                    'subtotal' => $subtotal,
                ]);

                // Update item stock
                $item = Item::find($itemData['item_id']);
                $item->stock_quantity += $itemData['quantity'];
                $item->purchase_price = $itemData['purchase_price']; // Update last purchase price
                $item->save();
            }

            DB::commit();
            return redirect()->route('stock-in.index')->with('success', 'Stock In created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to create Stock In: ' . $e->getMessage())->withInput();
        }
    }

    public function show(StockIn $stockIn)
    {
        $stockIn->load(['supplier', 'user', 'details.item']);
        return view('pages.stock-in.show', compact('stockIn'));
    }

    public function edit(StockIn $stockIn)
    {
        if ($stockIn->status === 'completed') {
            return redirect()->route('stock-in.index')->with('error', 'Cannot edit completed stock in transaction.');
        }

        $suppliers = Supplier::orderBy('name')->get();
        $items = Item::where('is_active', true)->orderBy('name')->get();
        $stockIn->load('details.item');
        return view('pages.stock-in.edit', compact('stockIn', 'suppliers', 'items'));
    }

    public function update(Request $request, StockIn $stockIn)
    {
        if ($stockIn->status === 'completed') {
            return redirect()->route('stock-in.index')->with('error', 'Cannot update completed stock in transaction.');
        }

        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'transaction_date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.purchase_price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Reverse previous stock changes
            foreach ($stockIn->details as $detail) {
                $item = Item::find($detail->item_id);
                $item->stock_quantity -= $detail->quantity;
                $item->save();
            }

            // Delete old details
            $stockIn->details()->delete();

            // Calculate new total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['purchase_price'];
            }

            // Update Stock In
            $stockIn->update([
                'supplier_id' => $request->supplier_id,
                'transaction_date' => $request->transaction_date,
                'total_amount' => $totalAmount,
                'notes' => $request->notes,
            ]);

            // Create new details and update stock
            foreach ($request->items as $itemData) {
                $subtotal = $itemData['quantity'] * $itemData['purchase_price'];
                
                StockInDetail::create([
                    'stock_in_id' => $stockIn->id,
                    'item_id' => $itemData['item_id'],
                    'quantity' => $itemData['quantity'],
                    'purchase_price' => $itemData['purchase_price'],
                    'subtotal' => $subtotal,
                ]);

                // Update item stock
                $item = Item::find($itemData['item_id']);
                $item->stock_quantity += $itemData['quantity'];
                $item->purchase_price = $itemData['purchase_price'];
                $item->save();
            }

            DB::commit();
            return redirect()->route('stock-in.index')->with('success', 'Stock In updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update Stock In: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(StockIn $stockIn)
    {
        if ($stockIn->status === 'completed') {
            return redirect()->route('stock-in.index')->with('error', 'Cannot delete completed stock in transaction.');
        }

        try {
            DB::beginTransaction();

            // Reverse stock changes
            foreach ($stockIn->details as $detail) {
                $item = Item::find($detail->item_id);
                $item->stock_quantity -= $detail->quantity;
                $item->save();
            }

            // Delete details first
            $stockIn->details()->delete();
            
            // Delete stock in
            $stockIn->delete();

            DB::commit();
            return redirect()->route('stock-in.index')->with('success', 'Stock In deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('stock-in.index')->with('error', 'Failed to delete Stock In: ' . $e->getMessage());
        }
    }
}
