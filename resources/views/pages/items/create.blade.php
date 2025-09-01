@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Create Item'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Create Item</h6>
                            <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Items
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form role="form" method="POST" action="{{ route('items.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Item Name</label>
                                        <input class="form-control @error('name') is-invalid @enderror" 
                                               type="text" 
                                               id="name" 
                                               name="name" 
                                               value="{{ old('name') }}" 
                                               placeholder="Enter item name"
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="sku" class="form-control-label">SKU</label>
                                        <input class="form-control @error('sku') is-invalid @enderror" 
                                               type="text" 
                                               id="sku" 
                                               name="sku" 
                                               value="{{ old('sku') }}" 
                                               placeholder="Enter SKU"
                                               required>
                                        @error('sku')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="category_id" class="form-control-label">Category</label>
                                        <select class="form-control @error('category_id') is-invalid @enderror" 
                                                id="category_id" 
                                                name="category_id" 
                                                required>
                                            <option value="">Select a category</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="supplier_id" class="form-control-label">Supplier</label>
                                        <select class="form-control @error('supplier_id') is-invalid @enderror" 
                                                id="supplier_id" 
                                                name="supplier_id">
                                            <option value="">Select a supplier (optional)</option>
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
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="purchase_price" class="form-control-label">Purchase Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input class="form-control @error('purchase_price') is-invalid @enderror" 
                                                   type="number" 
                                                   id="purchase_price" 
                                                   name="purchase_price" 
                                                   value="{{ old('purchase_price') }}" 
                                                   placeholder="0"
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                        </div>
                                        @error('purchase_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="selling_price" class="form-control-label">Selling Price</label>
                                        <div class="input-group">
                                            <span class="input-group-text">Rp</span>
                                            <input class="form-control @error('selling_price') is-invalid @enderror" 
                                                   type="number" 
                                                   id="selling_price" 
                                                   name="selling_price" 
                                                   value="{{ old('selling_price') }}" 
                                                   placeholder="0"
                                                   min="0"
                                                   step="0.01"
                                                   required>
                                        </div>
                                        @error('selling_price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="stock_quantity" class="form-control-label">Stock Quantity</label>
                                        <input class="form-control @error('stock_quantity') is-invalid @enderror" 
                                               type="number" 
                                               id="stock_quantity" 
                                               name="stock_quantity" 
                                               value="{{ old('stock_quantity', 0) }}" 
                                               placeholder="0"
                                               min="0"
                                               required>
                                        @error('stock_quantity')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="minimum_stock" class="form-control-label">Minimum Stock</label>
                                        <input class="form-control @error('minimum_stock') is-invalid @enderror" 
                                               type="number" 
                                               id="minimum_stock" 
                                               name="minimum_stock" 
                                               value="{{ old('minimum_stock', 0) }}" 
                                               placeholder="0"
                                               min="0"
                                               required>
                                        @error('minimum_stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="unit" class="form-control-label">Unit</label>
                                        <input class="form-control @error('unit') is-invalid @enderror" 
                                               type="text" 
                                               id="unit" 
                                               name="unit" 
                                               value="{{ old('unit', 'pcs') }}" 
                                               placeholder="pcs, kg, etc."
                                               required>
                                        @error('unit')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="description" class="form-control-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" 
                                                  name="description" 
                                                  rows="3" 
                                                  placeholder="Enter item description">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Create Item
                                </button>
                                <a href="{{ route('items.index') }}" class="btn btn-secondary ms-2">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
