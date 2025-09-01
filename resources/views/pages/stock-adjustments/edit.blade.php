@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Stock Adjustment'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Edit Stock Adjustment - {{ $stockAdjustment->transaction_code }}</h6>
                            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('stock-adjustments.update', $stockAdjustment) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="item_id" class="form-control-label">Item <span class="text-danger">*</span></label>
                                        <select class="form-control @error('item_id') is-invalid @enderror" 
                                                id="item_id" name="item_id" required>
                                            <option value="">Select Item</option>
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" 
                                                        data-stock="{{ $item->stock_quantity }}"
                                                        {{ (old('item_id', $stockAdjustment->item_id) == $item->id) ? 'selected' : '' }}>
                                                    {{ $item->name }} (Current Stock: {{ $item->stock_quantity }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('item_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Current Stock: <span id="current-stock">{{ $stockAdjustment->item->stock_quantity ?? 0 }}</span>
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="adjustment_date" class="form-control-label">Adjustment Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('adjustment_date') is-invalid @enderror" 
                                               id="adjustment_date" name="adjustment_date" 
                                               value="{{ old('adjustment_date', $stockAdjustment->adjustment_date->format('Y-m-d')) }}" required>
                                        @error('adjustment_date')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="type" class="form-control-label">Adjustment Type <span class="text-danger">*</span></label>
                                        <select class="form-control @error('type') is-invalid @enderror" 
                                                id="type" name="type" required>
                                            <option value="">Select Type</option>
                                            <option value="increase" {{ (old('type', $stockAdjustment->type) == 'increase') ? 'selected' : '' }}>
                                                Increase (+)
                                            </option>
                                            <option value="decrease" {{ (old('type', $stockAdjustment->type) == 'decrease') ? 'selected' : '' }}>
                                                Decrease (-)
                                            </option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="quantity" class="form-control-label">Quantity <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control @error('quantity') is-invalid @enderror" 
                                               id="quantity" name="quantity" min="1" 
                                               value="{{ old('quantity', $stockAdjustment->quantity) }}" required>
                                        @error('quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="form-text text-muted">
                                            Stock After Adjustment: <span id="stock-after">{{ $stockAdjustment->stock_after }}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="reason" class="form-control-label">Reason <span class="text-danger">*</span></label>
                                        <select class="form-control @error('reason') is-invalid @enderror" 
                                                id="reason" name="reason" required>
                                            <option value="">Select Reason</option>
                                            @php
                                                $reasons = [
                                                    'Stock Take Adjustment',
                                                    'Damaged Items',
                                                    'Expired Items',
                                                    'Lost Items',
                                                    'Found Items',
                                                    'System Error Correction',
                                                    'Other'
                                                ];
                                            @endphp
                                            @foreach($reasons as $reasonOption)
                                                <option value="{{ $reasonOption }}" 
                                                        {{ (old('reason', $stockAdjustment->reason) == $reasonOption) ? 'selected' : '' }}>
                                                    {{ $reasonOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('reason')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <label for="notes" class="form-control-label">Notes</label>
                                        <textarea class="form-control @error('notes') is-invalid @enderror" 
                                                  id="notes" name="notes" rows="3" 
                                                  placeholder="Optional additional notes about this adjustment">{{ old('notes', $stockAdjustment->notes) }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Cancel</button>
                                        <button type="submit" class="btn btn-primary">Update Stock Adjustment</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const itemSelect = document.getElementById('item_id');
            const typeSelect = document.getElementById('type');
            const quantityInput = document.getElementById('quantity');
            const currentStockSpan = document.getElementById('current-stock');
            const stockAfterSpan = document.getElementById('stock-after');

            function updateCurrentStock() {
                const selectedOption = itemSelect.options[itemSelect.selectedIndex];
                const stock = selectedOption.getAttribute('data-stock') || 0;
                currentStockSpan.textContent = stock;
                calculateStockAfter();
            }

            function calculateStockAfter() {
                const selectedOption = itemSelect.options[itemSelect.selectedIndex];
                const currentStock = parseInt(selectedOption.getAttribute('data-stock') || 0);
                const type = typeSelect.value;
                const quantity = parseInt(quantityInput.value || 0);

                if (type && quantity) {
                    let stockAfter;
                    if (type === 'increase') {
                        stockAfter = currentStock + quantity;
                        stockAfterSpan.className = 'text-success font-weight-bold';
                    } else if (type === 'decrease') {
                        stockAfter = currentStock - quantity;
                        stockAfterSpan.className = stockAfter < 0 ? 'text-danger font-weight-bold' : 'text-warning font-weight-bold';
                    }
                    stockAfterSpan.textContent = stockAfter;
                } else {
                    stockAfterSpan.textContent = '-';
                    stockAfterSpan.className = '';
                }
            }

            itemSelect.addEventListener('change', updateCurrentStock);
            typeSelect.addEventListener('change', calculateStockAfter);
            quantityInput.addEventListener('input', calculateStockAfter);

            // Initialize
            calculateStockAfter();
        });
    </script>
@endsection