@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Pilih Barcode untuk Dicetak'])
    <div id="alert">
        @include('components.alert')
    </div>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Pilih jenis barcode yang akan dicetak</h6>
                    </div>
                    <div class="card-body">
                        <p class="text-sm text-muted mb-3">
                            Produk: <strong>{{ $item->name }}</strong>
                        </p>
                        @if($barcodes->isEmpty())
                            <p class="text-muted mb-0">Produk ini belum memiliki barcode. Generate barcode dulu dari menu Barcode.</p>
                            <a href="{{ route('barcodes.create') }}" class="btn btn-primary btn-sm mt-3">Ke Halaman Barcode</a>
                            <a href="{{ route('items.show', $item) }}" class="btn btn-secondary btn-sm mt-3">Kembali ke Item</a>
                        @else
                            <p class="text-sm text-muted mb-3">Pilih salah satu barcode di bawah, lalu klik tombol untuk membuka halaman cetak.</p>
                            <div class="list-group">
                                @foreach($barcodes as $bc)
                                    <div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="badge bg-info me-2">{{ $bc->barcode_type }}</span>
                                            <code class="text-sm">{{ $bc->barcode_value }}</code>
                                        </div>
                                        <a href="{{ route('barcodes.print', $bc) }}" target="_blank" class="btn btn-success btn-sm">
                                            <i class="fas fa-print me-1"></i> Cetak
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3">
                                <a href="{{ route('items.show', $item) }}" class="btn btn-secondary btn-sm">Kembali ke Item</a>
                                <a href="{{ route('barcodes.index') }}" class="btn btn-outline-secondary btn-sm">Daftar Barcode</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
