@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'History Transaksi'])
    
    <div class="container-fluid py-4">
        <!-- Statistics Cards -->
        <div class="row">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Hari Ini</p>
                                    <h5 class="font-weight-bolder">{{ $stats['today_count'] }} Transaksi</h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">Rp {{ number_format($stats['today_total'], 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle">
                                    <i class="ni ni-calendar-grid-58 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Bulan Ini</p>
                                    <h5 class="font-weight-bolder">{{ $stats['month_count'] }} Transaksi</h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">Rp {{ number_format($stats['month_total'], 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-info shadow-info text-center rounded-circle">
                                    <i class="ni ni-chart-bar-32 text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Total Transaksi</p>
                                    <h5 class="font-weight-bolder">{{ $stats['total_count'] }}</h5>
                                    <p class="mb-0">
                                        <span class="text-success text-sm font-weight-bolder">Rp {{ number_format($stats['total_amount'], 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                                    <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-sm-6">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Rata-rata</p>
                                    <h5 class="font-weight-bolder">
                                        @if($stats['total_count'] > 0)
                                            Rp {{ number_format($stats['total_amount'] / $stats['total_count'], 0, ',', '.') }}
                                        @else
                                            Rp 0
                                        @endif
                                    </h5>
                                    <p class="mb-0">
                                        <span class="text-secondary text-sm">per transaksi</span>
                                    </p>
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

        <!-- Filters -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <div class="d-lg-flex">
                            <div>
                                <h5 class="mb-0">Filter & Pencarian</h5>
                                <p class="text-sm mb-0">Gunakan filter untuk mencari transaksi tertentu</p>
                            </div>
                            <div class="ms-auto my-auto mt-lg-0 mt-4">
                                <div class="ms-auto my-auto">
                                    @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                    <button type="button" class="btn bg-gradient-warning btn-sm mb-0 me-2" onclick="fixCashierData()">
                                        <i class="fas fa-wrench me-1"></i> Fix Cashier Data
                                    </button>
                                    @endif
                                    <a href="/transaction-history-export?{{ http_build_query(request()->query()) }}" class="btn bg-gradient-primary btn-sm mb-0">
                                        <i class="fas fa-download me-1"></i> Export CSV
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-0 pb-0">
                        <form method="GET" action="/transaction-history">
                            <div class="row px-4">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Tanggal Mulai</label>
                                        <input type="date" class="form-control" name="start_date" value="{{ request('start_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Tanggal Akhir</label>
                                        <input type="date" class="form-control" name="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-control-label">Status</label>
                                        <select class="form-select" name="status">
                                            <option value="">Semua Status</option>
                                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-control-label">Pembayaran</label>
                                        <select class="form-select" name="payment_method">
                                            <option value="">Semua Metode</option>
                                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Tunai</option>
                                            <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Kartu</option>
                                            <option value="transfer" {{ request('payment_method') == 'transfer' ? 'selected' : '' }}>Transfer</option>
                                            <option value="qris" {{ request('payment_method') == 'qris' ? 'selected' : '' }}>QRIS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label class="form-control-label">Kode Transaksi</label>
                                        <input type="text" class="form-control" name="search" placeholder="Cari kode..." value="{{ request('search') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row px-4 pb-4">
                                <div class="col-md-12">
                                    <button type="submit" class="btn bg-gradient-info btn-sm">
                                        <i class="fas fa-search me-1"></i> Filter
                                    </button>
                                    <a href="/transaction-history" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times me-1"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transaction Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6>History Transaksi</h6>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Kode Transaksi</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Kasir</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Items</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Total</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Metode Bayar</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $transaction)
                                    <tr>
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center">
                                                    <h6 class="mb-0 text-sm">{{ $transaction->transaction_code }}</h6>
                                                    @if($transaction->notes)
                                                    <p class="text-xs text-secondary mb-0">{{ Str::limit($transaction->notes, 30) }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $transaction->transaction_date->format('d/m/Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $transaction->transaction_date->format('H:i:s') }}</p>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">
                                                {{ $transaction->cashier_name }}
                                            </p>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">{{ $transaction->transactionItems->sum('quantity') }} item</span>
                                        </td>
                                        <td class="align-middle text-center">
                                            <span class="text-secondary text-xs font-weight-bold">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="align-middle text-center text-sm">
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
                                        </td>
                                        <td class="align-middle text-center text-sm">
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
                                        </td>
                                        <td class="align-middle text-center">
                                            <a href="/transaction-history/{{ $transaction->id }}" class="btn btn-sm btn-outline-info" data-bs-toggle="tooltip" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(auth()->user()->isKasir() && $transaction->cashier_id == auth()->id() || auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                            <a href="{{ route('transaction-history.edit', $transaction->id) }}" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Edit Transaksi">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endif

                                            <a href="/kasir/receipt/{{ $transaction->id }}?copy=1" target="_blank" class="btn btn-sm btn-outline-primary" data-bs-toggle="tooltip" title="Cetak Ulang Struk (Copy)">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            
                                            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-transaction" data-transaction-id="{{ $transaction->id }}" data-transaction-code="{{ $transaction->transaction_code }}" data-bs-toggle="tooltip" title="Hapus Transaksi">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">Tidak ada transaksi ditemukan</p>
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

        <!-- Pagination -->
        @if($transactions->hasPages())
        <div class="row mt-4">
            <div class="col-12">
                <div class="d-flex justify-content-center">
                    {{ $transactions->withQueryString()->links() }}
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Delete individual transaction
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-transaction')) {
            e.preventDefault();
            const button = e.target.closest('.delete-transaction');
            const transactionId = button.getAttribute('data-transaction-id');
            const transactionCode = button.getAttribute('data-transaction-code');
            
            Swal.fire({
                title: 'Hapus Transaksi?',
                html: 'Apakah Anda yakin ingin menghapus transaksi <strong>"' + transactionCode + '"</strong>?<br><br>Tindakan ini akan menghapus:<br>• Transaksi<br>• Item transaksi<br>• Data terkait<br><br><span class="text-danger">Tindakan ini tidak dapat dibatalkan!</span>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then(function(result) {
                if (!result.isConfirmed) return;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                button.disabled = true;
                fetch('/transaction-history/' + transactionId, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(function(response) {
                    if (response.ok) {
                        button.closest('tr').remove();
                        Swal.fire({ title: 'Berhasil', text: 'Transaksi "' + transactionCode + '" berhasil dihapus!', icon: 'success', confirmButtonColor: '#3085d6' });
                        var tbody = document.querySelector('tbody');
                        if (tbody && tbody.children.length === 0) {
                            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-4"><i class="fas fa-receipt fa-3x text-muted mb-3"></i><p class="text-muted">Tidak ada transaksi ditemukan</p></td></tr>';
                        }
                    } else {
                        throw new Error('HTTP error! status: ' + response.status);
                    }
                })
                .catch(function(error) {
                    console.error('Error:', error);
                    Swal.fire({ title: 'Error', text: 'Terjadi kesalahan saat menghapus transaksi', icon: 'error', confirmButtonColor: '#3085d6' });
                    button.innerHTML = '<i class="fas fa-trash"></i>';
                    button.disabled = false;
                });
            });
        }
    });

    // Delete all transactions on current page
    document.addEventListener('DOMContentLoaded', function() {
        const deleteAllTransactionsBtn = document.getElementById('deleteAllTransactions');
        
        if (deleteAllTransactionsBtn) {
            deleteAllTransactionsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const transactionRows = document.querySelectorAll('tbody tr');
                const totalTransactions = transactionRows.length;
                
                if (totalTransactions === 0) {
                    Swal.fire({ title: 'Info', text: 'Tidak ada transaksi untuk dihapus', icon: 'info', confirmButtonColor: '#3085d6' });
                    return;
                }
                
                // First confirmation - SweetAlert
                Swal.fire({
                    title: 'Hapus Semua Transaksi?',
                    html: '<strong class="text-danger">PERINGATAN:</strong> Anda akan menghapus <strong>' + totalTransactions + ' transaksi</strong> dari halaman ini!<br><br>Ini akan menghapus:<br>• Semua transaksi<br>• Semua item transaksi<br>• Data terkait<br><br><span class="text-danger">Tindakan ini tidak dapat dibatalkan!</span>',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Lanjutkan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then(function(firstResult) {
                    if (!firstResult.isConfirmed) return;
                    var transactionCodes = [];
                    transactionRows.forEach(function(row) {
                        var codeElement = row.querySelector('h6.mb-0.text-sm');
                        if (codeElement) transactionCodes.push(codeElement.textContent.trim());
                    });
                    var codeList = transactionCodes.slice(0, 5).join('<br>');
                    var remainingCount = transactionCodes.length > 5 ? transactionCodes.length - 5 : 0;
                    var detailHtml = 'Transaksi yang akan dihapus:<br><br>' + codeList + (remainingCount > 0 ? '<br>... dan ' + remainingCount + ' transaksi lainnya' : '') + '<br><br>Lanjutkan?';
                    Swal.fire({
                        title: 'Konfirmasi Lagi',
                        html: detailHtml,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus Semua',
                        cancelButtonText: 'Batal'
                    }).then(function(secondResult) {
                        if (!secondResult.isConfirmed) return;
                        // Final: ketik HAPUS
                        Swal.fire({
                            title: 'Konfirmasi Akhir',
                            html: 'Ketik <strong>HAPUS</strong> (huruf besar) untuk mengkonfirmasi penghapusan semua transaksi:',
                            input: 'text',
                            inputPlaceholder: 'HAPUS',
                            inputValidator: function(value) {
                                if (value !== 'HAPUS') return 'Anda harus mengetik HAPUS untuk mengkonfirmasi.';
                                return null;
                            },
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#6c757d',
                            confirmButtonText: 'Hapus Semua',
                            cancelButtonText: 'Batal'
                        }).then(function(thirdResult) {
                            if (!thirdResult.isConfirmed) return;
                            deleteAllTransactionsBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menghapus...';
                            deleteAllTransactionsBtn.disabled = true;
                            var transactionIds = [];
                            transactionRows.forEach(function(row) {
                                var deleteBtn = row.querySelector('.delete-transaction');
                                if (deleteBtn) transactionIds.push(deleteBtn.getAttribute('data-transaction-id'));
                            });
                            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            fetch('/transaction-history/bulk-delete', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                                body: JSON.stringify({ transaction_ids: transactionIds })
                            })
                            .then(function(response) {
                                if (!response.ok) throw new Error('HTTP error! status: ' + response.status);
                                return response.json();
                            })
                            .then(function(data) {
                                if (data.success) {
                                    Swal.fire({ title: 'Berhasil', text: 'Berhasil menghapus ' + data.deleted_count + ' transaksi!', icon: 'success', confirmButtonColor: '#3085d6' }).then(function() { window.location.reload(); });
                                } else {
                                    Swal.fire({ title: 'Error', text: data.message || 'Gagal menghapus transaksi', icon: 'error', confirmButtonColor: '#3085d6' });
                                    resetDeleteAllTransactionsButton();
                                }
                            })
                            .catch(function(error) {
                                console.error('Error:', error);
                                Swal.fire({ title: 'Error', text: 'Terjadi kesalahan: ' + error.message, icon: 'error', confirmButtonColor: '#3085d6' });
                                resetDeleteAllTransactionsButton();
                            });
                        });
                    });
                });
            });
        }
    });
    
    function resetDeleteAllTransactionsButton() {
        const deleteAllTransactionsBtn = document.getElementById('deleteAllTransactions');
        if (deleteAllTransactionsBtn) {
            deleteAllTransactionsBtn.innerHTML = '<i class="fas fa-trash-alt me-1"></i> Delete All Transactions';
            deleteAllTransactionsBtn.disabled = false;
        }
    }

    // Fix cashier data function
    function fixCashierData() {
        var button = event.target;
        var originalText = button.innerHTML;
        Swal.fire({
            title: 'Perbaiki Data Kasir?',
            text: 'Tindakan ini akan mengupdate transaksi yang memiliki data kasir yang tidak valid.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Perbaiki',
            cancelButtonText: 'Batal'
        }).then(function(result) {
            if (!result.isConfirmed) return;
            button.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memperbaiki...';
            button.disabled = true;
            fetch('/transaction-history/fix-cashier-data', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({ title: 'Berhasil', text: 'Berhasil memperbaiki ' + data.fixed_count + ' transaksi!', icon: 'success', confirmButtonColor: '#3085d6' }).then(function() { window.location.reload(); });
                } else {
                    Swal.fire({ title: 'Error', text: data.error || 'Terjadi kesalahan', icon: 'error', confirmButtonColor: '#3085d6' });
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                Swal.fire({ title: 'Error', text: 'Terjadi kesalahan saat memperbaiki data kasir', icon: 'error', confirmButtonColor: '#3085d6' });
            })
            .finally(function() {
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    }
</script>
@endpush
