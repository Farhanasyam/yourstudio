@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Transaction Detail'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Transaction Detail - {{ $transaction->transaction_code }}</h6>
                            <div>
                                <a href="{{ route('sales.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back to Sales
                                </a>
                                <button onclick="window.print()" class="btn btn-primary btn-sm">
                                    <i class="fas fa-print"></i> Print
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Transaction Information</h6>
                                <ul class="list-group">
                                    <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                        <strong class="text-dark">Transaction Code:</strong> {{ $transaction->transaction_code }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Date:</strong> {{ $transaction->transaction_date->format('d M Y H:i') }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Cashier:</strong> {{ $transaction->cashier->name }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Payment Method:</strong> 
                                        <span class="badge badge-sm bg-gradient-info">{{ ucfirst($transaction->payment_method) }}</span>
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Status:</strong> 
                                        @if($transaction->status == 'completed')
                                            <span class="badge badge-sm bg-gradient-success">Completed</span>
                                        @elseif($transaction->status == 'pending')
                                            <span class="badge badge-sm bg-gradient-warning">Pending</span>
                                        @else
                                            <span class="badge badge-sm bg-gradient-danger">Cancelled</span>
                                        @endif
                                    </li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Payment Summary</h6>
                                <ul class="list-group">
                                    <li class="list-group-item border-0 ps-0 pt-0 text-sm">
                                        <strong class="text-dark">Subtotal:</strong> Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Tax:</strong> Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Discount:</strong> Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Total Amount:</strong> 
                                        <span class="text-success font-weight-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Paid Amount:</strong> Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}
                                    </li>
                                    <li class="list-group-item border-0 ps-0 text-sm">
                                        <strong class="text-dark">Change:</strong> Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}
                                    </li>
                                </ul>
                            </div>
                        </div>

                        @if($transaction->notes)
                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Notes</h6>
                                <p class="text-sm">{{ $transaction->notes }}</p>
                            </div>
                        </div>
                        @endif

                        <div class="row mt-4">
                            <div class="col-12">
                                <h6 class="text-uppercase text-body text-xs font-weight-bolder mb-3">Items Purchased</h6>
                                <div class="table-responsive">
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SKU</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Quantity</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Unit Price</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Discount</th>
                                                <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($transaction->transactionItems as $item)
                                            <tr>
                                                <td>
                                                    <div class="d-flex px-2 py-1">
                                                        <div class="d-flex flex-column justify-content-center">
                                                            <h6 class="mb-0 text-sm">{{ $item->item_name }}</h6>
                                                            @if($item->barcode_scanned)
                                                            <p class="text-xs text-secondary mb-0">Barcode: {{ $item->barcode_scanned }}</p>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <p class="text-xs font-weight-bold mb-0">{{ $item->item_sku }}</p>
                                                </td>
                                                <td class="align-middle text-center text-sm">
                                                    <span class="badge badge-sm bg-gradient-success">{{ $item->quantity }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->discount_per_item, 0, ',', '.') }}</span>
                                                </td>
                                                <td class="align-middle text-center">
                                                    <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection