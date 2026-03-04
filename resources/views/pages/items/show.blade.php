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
                                @if($item->allBarcodes->isNotEmpty())
                                    <a href="{{ route('barcodes.print-select', $item) }}" class="btn btn-success btn-sm">
                                        <i class="fas fa-print"></i> Cetak Barcode
                                    </a>
                                @endif
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

                        {{-- Barcode dari semua tipe --}}
                        @if($item->allBarcodes->isNotEmpty())
                            <hr class="horizontal dark">
                            <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Barcode (Semua Tipe)</h6>
                            <div class="row">
                                @foreach($item->allBarcodes as $barcode)
                                    <div class="col-md-4 col-lg-3 mb-4">
                                        <div class="card border">
                                            <div class="card-body p-3 text-center">
                                                <span class="badge badge-sm bg-gradient-info mb-2">{{ $barcode->barcode_type }}</span>
                                                <div class="mb-2 item-barcode-wrap d-flex justify-content-center align-items-center" data-value="{{ $barcode->barcode_value }}" data-format="{{ strtoupper($barcode->barcode_type) }}" style="min-height: 60px;"></div>
                                                <small class="text-muted d-block text-center">{{ $barcode->barcode_value }}</small>
                                                <a href="{{ route('barcodes.show', $barcode) }}" class="btn btn-link text-primary btn-sm mt-1 p-0">Detail</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <hr class="horizontal dark">
                            <p class="text-muted text-sm mb-0">Belum ada barcode untuk item ini. Tambah dari menu Barcode.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.item-barcode-wrap').forEach(function(wrap) {
            var format = (wrap.getAttribute('data-format') || 'CODE128').toUpperCase();
            var value = wrap.getAttribute('data-value') || '';
            if (format === 'QR') {
                try { new QRCode(wrap, { text: value, width: 100, height: 100 }); } catch (e) {}
            } else {
                var canvas = document.createElement('canvas');
                wrap.appendChild(canvas);
                try {
                    JsBarcode(canvas, value, { format: format, width: 1.5, height: 50, displayValue: false });
                } catch (e) {}
            }
        });
    });
</script>
@endpush
