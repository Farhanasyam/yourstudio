@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Bulk Generate Barcodes'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Bulk Generate Barcodes</h6>
                            <a href="{{ route('barcodes.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to Barcodes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Statistics Card -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="card bg-gradient-info">
                                    <div class="card-body">
                                        <div class="row" id="statsContainer">
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="totalItems">{{ $totalItems ?? 0 }}</h4>
                                                <p class="text-white mb-0">Total Items</p>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="itemsWithBarcodes">{{ $itemsWithBarcodes ?? 0 }}</h4>
                                                <p class="text-white mb-0">With Barcodes</p>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="itemsWithoutBarcodes">{{ $itemsWithoutBarcodes ?? 0 }}</h4>
                                                <p class="text-white mb-0">Without Barcodes</p>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="completionPercentage">{{ $completionPercentage ?? 0 }}%</h4>
                                                <p class="text-white mb-0">Completion</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Generate Semua Tipe untuk SEMUA Produk (Bulk) -->
                        <div class="card bg-gradient-success mb-4">
                            <div class="card-body">
                                <h6 class="mb-2 text-white">Generate semua tipe barcode untuk semua barang</h6>
                                <p class="text-sm text-white opacity-8 mb-3">Satu klik: generate CODE128, CODE39, EAN13, dan QR untuk <strong>semua {{ $totalItems }} produk</strong>. Barcode lama per tipe otomatis dihapus.</p>
                                <form id="bulkGenerateAllTypesForm" action="{{ route('barcodes.bulk-generate-all-types') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-light" id="bulkAllTypesBtn">
                                        <i class="fas fa-barcode"></i> Generate Semua Tipe untuk Semua Barang
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Generate Semua Tipe untuk Satu Item -->
                        <div class="card bg-light mb-4">
                            <div class="card-body">
                                <h6 class="mb-3">Generate semua tipe untuk satu item</h6>
                                <p class="text-sm text-muted mb-3">Pilih satu item, lalu klik Generate. Semua tipe (CODE128, CODE39, EAN13, QR) akan dibuat; barcode lama per tipe otomatis dihapus.</p>
                                <form id="generateAllTypesOneItemForm" action="{{ route('barcodes.generate-all-types') }}" method="POST" class="d-inline">
                                    @csrf
                                    <div class="row align-items-end">
                                        <div class="col-md-8">
                                            <label class="form-label">Pilih item</label>
                                            <select class="form-select" name="item_id" required>
                                                <option value="">-- Pilih item --</option>
                                                @foreach($allItems as $it)
                                                    <option value="{{ $it->id }}">{{ $it->name }} ({{ $it->sku ?? '-' }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-info w-100">
                                                <i class="fas fa-barcode"></i> Generate Semua Tipe
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Generation Form (satu tipe, banyak item) -->
                        <form action="{{ route('barcodes.bulk-generate') }}" method="POST" id="bulkGenerateForm">
                            @csrf
                            
                            <!-- Barcode Type Selection -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="barcode_type" class="form-label">Barcode Type</label>
                                    <select class="form-select @error('barcode_type') is-invalid @enderror" 
                                            id="barcode_type" name="barcode_type" required>
                                        <option value="">Select Barcode Type</option>
                                        <option value="CODE128" {{ old('barcode_type') == 'CODE128' ? 'selected' : '' }}>
                                            CODE128 (Recommended)
                                        </option>
                                        <option value="CODE39" {{ old('barcode_type') == 'CODE39' ? 'selected' : '' }}>
                                            CODE39 (Alphanumeric)
                                        </option>
                                        <option value="EAN13" {{ old('barcode_type') == 'EAN13' ? 'selected' : '' }}>
                                            EAN13 (13 digits)
                                        </option>
                                        <option value="QR" {{ old('barcode_type') == 'QR' ? 'selected' : '' }}>
                                            QR Code
                                        </option>
                                    </select>
                                    @error('barcode_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <small>
                                            <strong>CODE128:</strong> Best for general use, supports numbers and letters<br>
                                            <strong>CODE39:</strong> Older standard, alphanumeric only<br>
                                            <strong>EAN13:</strong> Standard retail barcode (13 digits)<br>
                                            <strong>QR Code:</strong> 2D barcode, can store more information
                                        </small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="generation_mode" class="form-label">Generation Mode</label>
                                    <select class="form-select @error('generation_mode') is-invalid @enderror" 
                                            id="generation_mode" name="generation_mode" required>
                                        <option value="">Select Generation Mode</option>
                                        <option value="missing_only" {{ old('generation_mode') == 'missing_only' ? 'selected' : '' }}>
                                            Only Items Without Barcodes ({{ $itemsWithoutBarcodes }} items)
                                        </option>
                                        <option value="all_items" {{ old('generation_mode') == 'all_items' ? 'selected' : '' }}>
                                            All Items ({{ $allItems->count() }} items)
                                        </option>
                                        <option value="selected_items" {{ old('generation_mode') == 'selected_items' ? 'selected' : '' }}>
                                            Selected Items Only
                                        </option>
                                    </select>
                                    @error('generation_mode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <!-- Item Selection (shown when selected_items mode is chosen) -->
                            <div class="row mb-4" id="itemSelection" style="display: none;">
                                <div class="col-12">
                                    <label class="form-label">Select Items</label>
                                    <div class="card">
                                        <div class="card-header">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <button type="button" class="btn btn-sm btn-primary" id="selectAllItems">
                                                        Select All
                                                    </button>
                                                    <button type="button" class="btn btn-sm btn-secondary" id="deselectAllItems">
                                                        Deselect All
                                                    </button>
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control form-control-sm" 
                                                           id="searchItems" placeholder="Search items...">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                            <div class="row" id="itemsList">
                                                @foreach($allItems as $item)
                                                    <div class="col-md-6 mb-2 item-checkbox" 
                                                         data-name="{{ strtolower($item->name) }}" 
                                                         data-category="{{ strtolower($item->category->name ?? '') }}">
                                                        <div class="form-check">
                                                            <input class="form-check-input item-checkbox-input" 
                                                                   type="checkbox" name="item_ids[]" 
                                                                   value="{{ $item->id }}" 
                                                                   id="item_{{ $item->id }}">
                                                            <label class="form-check-label" for="item_{{ $item->id }}">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <strong>{{ $item->name }}</strong><br>
                                                                        <small class="text-muted">{{ $item->category->name ?? 'No Category' }}</small>
                                                                    </div>
                                                                    @if($item->barcodes->isNotEmpty())
                                                                        <span class="badge bg-success">Has Barcode</span>
                                                                    @else
                                                                        <span class="badge bg-warning">No Barcode</span>
                                                                    @endif
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary" id="generateBtn">
                                        <i class="fas fa-barcode"></i> Generate Barcodes
                                    </button>
                                    <a href="{{ route('barcodes.index') }}" class="btn btn-secondary ms-2">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
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
    loadStats();

    // Bulk generate SEMUA tipe untuk SEMUA produk - konfirmasi SweetAlert
    var bulkAllForm = document.getElementById('bulkGenerateAllTypesForm');
    if (bulkAllForm) {
        bulkAllForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            var btn = document.getElementById('bulkAllTypesBtn');
            Swal.fire({
                title: 'Generate Semua Tipe Barcode',
                html: 'Generate CODE128, CODE39, EAN13, dan QR untuk <strong>semua {{ $totalItems }} produk</strong>?<br><br><span class="text-danger">Barcode lama akan dihapus.</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#2dce89',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Generate',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) {
                    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...'; }
                    form.submit();
                }
            });
        });
    }

    // Generate semua tipe untuk satu item - konfirmasi SweetAlert
    var oneItemForm = document.getElementById('generateAllTypesOneItemForm');
    if (oneItemForm) {
        oneItemForm.addEventListener('submit', function(e) {
            e.preventDefault();
            var form = this;
            Swal.fire({
                title: 'Generate Semua Tipe',
                text: 'Generate CODE128, CODE39, EAN13, dan QR untuk item ini? Barcode lama per tipe akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#5e72e4',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Generate',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function(result) {
                if (result.isConfirmed) form.submit();
            });
        });
    }
    
    // Handle generation mode change
    document.getElementById('generation_mode').addEventListener('change', function() {
        const itemSelection = document.getElementById('itemSelection');
        if (this.value === 'selected_items') {
            itemSelection.style.display = 'block';
            // Make item selection required
            document.querySelectorAll('.item-checkbox-input').forEach(input => {
                input.setAttribute('required', 'required');
            });
        } else {
            itemSelection.style.display = 'none';
            // Remove required attribute
            document.querySelectorAll('.item-checkbox-input').forEach(input => {
                input.removeAttribute('required');
                input.checked = false;
            });
        }
    });

    // Select/Deselect all items
    document.getElementById('selectAllItems').addEventListener('click', function() {
        document.querySelectorAll('.item-checkbox-input:not([style*="display: none"])').forEach(input => {
            input.checked = true;
        });
    });

    document.getElementById('deselectAllItems').addEventListener('click', function() {
        document.querySelectorAll('.item-checkbox-input').forEach(input => {
            input.checked = false;
        });
    });

    // Search items
    document.getElementById('searchItems').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        document.querySelectorAll('.item-checkbox').forEach(item => {
            const name = item.getAttribute('data-name');
            const category = item.getAttribute('data-category');
            
            if (name.includes(searchTerm) || category.includes(searchTerm)) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });

    // Form submission with loading state
    document.getElementById('bulkGenerateForm').addEventListener('submit', function() {
        const generateBtn = document.getElementById('generateBtn');
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
        generateBtn.disabled = true;
    });
});

function loadStats() {
    fetch('{{ route("barcodes.generation-stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.error) return;
            document.getElementById('totalItems').textContent = data.total_items;
            document.getElementById('itemsWithBarcodes').textContent = data.items_with_barcodes;
            document.getElementById('itemsWithoutBarcodes').textContent = data.items_without_barcodes;
            document.getElementById('completionPercentage').textContent = data.completion_percentage + '%';
        })
        .catch(function() {});
}
</script>
@endpush
