@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Barcode Details'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Barcode Details</h6>
                            <div>
                                <a href="{{ route('barcodes.print-select', $barcode->item) }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-print"></i> Print
                                </a>
                                <a href="{{ route('barcodes.edit', $barcode) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('barcodes.index') }}" class="btn btn-secondary btn-sm ms-2">
                                    <i class="fas fa-arrow-left"></i> Back to Barcodes
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Barcode Information</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Barcode Number</label>
                                            <p class="form-control-static">{{ $barcode->barcode_value }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Type</label>
                                            <p class="form-control-static">{{ strtoupper($barcode->barcode_type) }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Status</label>
                                            <p class="form-control-static">
                                                @if($barcode->is_active)
                                                    <span class="badge badge-sm bg-gradient-success">Active</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Inactive</span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder">Item Information</h6>
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Item Name</label>
                                            <p class="form-control-static">{{ $barcode->item->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">SKU</label>
                                            <p class="form-control-static">{{ $barcode->item->sku ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-group">
                                            <label class="form-control-label">Category</label>
                                            <p class="form-control-static">{{ $barcode->item->category->name ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="horizontal dark">
                        <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Barcode Preview</h6>
                        <div class="text-center">
                            <div class="border p-4 d-inline-block bg-white rounded">
                                <div class="mb-2">
                                    <strong>{{ $barcode->item->name ?? 'N/A' }}</strong>
                                </div>
                                <div id="barcode-preview" class="d-inline-flex justify-content-center align-items-center" style="min-height: 100px;">
                                    {{-- Diisi oleh JS: canvas untuk CODE128/CODE39/EAN13, atau QR untuk tipe QR --}}
                                </div>
                                <div class="mt-2 small text-muted text-center">{{ $barcode->barcode_value }}</div>
                            </div>
                        </div>

                        <hr class="horizontal dark">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Created At</label>
                                    <p class="form-control-static">{{ $barcode->created_at->format('d M Y, H:i') }}</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-control-label">Last Updated</label>
                                    <p class="form-control-static">{{ $barcode->updated_at->format('d M Y, H:i') }}</p>
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

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
(function() {
    var type = "{{ strtoupper($barcode->barcode_type) }}";
    var value = @json($barcode->barcode_value);
    var container = document.getElementById('barcode-preview');

    if (type === 'QR') {
        try {
            new QRCode(container, {
                text: value,
                width: 180,
                height: 180
            });
        } catch (e) {
            container.innerHTML = '<p class="text-danger small">QR tidak dapat ditampilkan: ' + e.message + '</p>';
        }
    } else {
        var canvas = document.createElement('canvas');
        container.appendChild(canvas);
        try {
            JsBarcode(canvas, value, {
                format: type,
                width: 2,
                height: 100,
                displayValue: false
            });
        } catch (e) {
            container.innerHTML = '<p class="text-danger small">Barcode tidak dapat ditampilkan: ' + e.message + '</p>';
        }
    }
})();
</script>
@endpush
