@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Stock Adjustments'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Stock Adjustments</h6>
                            <a href="{{ route('stock-adjustments.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Stock Adjustment
                            </a>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Transaction Code</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">
                                            Item</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Type</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Quantity</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Stock Before/After</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Date</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">
                                            Created By</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($stockAdjustments as $adjustment)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="icon icon-shape icon-sm me-3 bg-gradient-{{ $adjustment->type === 'increase' ? 'success' : 'warning' }} shadow text-center">
                                                        <i class="fas fa-{{ $adjustment->type === 'increase' ? 'arrow-up' : 'arrow-down' }} text-white opacity-10"></i>
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $adjustment->transaction_code }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $adjustment->reason }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">{{ $adjustment->item->name ?? 'N/A' }}</p>
                                                <p class="text-xs text-secondary mb-0">{{ $adjustment->item->sku ?? '' }}</p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="badge badge-sm bg-gradient-{{ $adjustment->type === 'increase' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($adjustment->type) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ $adjustment->type === 'increase' ? '+' : '-' }}{{ number_format($adjustment->quantity) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ number_format($adjustment->stock_before) }} → {{ number_format($adjustment->stock_after) }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ $adjustment->adjustment_date->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ $adjustment->user->name ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="dropdown">
                                                    <a href="#" class="btn btn-link text-dark p-0 mb-0" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-v text-xs"></i>
                                                    </a>
                                                    <div class="dropdown-menu">
                                                        <a class="dropdown-item" href="{{ route('stock-adjustments.show', $adjustment) }}">
                                                            <i class="fas fa-eye me-2"></i> View
                                                        </a>
                                                        <a class="dropdown-item" href="{{ route('stock-adjustments.edit', $adjustment) }}">
                                                            <i class="fas fa-edit me-2"></i> Edit
                                                        </a>
                                                        <div class="dropdown-divider"></div>
                                                        <form action="{{ route('stock-adjustments.destroy', $adjustment) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this stock adjustment? This will reverse the adjustment.')">
                                                                <i class="fas fa-trash me-2"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="fas fa-adjust fa-3x text-secondary mb-3"></i>
                                                    <h6 class="text-secondary">No Stock Adjustments found</h6>
                                                    <p class="text-xs text-secondary">Create your first stock adjustment to get started.</p>
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
