@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Create Stock In'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Create New Stock In</h6>
                            <a href="{{ route('stock-in.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('stock-in.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_id" class="form-control-label">Supplier <span class="text-danger">*</span></label>
                                        <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                                id="supplier_id" name="supplier_id" required>
                                            <option value="">Select Supplier</option>
                                            @foreach($suppliers as $supplier)
                                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                                    {{ $supplier->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('supplier_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="transaction_date" class="form-control-label">Transaction Date <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control @error('transaction_date') is-invalid @enderror" 
                                               id="transaction_date" name="transaction_date" 
                                               value="{{ old('transaction_date', date('Y-m-d')) }}" required>
                                        @error('transaction_date')
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
                                                  placeholder="Optional notes about this transaction">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <h6 class="mb-3">Items</h6>
                                    <div id="items-container">
                                        <div class="item-row mb-3 p-3 border rounded">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label class="form-control-label">Item <span class="text-danger">*</span></label>
                                                    <select class="form-control item-select" name="items[0][item_id]" required>
                                                        <option value="">Select Item</option>
                                                        @foreach($items as $item)
                                                            <option value="{{ $item->id }}" data-stock="{{ $item->stock_quantity }}">
                                                                {{ $item->name }} (Stock: {{ $item->stock_quantity }})
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Quantity <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control quantity-input" 
                                                           name="items[0][quantity]" min="1" required>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-control-label">Purchase Price <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control price-input" 
                                                           name="items[0][purchase_price]" step="0.01" min="0" required>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-control-label">Subtotal</label>
                                                    <input type="text" class="form-control subtotal-display" readonly>
                                                </div>
                                                <div class="col-md-1">
                                                    <label class="form-control-label">&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-sm remove-item w-100" style="display: none;">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" id="add-item" class="btn btn-success btn-sm">
                                        <i class="fas fa-plus"></i> Add Item
                                    </button>
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>Total Amount: <span id="total-amount">Rp 0</span></h5>
                                        <div>
                                            <button type="button" class="btn btn-secondary me-2" onclick="window.history.back()">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Create Stock In</button>
                                        </div>
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
        let itemIndex = 1;
        const itemsData = @json($items);

        document.getElementById('add-item').addEventListener('click', function() {
            const container = document.getElementById('items-container');
            const newRow = document.createElement('div');
            newRow.className = 'item-row mb-3 p-3 border rounded';
            newRow.innerHTML = `
                <div class="row">
                    <div class="col-md-4">
                        <label class="form-control-label">Item <span class="text-danger">*</span></label>
                        <select class="form-control item-select" name="items[${itemIndex}][item_id]" required>
                            <option value="">Select Item</option>
                            ${itemsData.map(item => `<option value="${item.id}" data-stock="${item.stock_quantity}">${item.name} (Stock: ${item.stock_quantity})</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-control-label">Quantity <span class="text-danger">*</span></label>
                        <input type="number" class="form-control quantity-input" name="items[${itemIndex}][quantity]" min="1" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-control-label">Purchase Price <span class="text-danger">*</span></label>
                        <input type="number" class="form-control price-input" name="items[${itemIndex}][purchase_price]" step="0.01" min="0" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-control-label">Subtotal</label>
                        <input type="text" class="form-control subtotal-display" readonly>
                    </div>
                    <div class="col-md-1">
                        <label class="form-control-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm remove-item w-100">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            `;
            container.appendChild(newRow);
            itemIndex++;
            updateRemoveButtons();
            bindEventHandlers();
        });

        function updateRemoveButtons() {
            const rows = document.querySelectorAll('.item-row');
            rows.forEach((row, index) => {
                const removeBtn = row.querySelector('.remove-item');
                if (rows.length > 1) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }

        function bindEventHandlers() {
            // Remove item handlers
            document.querySelectorAll('.remove-item').forEach(btn => {
                btn.addEventListener('click', function() {
                    this.closest('.item-row').remove();
                    updateRemoveButtons();
                    calculateTotal();
                });
            });

            // Calculate subtotal handlers
            document.querySelectorAll('.quantity-input, .price-input').forEach(input => {
                input.addEventListener('input', function() {
                    const row = this.closest('.item-row');
                    const quantity = row.querySelector('.quantity-input').value || 0;
                    const price = row.querySelector('.price-input').value || 0;
                    const subtotal = quantity * price;
                    row.querySelector('.subtotal-display').value = 'Rp ' + subtotal.toLocaleString('id-ID');
                    calculateTotal();
                });
            });
        }

        function calculateTotal() {
            let total = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const quantity = row.querySelector('.quantity-input').value || 0;
                const price = row.querySelector('.price-input').value || 0;
                total += quantity * price;
            });
            document.getElementById('total-amount').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Initialize event handlers
        bindEventHandlers();
    </script>
@endsection
