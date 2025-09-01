@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Barcodes'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <!-- Statistics Row -->
        <div class="row mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Items</p>
                                    <h5 class="font-weight-bolder mb-0" id="totalItems">-</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                    <i class="ni ni-box-2 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">With Barcodes</p>
                                    <h5 class="font-weight-bolder mb-0" id="itemsWithBarcodes">-</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="ni ni-tag text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Without Barcodes</p>
                                    <h5 class="font-weight-bolder mb-0" id="itemsWithoutBarcodes">-</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                    <i class="ni ni-fat-remove text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Completion</p>
                                    <h5 class="font-weight-bolder mb-0" id="completionPercentage">-%</h5>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                    <i class="ni ni-chart-pie-35 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Barcodes</h6>
                            <div class="btn-group" role="group">
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
                                                    <a href="{{ route('barcodes.generate', $barcode) }}" class="btn btn-link text-info font-weight-bold text-xs" data-toggle="tooltip" data-original-title="Generate barcode">
                                                        <i class="fas fa-sync-alt text-xs me-1"></i>Generate
                                                    </a>
                                                    <a href="{{ route('barcodes.print', $barcode) }}" class="btn btn-link text-warning font-weight-bold text-xs" target="_blank" data-toggle="tooltip" data-original-title="Print barcode">
                                                        <i class="fas fa-print text-xs me-1"></i>Print
                                                    </a>
                                                    <form action="{{ route('barcodes.destroy', $barcode) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-link text-danger font-weight-bold text-xs" 
                                                                onclick="return confirm('Are you sure you want to delete this barcode?')" 
                                                                data-toggle="tooltip" data-original-title="Delete barcode">
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
            // Set fallback values
            document.getElementById('totalItems').textContent = '0';
            document.getElementById('itemsWithBarcodes').textContent = '0';
            document.getElementById('itemsWithoutBarcodes').textContent = '0';
            document.getElementById('completionPercentage').textContent = '0%';
        });
}
</script>
@endsection
