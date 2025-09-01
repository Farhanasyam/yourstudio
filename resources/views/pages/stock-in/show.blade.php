@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Stock In Details'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Stock In Details - {{ $stockIn->transaction_code }}</h6>
                            <div>
                                @if($stockIn->status !== 'completed')
                                    <a href="{{ route('stock-in.edit', $stockIn) }}" class="btn btn-warning btn-sm me-2">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif
                                <a href="{{ route('stock-in.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to List
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Transaction Information</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-sm font-weight-bold">Transaction Code:</td>
                                            <td class="text-sm">{{ $stockIn->transaction_code }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Supplier:</td>
                                            <td class="text-sm">{{ $stockIn->supplier->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Transaction Date:</td>
                                            <td class="text-sm">{{ $stockIn->transaction_date->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Status:</td>
                                            <td class="text-sm">
                                                <span class="badge badge-sm bg-gradient-{{ $stockIn->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ ucfirst($stockIn->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Created By:</td>
                                            <td class="text-sm">{{ $stockIn->user->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Total Amount:</td>
                                            <td class="text-sm font-weight-bold text-success">Rp {{ number_format($stockIn->total_amount, 0, ',', '.') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6">Additional Information</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <tr>
                                            <td class="text-sm font-weight-bold">Notes:</td>
                                            <td class="text-sm">{{ $stockIn->notes ?? 'No notes' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Created At:</td>
                                            <td class="text-sm">{{ $stockIn->created_at->format('d M Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-sm font-weight-bold">Updated At:</td>
                                            <td class="text-sm">{{ $stockIn->updated_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-uppercase text-xs font-weight-bolder opacity-6 mb-3">Items</h6>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantity</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Purchase Price</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($stockIn->details as $detail)
                                                <tr>
                                                    <td>
                                                        <div class="d-flex px-2 py-1">
                                                            <div class="icon icon-shape icon-sm me-3 bg-gradient-primary shadow text-center">
                                                                <i class="ni ni-box-2 text-white opacity-10"></i>
                                                            </div>
                                                            <div class="d-flex flex-column justify-content-center">
                                                                <h6 class="mb-0 text-sm">{{ $detail->item->name }}</h6>
                                                                <p class="text-xs text-secondary mb-0">{{ $detail->item->sku }}</p>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="text-secondary text-xs font-weight-bold">{{ number_format($detail->quantity) }}</span>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            Rp {{ number_format($detail->purchase_price, 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                    <td class="align-middle text-center text-sm">
                                                        <span class="text-secondary text-xs font-weight-bold">
                                                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                                                        </span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th colspan="3" class="text-end">Total:</th>
                                                <th class="text-center">
                                                    <span class="text-success font-weight-bold">
                                                        Rp {{ number_format($stockIn->total_amount, 0, ',', '.') }}
                                                    </span>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection