@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Barcodes'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <!-- Barcode Summary -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-barcode text-primary me-2"></i>
                                <h6 class="mb-0">BARCODE SUMMARY</h6>
                            </div>
                            <div class="d-flex align-items-center">
                                <span class="badge bg-gradient-primary" id="lastUpdate">
                                    <i class="fas fa-clock me-1"></i>
                                    Last Update: <span id="updateTime">Just Now</span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-3 py-0">
                        <div class="row align-items-center">
                            <div class="col-md-4 text-center py-4 border-end">
                                <div class="numbers">
                                    <p class="text-sm mb-2 text-uppercase font-weight-bold text-primary">Total Items</p>
                                    <h2 class="font-weight-bolder mb-0" id="totalItems">{{ $totalItems ?? 0 }}</h2>
                                    <span class="text-sm text-muted">in inventory</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center py-4 border-end">
                                <div class="numbers">
                                    <p class="text-sm mb-2 text-uppercase font-weight-bold text-success">With Barcode</p>
                                    <h2 class="font-weight-bolder mb-0" id="itemsWithBarcodes">{{ $itemsWithBarcodes ?? 0 }}</h2>
                                    <span class="text-sm text-success">Ready to Use ({{ $completionPercentage ?? 0 }}%)</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center py-4">
                                <div class="numbers">
                                    <p class="text-sm mb-2 text-uppercase font-weight-bold text-warning">Pending</p>
                                    <h2 class="font-weight-bolder mb-0" id="itemsWithoutBarcodes">{{ $itemsWithoutBarcodes ?? 0 }}</h2>
                                    <span class="text-sm text-warning">Need Barcode</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .border-end { border-right: 1px solid #dee2e6 !important; }
        .numbers h2 { font-size: 2.5rem; line-height: 1.2; }
        #lastUpdate { font-size: 0.75rem; padding: 0.5rem 0.75rem; }
        </style>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <h6 class="mb-0">Barcodes</h6>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                {{-- Filter jenis barcode --}}
                                <form method="GET" action="{{ route('barcodes.index') }}" class="d-inline-flex align-items-center" id="filterForm">
                                    @if(request()->filled('search'))
                                        <input type="hidden" name="search" value="{{ request('search') }}">
                                    @endif
                                    @if(request()->filled('item_id'))
                                        <input type="hidden" name="item_id" value="{{ request('item_id') }}">
                                    @endif
                                    @if(request()->filled('is_active'))
                                        <input type="hidden" name="is_active" value="{{ request('is_active') }}">
                                    @endif
                                    @if(request()->filled('is_printed'))
                                        <input type="hidden" name="is_printed" value="{{ request('is_printed') }}">
                                    @endif
                                    <label class="me-2 mb-0 text-sm">Filter:</label>
                                    <select name="barcode_type" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                        <option value="">Semua Tipe</option>
                                        <option value="CODE128" {{ request('barcode_type') == 'CODE128' ? 'selected' : '' }}>CODE128</option>
                                        <option value="CODE39" {{ request('barcode_type') == 'CODE39' ? 'selected' : '' }}>CODE39</option>
                                        <option value="EAN13" {{ request('barcode_type') == 'EAN13' ? 'selected' : '' }}>EAN13</option>
                                        <option value="QR" {{ request('barcode_type') == 'QR' ? 'selected' : '' }}>QR</option>
                                    </select>
                                </form>
                                <a href="{{ route('barcodes.bulk-generate-form') }}" class="btn btn-success btn-sm">
                                    <i class="fas fa-magic"></i> Bulk Generate
                                </a>
                                <a href="{{ route('barcodes.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Add Barcode
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        {{-- Search (optional - bisa ditambah di sini jika ada) --}}
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Item</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Barcode Number</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Type</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Created</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($barcodes as $barcode)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="icon icon-shape icon-sm me-3 bg-gradient-info shadow text-center">
                                                        <i class="ni ni-bag-17 text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $barcode->item->name ?? 'N/A' }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $barcode->item->sku ?? 'N/A' }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $barcode->barcode_value }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-secondary">{{ $barcode->barcode_type }}</span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if($barcode->is_active)
                                                    <span class="badge badge-sm bg-gradient-success">Active</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-secondary">Inactive</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">{{ $barcode->created_at->format('d/m/Y') }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('barcodes.edit', $barcode) }}" class="btn btn-link text-secondary font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Edit barcode">
                                                        <i class="fas fa-pencil-alt text-xs me-1"></i>Edit
                                                    </a>

                                                    <a href="{{ route('barcodes.print', $barcode) }}" class="btn btn-link text-warning font-weight-bold text-xs" target="_blank" data-toggle="tooltip" data-original-title="Print barcode">
                                                        <i class="fas fa-print text-xs me-1"></i>Print
                                                    </a>
                                                    <form id="delete-form-{{ $barcode->id }}" action="{{ route('barcodes.destroy', $barcode) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" 
                                                                class="btn btn-link text-danger font-weight-bold text-xs" 
                                                                onclick="deleteConfirmation('delete-form-{{ $barcode->id }}')"
                                                                data-toggle="tooltip" 
                                                                data-original-title="Delete barcode">
                                                            <i class="fas fa-trash text-xs me-1"></i>Delete
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="ni ni-bag-17 text-secondary opacity-10" style="font-size: 3rem;"></i>
                                                    <p class="text-secondary mt-2">No barcodes found</p>
                                                    <a href="{{ route('barcodes.create') }}" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-plus"></i> Create First Barcode
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        {{-- Pagination: panah dan nomor halaman --}}
                        @if($barcodes->hasPages())
                            <div class="card-footer py-2 px-3 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2">
                                <div class="text-sm text-muted">
                                    Showing {{ $barcodes->firstItem() ?? 0 }} to {{ $barcodes->lastItem() ?? 0 }} of {{ $barcodes->total() }} results
                                </div>
                                <div class="mt-2 mt-md-0">
                                    {{ $barcodes->appends(request()->query())->links() }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var el = document.getElementById('updateTime');
    if (el) {
        var now = new Date();
        el.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    }
});
</script>
@endpush

