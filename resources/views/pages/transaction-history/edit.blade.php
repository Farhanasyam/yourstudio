@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit Transaksi'])
    
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex align-items-center">
                            <h6 class="mb-0">Edit Transaksi #{{ $transaction->transaction_code }}</h6>
                            <a href="{{ route('transaction-history.show', $transaction->id) }}" class="btn btn-secondary btn-sm ms-auto">
                                <i class="fas fa-arrow-left me-1"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                            </div>
                        @endif
                        
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('transaction-history.update', $transaction->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            
                            <!-- Transaction Info -->
                            <div class="row mb-4">
                                <div class="col-md-3">
                                    <label class="form-label">Kode Transaksi</label>
                                    <input type="text" class="form-control" value="{{ $transaction->transaction_code }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tanggal</label>
                                    <input type="text" class="form-control" value="{{ $transaction->transaction_date->format('d/m/Y H:i') }}" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Kasir</label>
                                    <input type="text" class="form-control" value="@if($transaction->cashier){{ $transaction->cashier->name }}@else Kasir tidak ditemukan @endif" readonly>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Status</label>
                                    <input type="text" class="form-control" value="{{ ucfirst($transaction->status) }}" readonly>
                                </div>
                            </div>

                            <!-- Items Table -->
                            <div class="table-responsive">
                                <table class="table align-items-center mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Item</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Harga Satuan</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Qty</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Subtotal</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Stok Tersedia</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transaction->transactionItems as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $item->item_name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">SKU: {{ $item->item_sku }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                                <input type="number" 
                                                       name="items[{{ $loop->index }}][quantity]" 
                                                       value="{{ $item->quantity }}" 
                                                       min="1" 
                                                       max="{{ $item->item->stock_quantity + $item->quantity }}"
                                                       class="form-control form-control-sm text-center"
                                                       style="width: 80px;"
                                                       onchange="updateSubtotal(this, {{ $item->unit_price }})">
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold subtotal-{{ $item->id }}">
                                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-secondary text-xs font-weight-bold">
                                                    {{ $item->item->stock_quantity + $item->quantity }}
                                                </span>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Summary -->
                            <div class="row mt-4">
                                <div class="col-md-6 offset-md-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Subtotal:</span>
                                                <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Diskon:</span>
                                                <span>- Rp {{ number_format($transaction->discount_amount, 0, ',', '.') }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Pajak:</span>
                                                <span>+ Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span>
                                            </div>
                                            <hr>
                                            <div class="d-flex justify-content-between">
                                                <strong>Total:</strong>
                                                <strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="row mt-4">
                                <div class="col-12 text-end">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
<script>
function updateSubtotal(input, unitPrice) {
    const quantity = parseInt(input.value);
    const subtotal = quantity * unitPrice;
    const itemId = input.name.match(/\[(\d+)\]\[quantity\]/)[1];
    
    // Update subtotal display
    const subtotalElement = document.querySelector(`.subtotal-${itemId}`);
    if (subtotalElement) {
        subtotalElement.textContent = `Rp ${subtotal.toLocaleString('id-ID')}`;
    }
}
</script>
@endpush
