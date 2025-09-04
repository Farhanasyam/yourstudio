@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Buat Laporan Baru'])

<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6>Buat Laporan Baru</h6>
                        </div>
                        <div class="col-6 text-end">
                            <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali ke Laporan
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('reports.store') }}" method="POST" id="reportForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="name" class="form-control-label">Nama Laporan <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', 'Laporan ' . now()->setTimezone('Asia/Jakarta')->format('d M Y H:i')) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="type" class="form-control-label">Jenis Laporan <span class="text-danger">*</span></label>
                                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                                        @foreach($reportTypes as $typeKey => $typeName)
                                            <option value="{{ $typeKey }}" {{ $type == $typeKey ? 'selected' : '' }}>
                                                {{ $typeName }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row" id="dateRangeRow">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date" class="form-control-label">Tanggal Mulai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="start_date" name="start_date" value="{{ old('start_date', date('Y-m-01')) }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date" class="form-control-label">Tanggal Selesai <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="end_date" name="end_date" value="{{ old('end_date', date('Y-m-d')) }}" required>
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>


                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="alert alert-info">
                                    <h6 class="alert-heading">
                                        <i class="fas fa-info-circle"></i> Informasi Laporan
                                    </h6>
                                    <div id="report-info">
                                        <p class="mb-0">Pilih jenis laporan untuk melihat informasi detail tentang data yang akan disertakan.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12 text-end">
                                <button type="button" class="btn btn-secondary me-2" onclick="resetForm()">
                                    <i class="fas fa-undo"></i> Reset
                                </button>
                                <button type="submit" class="btn btn-primary" id="generateBtn">
                                    <i class="fas fa-chart-bar"></i> Buat Laporan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const reportInfo = document.getElementById('report-info');
    const dateRangeRow = document.getElementById('dateRangeRow');
    const generateBtn = document.getElementById('generateBtn');
    const reportForm = document.getElementById('reportForm');
    
    const reportDescriptions = {
        'daily_sales': {
            title: 'Laporan Penjualan Harian',
            description: 'Menampilkan data penjualan harian termasuk jumlah transaksi, total penjualan, pajak, dan diskon. Berguna untuk melacak tren kinerja harian.',
            needsDateRange: true
        },
        'monthly_sales': {
            title: 'Laporan Penjualan Bulanan',
            description: 'Memberikan ringkasan penjualan bulanan dengan data agregat. Sempurna untuk analisis kinerja bulanan dan perbandingan.',
            needsDateRange: true
        },
        'stock_report': {
            title: 'Laporan Stok Barang',
            description: 'Ikhtisar inventaris komprehensif yang menunjukkan tingkat stok saat ini, nilai, dan detail item di semua kategori.',
            needsDateRange: false
        },
        'low_stock': {
            title: 'Laporan Stok Menipis',
            description: 'Mengidentifikasi item yang stoknya menipis atau habis. Penting untuk manajemen inventaris.',
            needsDateRange: false
        },
        'item_trends': {
            title: 'Laporan Tren Barang',
            description: 'Menganalisis barang mana yang paling laris selama periode yang dipilih. Membantu dengan wawasan kinerja produk.',
            needsDateRange: true
        },
        'cashier_performance': {
            title: 'Laporan Performa Kasir',
            description: 'Mengevaluasi kinerja kasir termasuk jumlah transaksi, total penjualan, dan nilai transaksi rata-rata.',
            needsDateRange: true
        },
        'sales_by_category': {
            title: 'Laporan Penjualan per Kategori',
            description: 'Memecah kinerja penjualan berdasarkan kategori produk. Berguna untuk analisis kategori dan perencanaan.',
            needsDateRange: true
        },
        'profit_analysis': {
            title: 'Laporan Analisis Keuntungan',
            description: 'Analisis keuntungan detail yang menunjukkan pendapatan, biaya, dan margin keuntungan untuk setiap item yang dijual.',
            needsDateRange: true
        }
    };
    
    function updateReportInfo() {
        const selectedType = typeSelect.value;
        const info = reportDescriptions[selectedType];
        
        if (info) {
            reportInfo.innerHTML = `
                <p class="mb-1"><strong>${info.title}</strong></p>
                <p class="mb-0">${info.description}</p>
            `;
            
            // Show/hide date range based on report type
            if (info.needsDateRange) {
                dateRangeRow.style.display = 'flex';
                document.getElementById('start_date').required = true;
                document.getElementById('end_date').required = true;
            } else {
                dateRangeRow.style.display = 'none';
                document.getElementById('start_date').required = false;
                document.getElementById('end_date').required = false;
            }
        }
    }
    
    function resetForm() {
        reportForm.reset();
        const now = new Date();
        const indonesiaTime = new Date(now.getTime() + (7 * 60 * 60 * 1000)); // UTC+7 for WIB
        document.getElementById('name').value = 'Laporan ' + indonesiaTime.toLocaleDateString('id-ID') + ' ' + indonesiaTime.toLocaleTimeString('id-ID', {hour: '2-digit', minute: '2-digit'});
        document.getElementById('start_date').value = new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0];
        document.getElementById('end_date').value = new Date().toISOString().split('T')[0];
        updateReportInfo();
    }
    
    // Form submission with loading state
    reportForm.addEventListener('submit', function(e) {
        console.log('Form submitted');
        console.log('Form data:', new FormData(reportForm));
        
        // Check if form is valid
        if (!reportForm.checkValidity()) {
            console.log('Form validation failed');
            e.preventDefault();
            return false;
        }
        
        console.log('Form validation passed');
        generateBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Membuat Laporan...';
        generateBtn.disabled = true;
    });
    
    typeSelect.addEventListener('change', updateReportInfo);
    updateReportInfo();
    
    // Date validation
    document.getElementById('start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDate = new Date(document.getElementById('end_date').value);
        
        if (startDate > endDate) {
            document.getElementById('end_date').value = this.value;
        }
    });
    
    document.getElementById('end_date').addEventListener('change', function() {
        const startDate = new Date(document.getElementById('start_date').value);
        const endDate = new Date(this.value);
        
        if (endDate < startDate) {
            this.setCustomValidity('Tanggal selesai tidak boleh lebih awal dari tanggal mulai');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
@endpush
@endsection

