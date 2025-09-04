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
                                    <h2 class="font-weight-bolder mb-0" id="totalItems">-</h2>
                                    <span class="text-sm text-muted">in inventory</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center py-4 border-end">
                                <div class="numbers">
                                    <p class="text-sm mb-2 text-uppercase font-weight-bold text-success">With Barcode</p>
                                    <h2 class="font-weight-bolder mb-0" id="itemsWithBarcodes">-</h2>
                                    <span class="text-sm text-success">Ready to Use</span>
                                </div>
                            </div>
                            <div class="col-md-4 text-center py-4">
                                <div class="numbers">
                                    <p class="text-sm mb-2 text-uppercase font-weight-bold text-warning">Pending</p>
                                    <h2 class="font-weight-bolder mb-0" id="itemsWithoutBarcodes">-</h2>
                                    <span class="text-sm text-warning">Need Barcode</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <style>
        .border-end {
            border-right: 1px solid #dee2e6 !important;
        }
        .numbers h2 {
            font-size: 2.5rem;
            line-height: 1.2;
        }
        #lastUpdate {
            font-size: 0.75rem;
            padding: 0.5rem 0.75rem;
        }
        </style>
        </style>

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
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@section('scripts')
<script>
// Delete confirmation handler
function deleteConfirmation(formId) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah anda yakin ingin menghapus data ini?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize statistics
    loadStats();
});

function animateNumber(elementId, finalNumber) {
    console.log(`Animating ${elementId} to ${finalNumber}`);
    const element = document.getElementById(elementId);
    
    if (!element) {
        console.error(`Element with id ${elementId} not found`);
        return;
    }

    // Pastikan finalNumber adalah angka
    finalNumber = parseInt(finalNumber) || 0;
    
    // Jika angka kecil, tidak perlu animasi
    if (finalNumber <= 5) {
        element.textContent = finalNumber.toString();
        return;
    }

    const duration = 1000;
    const stepTime = 50;
    const steps = duration / stepTime;
    const increment = finalNumber / steps;
    let currentNumber = 0;
    let currentStep = 0;

    const animation = setInterval(() => {
        currentStep++;
        currentNumber += increment;
        
        if (currentStep >= steps) {
            clearInterval(animation);
            element.textContent = finalNumber.toString();
        } else {
            element.textContent = Math.round(currentNumber).toString();
        }
    }, stepTime);
}

document.addEventListener('DOMContentLoaded', function() {
    // Load statistics
    loadStats();

    // Add refresh button handler
    document.getElementById('refreshStats').addEventListener('click', function() {
        this.disabled = true;
        const icon = this.querySelector('i');
        icon.classList.add('fa-spin');
        
        loadStats().finally(() => {
            setTimeout(() => {
                this.disabled = false;
                icon.classList.remove('fa-spin');
            }, 1000);
        });
    });
});

function updateLastUpdateTime() {
    const now = new Date();
    const hours = now.getHours().toString().padStart(2, '0');
    const minutes = now.getMinutes().toString().padStart(2, '0');
    document.getElementById('updateTime').textContent = `${hours}:${minutes}`;
}

async function loadStats() {
    console.log('Loading barcode statistics...');
    
    // Tampilkan loading state
    document.getElementById('totalItems').textContent = 'Loading...';
    document.getElementById('itemsWithBarcodes').textContent = 'Loading...';
    document.getElementById('itemsWithoutBarcodes').textContent = 'Loading...';
    
    // Update waktu
    updateLastUpdateTime();
    
    return fetch('{{ route("barcodes.generation-stats") }}', {
        method: 'GET',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
            'Cache-Control': 'no-cache'
        },
        credentials: 'same-origin'
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            
            if (!data.total_items && data.total_items !== 0) {
                throw new Error('Invalid data received from server');
            }
            // Animate the numbers
            animateNumber('totalItems', data.total_items);
            animateNumber('itemsWithBarcodes', data.items_with_barcodes);
            animateNumber('itemsWithoutBarcodes', data.items_without_barcodes);
            
            // Update completion percentage and progress bar
            const completionPercentage = data.completion_percentage;
            const formattedPercentage = Number(completionPercentage).toFixed(0); // Bulatkan ke angka bulat
            document.getElementById('completionPercentage').textContent = formattedPercentage + '%';
            
            // Animate progress bar with color based on percentage
            const progressBar = document.getElementById('completionProgress');
            progressBar.style.width = '0%';
            
            // Set warna berdasarkan persentase
            let barColor = 'bg-gradient-danger'; // 0-25%
            if (completionPercentage > 75) {
                barColor = 'bg-gradient-success';
            } else if (completionPercentage > 50) {
                barColor = 'bg-gradient-info';
            } else if (completionPercentage > 25) {
                barColor = 'bg-gradient-warning';
            }
            
            // Update class warna
            progressBar.className = `progress-bar ${barColor}`;
            
            // Animate width
            setTimeout(() => {
                progressBar.style.transition = 'width 1s ease-in-out';
                progressBar.style.width = formattedPercentage + '%';
            }, 200);
        })
        .catch(error => {
            console.error('Error loading stats:', error);
            console.error('Error details:', error.stack);
            // Show error alert
            const errorAlert = `
                <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                    <i class="fas fa-exclamation-circle flex-shrink-0 me-2"></i>
                    <div>
                        <strong>Error!</strong> Failed to load barcode statistics. Please refresh the page or contact support if the problem persists.
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            document.getElementById('alert').innerHTML = errorAlert;
            // Set fallback values
            document.getElementById('totalItems').textContent = '0';
            document.getElementById('itemsWithBarcodes').textContent = '0';
            document.getElementById('itemsWithoutBarcodes').textContent = '0';
            document.getElementById('completionPercentage').textContent = '0%';
            document.getElementById('completionProgress').style.width = '0%';
        });
}
</script>
@endsection

