@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Laporan & Analisis'])

<div class="container-fluid py-4">
    <!-- Statistics Cards -->
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Laporan</p>
                                <h5 class="font-weight-bolder">{{ $stats['total_reports'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Hari Ini</p>
                                <h5 class="font-weight-bolder">{{ $stats['reports_today'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="ni ni-paper-diploma text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Minggu Ini</p>
                                <h5 class="font-weight-bolder">{{ $stats['reports_this_week'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                <i class="ni ni-world text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-uppercase font-weight-bold">Bulan Ini</p>
                                <h5 class="font-weight-bolder">{{ $stats['reports_this_month'] }}</h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="ni ni-cart text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6>Buat Laporan Baru</h6>
                        </div>
                        <div class="col-6 text-end">
                            <a href="{{ route('reports.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Laporan Baru
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($reportTypes as $type => $name)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle mb-3">
                                        @switch($type)
                                            @case('daily_sales')
                                                <i class="ni ni-calendar-grid-58 text-lg opacity-10"></i>
                                                @break
                                            @case('monthly_sales')
                                                <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
                                                @break
                                            @case('stock_report')
                                                <i class="ni ni-box-2 text-lg opacity-10"></i>
                                                @break
                                            @case('low_stock')
                                                <i class="ni ni-notification-70 text-lg opacity-10"></i>
                                                @break
                                            @case('item_trends')
                                                <i class="ni ni-chart-pie-35 text-lg opacity-10"></i>
                                                @break
                                            @case('cashier_performance')
                                                <i class="ni ni-single-02 text-lg opacity-10"></i>
                                                @break
                                            @case('sales_by_category')
                                                <i class="ni ni-tag text-lg opacity-10"></i>
                                                @break
                                            @case('profit_analysis')
                                                <i class="ni ni-money-coins text-lg opacity-10"></i>
                                                @break
                                            @default
                                                <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
                                        @endswitch
                                    </div>
                                    <h6 class="card-title">{{ $name }}</h6>
                                    <p class="text-xs text-secondary mb-3">
                                        @switch($type)
                                            @case('daily_sales')
                                                Laporan penjualan harian
                                                @break
                                            @case('monthly_sales')
                                                Laporan penjualan bulanan
                                                @break
                                            @case('stock_report')
                                                Laporan stok barang
                                                @break
                                            @case('low_stock')
                                                Laporan stok menipis
                                                @break
                                            @case('item_trends')
                                                Tren penjualan barang
                                                @break
                                            @case('cashier_performance')
                                                Performa kasir
                                                @break
                                            @case('sales_by_category')
                                                Penjualan per kategori
                                                @break
                                            @case('profit_analysis')
                                                Analisis keuntungan
                                                @break
                                        @endswitch
                                    </p>
                                    <a href="{{ route('reports.create', ['type' => $type]) }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-chart-line"></i> Buat Laporan
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Reports -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6>Laporan Terbaru</h6>
                        </div>
                        <div class="col-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="refreshReports()">
                                    <i class="fas fa-sync-alt"></i> Refresh
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Laporan</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Jenis</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dibuat Oleh</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tanggal</th>
                                    <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentReports as $report)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $report->name }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    @if(isset($report->data['period']))
                                                        {{ \Carbon\Carbon::parse($report->data['period']['start_date'])->format('d M Y') }} - 
                                                        {{ \Carbon\Carbon::parse($report->data['period']['end_date'])->format('d M Y') }}
                                                    @else
                                                        {{ $report->type }}
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-sm bg-gradient-info">
                                            {{ $reportTypes[$report->type] ?? ucwords(str_replace('_', ' ', $report->type)) }}
                                        </span>
                                    </td>
                                    <td class="align-middle text-center text-sm">
                                        <span class="badge badge-sm bg-gradient-success">{{ $report->generatedBy->name }}</span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <span class="text-secondary text-xs font-weight-bold">
                                            {{ $report->generated_at->setTimezone('Asia/Jakarta')->format('d M Y H:i') }} WIB
                                        </span>
                                    </td>
                                    <td class="align-middle text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('reports.show', $report) }}" class="btn btn-link text-info mb-0" title="Lihat Laporan">
                                                <i class="fas fa-eye text-xs"></i>
                                            </a>
                                            <button type="button" class="btn btn-link text-danger mb-0" 
                                                    onclick="deleteConfirmation('delete-form-report-{{ $report->id }}')" title="Hapus">
                                                <i class="fas fa-trash text-xs"></i>
                                            </button>
                                        </div>
                                        <form id="delete-form-report-{{ $report->id }}" action="{{ route('reports.destroy', $report) }}" method="POST" class="d-none">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-center">
                                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum Ada Laporan</h5>
                                            <p class="text-muted">Mulai buat laporan pertama Anda untuk melihat analisis data.</p>
                                            <a href="{{ route('reports.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Buat Laporan Pertama
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
</div>
@endsection

@push('scripts')
<script>
function deleteConfirmation(formId) {
    Swal.fire({
        title: 'Konfirmasi Hapus',
        text: "Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(formId).submit();
        }
    });
}

function refreshReports() {
    location.reload();
}

// Auto refresh every 5 minutes
setInterval(function() {
    // Only refresh if user is still on the page
    if (document.visibilityState === 'visible') {
        location.reload();
    }
}, 300000); // 5 minutes
</script>
@endpush

