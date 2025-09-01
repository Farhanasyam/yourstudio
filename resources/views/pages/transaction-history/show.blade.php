@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Detail Transaksi'])
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-lg-flex">
                            <div>
                                <h5 class="mb-0">Detail Transaksi</h5>
                                <p class="text-sm mb-0">{{ $transaction->transaction_code }}</p>
                            </div>
                            <div class="ms-auto my-auto mt-lg-0 mt-4">
                                <div class="ms-auto my-auto">
                                    <a href="/transaction-history" class="btn btn-outline-secondary btn-sm mb-0">
                                        <i class="fas fa-arrow-left me-1"></i> Kembali
                                    </a>
                                    @if(auth()->user()->isKasir() && $transaction->cashier_id == auth()->id() || auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                    <a href="{{ route('transaction-history.edit', $transaction->id) }}" class="btn bg-gradient-info btn-sm mb-0">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    @endif
                                    <a href="/kasir/receipt/{{ $transaction->id }}" target="_blank" class="btn bg-gradient-primary btn-sm mb-0">
                                        <i class="fas fa-print me-1"></i> Cetak Struk
                                    </a>
                                    <a href="/kasir/receipt/{{ $transaction->id }}?copy=1" target="_blank" class="btn bg-gradient-warning btn-sm mb-0">
                                        <i class="fas fa-copy me-1"></i> Cetak Ulang
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        <div class="row">
                            <!-- Transaction Info -->
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-sm font-weight-bolder mb-3">Informasi Transaksi</h6>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Kode Transaksi:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">{{ $transaction->transaction_code }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Tanggal & Waktu:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">{{ $transaction->transaction_date->format('d/m/Y H:i:s') }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Kasir:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">
                                            @if($transaction->cashier)
                                                {{ $transaction->cashier->name }}
                                            @else
                                                <span class="text-danger">Kasir tidak ditemukan</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Status:</p>
                                    </div>
                                    <div class="col-8">
                                        @php
                                            $statusColors = [
                                                'completed' => 'bg-gradient-success',
                                                'pending' => 'bg-gradient-warning',
                                                'cancelled' => 'bg-gradient-danger'
                                            ];
                                        @endphp
                                        <span class="badge badge-sm {{ $statusColors[$transaction->status] ?? 'bg-gradient-secondary' }}">
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </div>
                                </div>
                                @if($transaction->notes)
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Catatan:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">{{ $transaction->notes }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <!-- Payment Info -->
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-sm font-weight-bolder mb-3">Informasi Pembayaran</h6>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Metode Bayar:</p>
                                    </div>
                                    <div class="col-8">
                                        @php
                                            $paymentColors = [
                                                'cash' => 'bg-gradient-success',
                                                'card' => 'bg-gradient-info',
                                                'transfer' => 'bg-gradient-warning',
                                                'qris' => 'bg-gradient-primary'
                                            ];
                                        @endphp
                                        <span class="badge badge-sm {{ $paymentColors[$transaction->payment_method] ?? 'bg-gradient-secondary' }}">
                                            {{ strtoupper($transaction->payment_method) }}
                                        </span>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Subtotal:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                @if($transaction->discount_amount > 0)
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Diskon:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1 text-danger">-Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                @endif
                                @if($transaction->tax_amount > 0)
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Pajak:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                @endif
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Total:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1 font-weight-bold text-success">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Dibayar:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-4">
                                        <p class="text-sm font-weight-bold mb-1">Kembalian:</p>
                                    </div>
                                    <div class="col-8">
                                        <p class="text-sm mb-1">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Transaction Items -->
                        <h6 class="text-uppercase text-sm font-weight-bolder mb-3">Detail Item</h6>
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">SKU</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Harga Satuan</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qty</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Diskon</th>
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
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{ $item->quantity }}</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            @if($item->discount_per_item > 0)
                                            <span class="text-danger text-xs font-weight-bold">-Rp {{ number_format($item->discount_per_item, 0, ',', '.') }}</span>
                                            @else
                                            <span class="text-secondary text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light">
                                        <td colspan="4" class="text-end font-weight-bold">Total Item:</td>
                                        <td class="text-center font-weight-bold">{{ $transaction->transactionItems->sum('quantity') }}</td>
                                        <td class="text-center font-weight-bold text-success">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
