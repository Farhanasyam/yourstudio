@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Detail Laporan'])

<div class="container-fluid py-4">
    <!-- Report Header -->
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6>{{ $report->name }}</h6>
                        </div>
                        <div class="col-6 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('reports.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center mb-3">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle me-3">
                                    @switch($report->type)
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
                                <div>
                                    <h6 class="mb-0">{{ \App\Models\Report::getTypes()[$report->type] ?? ucwords(str_replace('_', ' ', $report->type)) }}</h6>
                                    <p class="text-sm text-secondary mb-0">Jenis Laporan</p>
                                </div>
                            </div>
                            <p><strong>Dibuat Oleh:</strong> {{ $report->generatedBy->name }}</p>
                            <p><strong>Dibuat Pada:</strong> {{ $report->generated_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') }} WIB</p>
                        </div>
                        <div class="col-md-6">
                            @if(isset($report->data['period']))
                            <p><strong>Periode:</strong> 
                                {{ \Carbon\Carbon::parse($report->data['period']['start_date'])->format('d M Y') }} - 
                                {{ \Carbon\Carbon::parse($report->data['period']['end_date'])->format('d M Y') }}
                            </p>
                            @endif
                            <p><strong>Status:</strong> 
                                @if($report->isExpired())
                                    <span class="badge badge-sm bg-gradient-warning">Kadaluarsa</span>
                                @else
                                    <span class="badge badge-sm bg-gradient-success">Aktif</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    @if(isset($report->data['summary']))
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <h6><i class="fas fa-chart-line"></i> Ringkasan</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach($report->data['summary'] as $key => $value)
                            @if(!str_contains($key, 'total_discount') && !str_contains($key, 'total_tax'))
                        <div class="col-md-3 col-sm-6 mb-3">
                            <div class="card h-100">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-uppercase font-weight-bold">
                                                    {{ ucwords(str_replace('_', ' ', $key)) }}
                                                </p>
                                                <h5 class="font-weight-bolder">
                                                    @if(is_numeric($value))
                                                        @if(str_contains($key, 'revenue') || str_contains($key, 'sales') || str_contains($key, 'cost') || str_contains($key, 'profit') || str_contains($key, 'value'))
                                                            Rp {{ number_format($value, 0, ',', '.') }}
                                                        @elseif(str_contains($key, 'margin'))
                                                            {{ number_format($value, 2) }}%
                                                        @elseif(str_contains($key, 'total') && (str_contains($key, 'items') || str_contains($key, 'quantity') || str_contains($key, 'count') || str_contains($key, 'categories')))
                                                            {{ number_format($value) }}
                                                        @elseif(str_contains($key, 'total'))
                                                            Rp {{ number_format($value, 0, ',', '.') }}
                                                        @else
                                                            {{ number_format($value) }}
                                                        @endif
                                                    @elseif(is_array($value))
                                                        @if(isset($value['date']))
                                                            {{ \Carbon\Carbon::parse($value['date'])->format('d M Y') }}
                                                        @elseif(isset($value['name']))
                                                            {{ $value['name'] }}
                                                        @elseif(isset($value['year']) && isset($value['month']))
                                                            {{ \Carbon\Carbon::create($value['year'], $value['month'])->format('M Y') }}
                                                        @elseif(isset($value['name']) && isset($value['sku']))
                                                            {{ $value['name'] }} ({{ $value['sku'] }})
                                                        @elseif(isset($value['name']) && isset($value['email']))
                                                            {{ $value['name'] }} - {{ $value['email'] }}
                                                        @elseif(isset($value['name']) && isset($value['code']))
                                                            {{ $value['name'] }} ({{ $value['code'] }})
                                                        @else
                                                            {{ json_encode($value) }}
                                                        @endif
                                                    @elseif(is_object($value))
                                                        @if(isset($value->name))
                                                            @if(isset($value->sku))
                                                                {{ $value->name }} ({{ $value->sku }})
                                                            @elseif(isset($value->email))
                                                                {{ $value->name }} - {{ $value->email }}
                                                            @elseif(isset($value->code))
                                                                {{ $value->name }} ({{ $value->code }})
                                                            @else
                                                                {{ $value->name }}
                                                            @endif
                                                        @elseif(isset($value->date))
                                                            {{ \Carbon\Carbon::parse($value->date)->format('d M Y') }}
                                                        @elseif(isset($value->year) && isset($value->month))
                                                            {{ \Carbon\Carbon::create($value->year, $value->month)->format('M Y') }}
                                                        @else
                                                            {{ json_encode($value) }}
                                                        @endif
                                                    @else
                                                        {{ $value }}
                                                    @endif
                                                </h5>
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
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Detailed Data Table -->
    @if(isset($report->data['data']) && count($report->data['data']) > 0)
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6><i class="fas fa-table"></i> Data Detail</h6>
                        </div>
                        <div class="col-6 text-end">
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-outline-info btn-sm" onclick="exportTableToCSV()">
                                    <i class="fas fa-file-csv"></i> Export CSV
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="printTable()">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0" id="dataTable">
                            <thead>
                                <tr>
                                    @foreach(array_keys((array) $report->data['data'][0]) as $header)
                                        @if($header !== 'category_id')
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            {{ ucwords(str_replace('_', ' ', $header)) }}
                                        </th>
                                        @endif
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($report->data['data'] as $row)
                                <tr>
                                    @foreach((array) $row as $key => $value)
                                        @if($key !== 'category_id')
                                        <td class="align-middle">
                                            @if(is_numeric($value))
                                                @if(str_contains($key, 'revenue') || str_contains($key, 'sales') || str_contains($key, 'cost') || str_contains($key, 'profit') || str_contains($key, 'price'))
                                                    <span class="text-success font-weight-bold">Rp {{ number_format($value, 0, ',', '.') }}</span>
                                                @elseif(str_contains($key, 'margin'))
                                                    <span class="text-info font-weight-bold">{{ number_format($value, 2) }}%</span>
                                                @elseif(str_contains($key, 'quantity') || str_contains($key, 'count'))
                                                    {{ number_format($value) }}
                                                @else
                                                    {{ number_format($value) }}
                                                @endif
                                            @elseif(is_array($value))
                                                @if(isset($value['date']))
                                                    {{ \Carbon\Carbon::parse($value['date'])->format('d M Y') }}
                                                @elseif(isset($value['name']))
                                                    {{ $value['name'] }}
                                                @elseif(isset($value['year']) && isset($value['month']))
                                                    {{ \Carbon\Carbon::create($value['year'], $value['month'])->format('M Y') }}
                                                @else
                                                    {{ json_encode($value) }}
                                                @endif
                                            @elseif(is_object($value))
                                                @if(isset($value->name))
                                                    {{ $value->name }}
                                                @elseif(isset($value->date))
                                                    {{ \Carbon\Carbon::parse($value->date)->format('d M Y') }}
                                                @elseif(isset($value->year) && isset($value->month))
                                                    {{ \Carbon\Carbon::create($value->year, $value->month)->format('M Y') }}
                                                @else
                                                    {{ json_encode($value) }}
                                                @endif
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                        @endif
                                    @endforeach
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- No Data Message -->
    @if(empty($report->data['data']))
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-body text-center py-5">
                    <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Tidak Ada Data</h5>
                    <p class="text-muted">Tidak ada data yang tersedia untuk periode dan jenis laporan yang dipilih.</p>
                    <a href="{{ route('reports.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Buat Laporan Baru
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function exportTableToCSV() {
    const table = document.getElementById('dataTable');
    const rows = table.querySelectorAll('tr');
    let csv = [];
    
    for (let i = 0; i < rows.length; i++) {
        const row = [], cols = rows[i].querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let cellText = cols[j].innerText.replace(/"/g, '""');
            row.push('"' + cellText + '"');
        }
        
        csv.push(row.join(','));
    }
    
    const csvContent = csv.join('\n');
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    
    if (link.download !== undefined) {
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', '{{ $report->name }}.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

function printTable() {
    const printWindow = window.open('', '_blank');
    const table = document.getElementById('dataTable');
    
    printWindow.document.write(`
        <html>
            <head>
                <title>{{ $report->name }}</title>
                <style>
                    body { font-family: Arial, sans-serif; }
                    table { border-collapse: collapse; width: 100%; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .text-center { text-align: center; }
                </style>
            </head>
            <body>
                <h2>{{ $report->name }}</h2>
                <p><strong>Jenis:</strong> {{ \App\Models\Report::getTypes()[$report->type] ?? ucwords(str_replace('_', ' ', $report->type)) }}</p>
                <p><strong>Dibuat:</strong> {{ $report->generated_at->setTimezone('Asia/Jakarta')->format('d M Y H:i:s') }} WIB</p>
                <p><strong>Dibuat Oleh:</strong> {{ $report->generatedBy->name }}</p>
                ${table.outerHTML}
            </body>
        </html>
    `);
    
    printWindow.document.close();
    printWindow.print();
}

// Auto refresh if report is expired
@if($report->isExpired())
setTimeout(function() {
    Swal.fire({
        title: 'Laporan Kadaluarsa',
        text: 'Laporan ini sudah kadaluarsa. Apakah Anda ingin membuat laporan baru?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Buat Baru',
        cancelButtonText: 'Tidak'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '{{ route("reports.create", ["type" => $report->type]) }}';
        }
    });
}, 2000);
@endif
</script>
@endpush

