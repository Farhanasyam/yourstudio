@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Stock Adjustment Details'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Stock Adjustment Details - {{ $stockAdjustment->transaction_code }}</h6>
                            <div>
                                <a href="{{ route('stock-adjustments.edit', $stockAdjustment) }}" class="btn btn-warning btn-sm me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Adjustment Information</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-sm font-weight-bold">Transaction Code:</td>
                                            <td class="text-sm">{{ $stockAdjustment->transaction_code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Item:</td>
                                            <td class="text-sm">{{ $stockAdjustment->item->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">SKU:</td>
                                            <td class="text-sm">{{ $stockAdjustment->item->sku ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Adjustment Type:</td>
                                            <td class="text-sm">
                                                <span class="badge badge-sm bg-gradient-{{ $stockAdjustment->type === 'increase' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($stockAdjustment->type) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Quantity:</td>
                                            <td class="text-sm">
                                                <span class="font-weight-bold {{ $stockAdjustment->type === 'increase' ? 'text-success' : 'text-warning' }}">
                                                    {{ $stockAdjustment->type === 'increase' ? '+' : '-' }}{{ number_format($stockAdjustment->quantity) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Stock Before:</td>
                                            <td class="text-sm">{{ number_format($stockAdjustment->stock_before) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Stock After:</td>
                                            <td class="text-sm font-weight-bold">{{ number_format($stockAdjustment->stock_after) }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Additional Information</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-sm font-weight-bold">Reason:</td>
                                            <td class="text-sm">{{ $stockAdjustment->reason }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Notes:</td>
                                            <td class="text-sm">{{ $stockAdjustment->notes ?? 'No notes' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Adjustment Date:</td>
                                            <td class="text-sm">{{ $stockAdjustment->adjustment_date->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Created By:</td>
                                            <td class="text-sm">{{ $stockAdjustment->user->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Created At:</td>
                                            <td class="text-sm">{{ $stockAdjustment->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Updated At:</td>
                                            <td class="text-sm">{{ $stockAdjustment->updated_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card bg-gradient-{{ $stockAdjustment->type === 'increase' ? 'success' : 'warning' }}">
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-8">
                                                <div class="numbers">
                                                    <p class="text-white text-sm mb-0 text-capitalize font-weight-bold">Stock Change Summary</p>
                                                    <h5 class="text-white font-weight-bolder mb-0">
                                                        {{ number_format($stockAdjustment->stock_before) }} → {{ number_format($stockAdjustment->stock_after) }}
                                                    </h5>
                                                </div>
                                            </div>
                                            <div class="col-4 text-end">
                                                <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                                    <i class="fas fa-{{ $stockAdjustment->type === 'increase' ? 'arrow-up' : 'arrow-down' }} text-lg opacity-10" 
                                                       aria-hidden="true"></i>
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