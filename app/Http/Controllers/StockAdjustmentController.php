<?php

namespace App\Http\Controllers;

use App\Models\StockAdjustment;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StockAdjustmentController extends Controller
{
    public function index()
    {
        $stockAdjustments = StockAdjustment::with(['item', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        return view('pages.stock-adjustments.index', compact('stockAdjustments'));
    }

    public function create()
    {
        $items = Item::where('is_active', true)->orderBy('name')->get();
        return view('pages.stock-adjustments.create', compact('items'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'adjustment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            $item = Item::find($request->item_id);
            $stockBefore = $item->stock_quantity;

            // Calculate new stock
            if ($request->type === 'increase') {
                $stockAfter = $stockBefore + $request->quantity;
            } else {
                $stockAfter = $stockBefore - $request->quantity;
                
                // Check if decrease would result in negative stock
                if ($stockAfter < 0) {
                    return redirect()->back()->with('error', 'Insufficient stock. Current stock: ' . $stockBefore)->withInput();
                }
            }

            // Create stock adjustment record
            $stockAdjustment = StockAdjustment::create([
                'item_id' => $request->item_id,
                'user_id' => Auth::id(),
                'type' => $request->type,
                'quantity' => $request->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'adjustment_date' => $request->adjustment_date,
            ]);

            // Update item stock
            $item->stock_quantity = $stockAfter;
            $item->save();

            DB::commit();
            return redirect()->route('stock-adjustments.index')->with('success', 'Stock Adjustment created successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to create Stock Adjustment: ' . $e->getMessage())->withInput();
        }
    }

    public function show(StockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['item', 'user']);
        return view('pages.stock-adjustments.show', compact('stockAdjustment'));
    }

    public function edit(StockAdjustment $stockAdjustment)
    {
        $items = Item::where('is_active', true)->orderBy('name')->get();
        return view('pages.stock-adjustments.edit', compact('stockAdjustment', 'items'));
    }

    public function update(Request $request, StockAdjustment $stockAdjustment)
    {
        $validator = Validator::make($request->all(), [
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:increase,decrease',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'adjustment_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        try {
            DB::beginTransaction();

            // Reverse the previous adjustment
            $item = Item::find($stockAdjustment->item_id);
            if ($stockAdjustment->type === 'increase') {
                $item->stock_quantity -= $stockAdjustment->quantity;
            } else {
                $item->stock_quantity += $stockAdjustment->quantity;
            }

            // Apply new adjustment
            $newItem = Item::find($request->item_id);
            $stockBefore = $newItem->stock_quantity;

            if ($request->type === 'increase') {
                $stockAfter = $stockBefore + $request->quantity;
            } else {
                $stockAfter = $stockBefore - $request->quantity;
                
                if ($stockAfter < 0) {
                    return redirect()->back()->with('error', 'Insufficient stock. Current stock: ' . $stockBefore)->withInput();
                }
            }

            // Update stock adjustment record
            $stockAdjustment->update([
                'item_id' => $request->item_id,
                'type' => $request->type,
                'quantity' => $request->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'adjustment_date' => $request->adjustment_date,
            ]);

            // Update both items
            $item->save();
            $newItem->stock_quantity = $stockAfter;
            $newItem->save();

            DB::commit();
            return redirect()->route('stock-adjustments.index')->with('success', 'Stock Adjustment updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Failed to update Stock Adjustment: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(StockAdjustment $stockAdjustment)
    {
        try {
            DB::beginTransaction();

            // Reverse the adjustment
            $item = Item::find($stockAdjustment->item_id);
            if ($stockAdjustment->type === 'increase') {
                $item->stock_quantity -= $stockAdjustment->quantity;
            } else {
                $item->stock_quantity += $stockAdjustment->quantity;
            }
            $item->save();

            // Delete the adjustment record
            $stockAdjustment->delete();

            DB::commit();
            return redirect()->route('stock-adjustments.index')->with('success', 'Stock Adjustment deleted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('stock-adjustments.index')->with('error', 'Failed to delete Stock Adjustment: ' . $e->getMessage());
        }
    }
}
