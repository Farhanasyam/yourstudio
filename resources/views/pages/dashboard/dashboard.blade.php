{{-- resources/views/pages/dashboard/dashboard.blade.php --}}
@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
@include('layouts.navbars.auth.topnav', ['title' => 'Dashboard'])
<div class="container-fluid py-4">
    {{-- Pending Users Notification for Super Admin --}}
    @if(auth()->user()->isSuperAdmin() && $pendingUsers->count() > 0)
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-users me-2"></i>
                    <strong>Perhatian!</strong> Ada {{ $pendingUsers->count() }} user baru yang menunggu persetujuan.
                    <a href="{{ route('user-management.index', ['approval_status' => 'pending']) }}" class="btn btn-sm btn-warning ms-3">
                        <i class="fas fa-eye"></i> Lihat
                    </a>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endif
    
    @php
        $user = auth()->user();
        $today = now()->toDateString();
    @endphp
    <style>
        /* Clickable stat cards: the title link is stretched over the whole card,
           the small sub-links sit above it so they keep their own target */
        .stat-card { transition: transform .15s ease, box-shadow .15s ease; }
        .stat-card.is-link:hover { transform: translateY(-3px); box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.12) !important; }
        .stat-card .stretched-link { color: inherit; }
        .stat-card .stat-sublink { position: relative; z-index: 2; text-decoration: none; }
        .stat-card .stat-sublink:hover { text-decoration: underline; }
    </style>
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stat-card is-link h-100">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <a href="{{ route('sales.index', ['start_date' => $today, 'end_date' => $today]) }}" class="stretched-link">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Today's Sales</p>
                                </a>
                                <h5 class="font-weight-bolder">Rp {{ number_format($todaySales) }}</h5>
                                <p class="mb-0 text-sm">
                                    @if($salesGrowth > 0)
                                        <span class="text-success text-sm font-weight-bolder">+{{ number_format($salesGrowth, 1) }}%</span> vs yesterday
                                    @elseif($salesGrowth < 0)
                                        <span class="text-danger text-sm font-weight-bolder">{{ number_format($salesGrowth, 1) }}%</span> vs yesterday
                                    @else
                                        <span class="text-secondary text-sm font-weight-bolder">0%</span> vs yesterday
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                <i class="fas fa-coins text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stat-card h-100 {{ $user->canManageItems() ? 'is-link' : '' }}">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                @if($user->canManageItems())
                                    <a href="{{ route('items.index') }}" class="stretched-link">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Items</p>
                                    </a>
                                @else
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Items</p>
                                @endif
                                <h5 class="font-weight-bolder">{{ number_format($totalItems) }}</h5>
                                <p class="mb-0 text-sm">
                                    @if($user->canManageItems())
                                        <a href="{{ route('items.index', ['stock_status' => 'low_stock']) }}" class="stat-sublink text-dark">
                                            <span class="text-warning text-sm font-weight-bolder">{{ number_format($lowStockCount) }}</span> low stock
                                        </a>
                                        &middot;
                                        <a href="{{ route('items.index', ['stock_status' => 'out_of_stock']) }}" class="stat-sublink text-dark">
                                            <span class="text-danger text-sm font-weight-bolder">{{ number_format($outOfStockCount) }}</span> habis
                                        </a>
                                    @else
                                        <a href="#low-stock-items" class="stat-sublink text-dark">
                                            <span class="text-warning text-sm font-weight-bolder">{{ number_format($lowStockCount) }}</span> low stock
                                            &middot;
                                            <span class="text-danger text-sm font-weight-bolder">{{ number_format($outOfStockCount) }}</span> habis
                                        </a>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                <i class="fas fa-boxes text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stat-card h-100 {{ $user->canManageItems() ? 'is-link' : '' }}">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                @if($user->canManageItems())
                                    <a href="{{ route('suppliers.index', ['is_active' => 1]) }}" class="stretched-link">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Suppliers</p>
                                    </a>
                                @else
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Active Suppliers</p>
                                @endif
                                <h5 class="font-weight-bolder">{{ number_format($totalSuppliers) }}</h5>
                                <p class="mb-0 text-sm">
                                    @if($user->canManageItems())
                                        <a href="{{ route('stock-in.index', ['start_date' => now()->subDays(30)->toDateString()]) }}" class="stat-sublink text-dark">
                                            <span class="text-success text-sm font-weight-bolder">{{ number_format($recentDeliveriesCount) }}</span> deliveries (30 hari)
                                        </a>
                                    @else
                                        <span class="text-success text-sm font-weight-bolder">{{ number_format($recentDeliveriesCount) }}</span> deliveries (30 hari)
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow-warning text-center rounded-circle">
                                <i class="fas fa-truck text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-4">
            <div class="card stat-card h-100 {{ $user->isSuperAdmin() ? 'is-link' : '' }}">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                @if($user->isSuperAdmin())
                                    <a href="{{ route('user-management.index') }}" class="stretched-link">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">System Users</p>
                                    </a>
                                @else
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">System Users</p>
                                @endif
                                <h5 class="font-weight-bolder">{{ number_format($totalUsers) }}</h5>
                                <p class="mb-0 text-sm">
                                    @if($user->isSuperAdmin())
                                        <a href="{{ route('user-management.index', ['approval_status' => 'pending']) }}" class="stat-sublink text-dark">
                                            <span class="text-info text-sm font-weight-bolder">{{ $pendingUsers->count() }}</span> pending approvals
                                        </a>
                                    @else
                                        <span class="text-info text-sm font-weight-bolder">{{ $pendingUsers->count() }}</span> pending approvals
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                <i class="fas fa-users text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Quick Actions</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        {{-- Only show actions the current role can open --}}
                        <div class="col-md-3">
                            <a href="{{ route('kasir.index') }}" class="btn btn-primary w-100 mb-2">
                                <i class="fas fa-cart-plus me-2"></i> New Sale
                            </a>
                        </div>
                        @if($user->canManageItems())
                        <div class="col-md-3">
                            <a href="{{ route('stock-in.create') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-box me-2"></i> Stock In
                            </a>
                        </div>
                        <div class="col-md-3">
                            <a href="{{ route('items.create') }}" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-plus-circle me-2"></i> Add Item
                            </a>
                        </div>
                        @else
                        <div class="col-md-3">
                            <a href="{{ route('transaction-history.index') }}" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-list me-2"></i> Transaction History
                            </a>
                        </div>
                        @endif
                        @if($user->canViewReports())
                        <div class="col-md-3">
                            <a href="{{ route('reports.index') }}" class="btn btn-warning w-100 mb-2">
                                <i class="fas fa-chart-line me-2"></i> View Reports
                            </a>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Business Overview</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-gradient-info">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">Total Items</p>
                                                <h5 class="font-weight-bolder text-white">{{ number_format($totalItems) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="ni ni-box-2 text-lg text-info opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-gradient-warning">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">Total Categories</p>
                                                <h5 class="font-weight-bolder text-white">{{ number_format($totalCategories) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="ni ni-tag text-lg text-warning opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Items by Category</h6>
                </div>
                <div class="card-body">
                    <canvas id="itemsByCategoryChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Recent Sales</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Transaction Code</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Cashier</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">{{ $sale->transaction_code }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $sale->cashier->name ?? 'N/A' }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Rp {{ number_format($sale->total_amount) }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $sale->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent sales found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4" id="low-stock-items">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-start flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0">Low Stock Items</h6>
                        <p class="text-xs text-secondary mb-0">
                            <span class="text-danger font-weight-bold">{{ number_format($outOfStockCount) }} habis</span>
                            &middot;
                            <span class="text-warning font-weight-bold">{{ number_format($lowStockCount - $outOfStockCount) }} menipis</span>
                            @if($lowStockCount > $lowStockItems->count())
                                &middot; menampilkan {{ $lowStockItems->count() }} dari {{ number_format($lowStockCount) }}
                            @endif
                        </p>
                    </div>
                    @if(auth()->user()->canManageItems() && $lowStockCount > 0)
                        <div class="d-flex gap-2">
                            <a href="{{ route('items.index', ['stock_status' => 'out_of_stock']) }}" class="btn btn-sm btn-outline-danger mb-0">Lihat yang habis</a>
                            <a href="{{ route('items.index', ['stock_status' => 'low_stock']) }}" class="btn btn-sm btn-outline-warning mb-0">Lihat semua</a>
                        </div>
                    @endif
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item Name</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Category</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Stock / Min</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                @if(auth()->user()->canManageItems())
                                                    <a href="{{ route('items.show', $item) }}"><h6 class="mb-0 text-sm">{{ $item->name }}</h6></a>
                                                @else
                                                    <h6 class="mb-0 text-sm">{{ $item->name }}</h6>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $item->category->name ?? 'N/A' }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $item->stock_quantity }} / {{ $item->minimum_stock }} {{ $item->unit }}</p>
                                    </td>
                                    <td>
                                        @if($item->stock_quantity <= 0)
                                            <span class="badge badge-sm bg-gradient-danger">Habis</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-warning">Menipis</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No low stock items found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Recent Stock Ins</h6>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stock In ID</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Supplier</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Total Amount</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentStockIns as $stockIn)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">#{{ $stockIn->id }}</h6>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $stockIn->supplier->name ?? 'N/A' }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">Rp {{ number_format($stockIn->total_amount) }}</p>
                                    </td>
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">{{ $stockIn->created_at->setTimezone('Asia/Jakarta')->format('d/m/Y') }}</p>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center">No recent stock ins found</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-12 mb-4">
            <div class="card">
                <div class="card-header pb-0">
                    <h6>Sales Statistics</h6>
                </div>
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card bg-gradient-primary">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">Current Month Sales</p>
                                                <h5 class="font-weight-bolder text-white">Rp {{ number_format($currentMonthSales) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="ni ni-money-coins text-lg text-primary opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-gradient-success">
                                <div class="card-body p-3">
                                    <div class="row">
                                        <div class="col-8">
                                            <div class="numbers">
                                                <p class="text-sm mb-0 text-white text-uppercase font-weight-bold">Total Sales</p>
                                                <h5 class="font-weight-bolder text-white">Rp {{ number_format($totalSales) }}</h5>
                                            </div>
                                        </div>
                                        <div class="col-4 text-end">
                                            <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                                                <i class="ni ni-chart-bar-32 text-lg text-success opacity-10" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Items by Category Chart
    const itemsByCategoryCtx = document.getElementById('itemsByCategoryChart').getContext('2d');
    new Chart(itemsByCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($itemsByCategory->pluck('name')) !!},
            datasets: [{
                data: {{ json_encode($itemsByCategory->pluck('items_count')) }},
                backgroundColor: [
                    '#5e72e4', '#2dce89', '#fb6340', '#11cdef', '#f5365c',
                    '#8965e0', '#ffd600', '#8b9dc3', '#ddb892', '#7f5539'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
});
</script>
@endpush