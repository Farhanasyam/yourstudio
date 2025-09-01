@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Item Details'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Item Details</h6>
                            <div>
                                <a href="{{ route('items.edit', $item) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit Item
                                </a>
                                <a href="{{ route('items.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Items
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Item Name</label>
                                            <p class="form-control-static">{{ $item->name }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">SKU</label>
                                            <p class="form-control-static">{{ $item->sku }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Category</label>
                                            <p class="form-control-static">{{ $item->category->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Supplier</label>
                                            <p class="form-control-static">{{ $item->supplier->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Purchase Price</label>
                                            <p class="form-control-static">Rp {{ number_format($item->purchase_price, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Selling Price</label>
                                            <p class="form-control-static">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Stock Quantity</label>
                                            <p class="form-control-static">{{ $item->stock_quantity }} {{ $item->unit }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Minimum Stock</label>
                                            <p class="form-control-static">{{ $item->minimum_stock }} {{ $item->unit }}</p>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Unit</label>
                                            <p class="form-control-static">{{ $item->unit }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label class="form-control-label text-sm font-weight-bold">Description</label>
                                            <p class="form-control-static">{{ $item->description ?? 'No description available' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Stock Status</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($item->stock_quantity <= $item->minimum_stock)
                                            <div class="alert alert-danger mb-0">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Low Stock!</strong><br>
                                                Current: {{ $item->stock_quantity }} {{ $item->unit }}<br>
                                                Minimum: {{ $item->minimum_stock }} {{ $item->unit }}
                                            </div>
                                        @elseif($item->stock_quantity == 0)
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-circle"></i>
                                                <strong>Out of Stock!</strong><br>
                                                Current: {{ $item->stock_quantity }} {{ $item->unit }}
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-0">
                                                <i class="fas fa-check-circle"></i>
                                                <strong>In Stock</strong><br>
                                                Current: {{ $item->stock_quantity }} {{ $item->unit }}<br>
                                                Available: {{ $item->stock_quantity - $item->minimum_stock }} {{ $item->unit }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h6 class="mb-0">Item Information</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <small class="text-muted">Status</small>
                                                <p class="mb-0">
                                                    @if($item->is_active)
                                                        <span class="badge badge-sm bg-gradient-success">Active</span>
                                                    @else
                                                        <span class="badge badge-sm bg-gradient-secondary">Inactive</span>
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Created</small>
                                                <p class="mb-0">{{ $item->created_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-6">
                                                <small class="text-muted">Profit Margin</small>
                                                <p class="mb-0">
                                                    @php
                                                        $margin = $item->selling_price > 0 ? (($item->selling_price - $item->purchase_price) / $item->selling_price) * 100 : 0;
                                                    @endphp
                                                    {{ number_format($margin, 1) }}%
                                                </p>
                                            </div>
                                            <div class="col-6">
                                                <small class="text-muted">Last Updated</small>
                                                <p class="mb-0">{{ $item->updated_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
