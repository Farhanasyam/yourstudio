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
                                                <h4 class="text-white mb-0" id="totalItems">-</h4>
                                                <p class="text-white mb-0">Total Items</p>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="itemsWithBarcodes">-</h4>
                                                <p class="text-white mb-0">With Barcodes</p>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="itemsWithoutBarcodes">-</h4>
                                                <p class="text-white mb-0">Without Barcodes</p>
                                            </div>
                                            <div class="col-md-3 text-center">
                                                <h4 class="text-white mb-0" id="completionPercentage">-%</h4>
                                                <p class="text-white mb-0">Completion</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Generation Form -->
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
                                            Only Items Without Barcodes ({{ $itemsWithoutBarcodes->count() }} items)
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

                            <!-- Replace Existing Option -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               id="replace_existing" name="replace_existing" value="1"
                                               {{ old('replace_existing') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="replace_existing">
                                            Replace existing active barcodes
                                        </label>
                                        <div class="form-text">
                                            <small class="text-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                Warning: This will deactivate existing barcodes and create new ones
                                            </small>
                                        </div>
                                    </div>
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

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load statistics
    loadStats();
    
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
            document.getElementById('totalItems').textContent = data.total_items;
            document.getElementById('itemsWithBarcodes').textContent = data.items_with_barcodes;
            document.getElementById('itemsWithoutBarcodes').textContent = data.items_without_barcodes;
            document.getElementById('completionPercentage').textContent = data.completion_percentage + '%';
        })
        .catch(error => {
            console.error('Error loading stats:', error);
        });
}
</script>
@endsection
