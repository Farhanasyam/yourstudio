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
                                <a href="{{ route('barcodes.print', $barcode) }}" class="btn btn-success btn-sm" target="_blank">
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
                                            <p class="form-control-static">{{ $barcode->barcode_number }}</p>
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
                            <div class="border p-4 d-inline-block bg-white">
                                <div class="mb-2">
                                    <strong>{{ $barcode->item->name ?? 'N/A' }}</strong>
                                </div>
                                <canvas id="barcode"></canvas>
                                <div class="mt-2 small">
                                    {{ $barcode->barcode_number }}
                                </div>
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

@push('js')
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        JsBarcode("#barcode", "{{ $barcode->barcode_number }}", {
            format: "{{ strtoupper($barcode->barcode_type) }}",
            width: 2,
            height: 100,
            displayValue: false
        });
    });
</script>
@endpush
