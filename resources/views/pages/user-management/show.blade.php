@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Detail User'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Detail User: {{ $user->name }}</h6>
                            <div>
                                <a href="{{ route('user-management.edit', $user) }}" class="btn btn-info btn-sm me-2">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('user-management.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- User Profile Section -->
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body text-center">
                                        <img src="{{ asset('img/team-2.jpg') }}" 
                                             class="avatar avatar-xl" 
                                             alt="User Avatar">
                                        <h5 class="mt-3 mb-1">{{ $user->name }}</h5>
                                        <p class="text-muted">{{ $user->email }}</p>
                                        
                                        <div class="d-flex justify-content-center gap-2 mt-3">
                                            <span class="badge badge-lg 
                                                {{ $user->role == 'admin' ? 'bg-gradient-info' : 'bg-gradient-warning' }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </div>
                                        
                                        <div class="d-flex justify-content-center gap-2 mt-2">
                                            <span class="badge badge-lg 
                                                {{ $user->approval_status == 'approved' ? 'bg-gradient-success' : 
                                                   ($user->approval_status == 'pending' ? 'bg-gradient-warning' : 'bg-gradient-danger') }}">
                                                {{ ucfirst($user->approval_status) }}
                                            </span>
                                            
                                            <span class="badge badge-lg {{ $user->is_active ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick Actions -->
                                @if($user->id != auth()->id())
                                <div class="card mt-3">
                                    <div class="card-header pb-0">
                                        <h6>Aksi Cepat</h6>
                                    </div>
                                    <div class="card-body">
                                        @if($user->approval_status == 'pending')
                                            <form action="{{ route('user-management.approve', $user) }}" method="POST" class="mb-2">
                                                @csrf
                                                <button type="submit" class="btn btn-success btn-sm w-100" 
                                                        onclick="return confirm('Setujui user ini?')">
                                                    <i class="fas fa-check"></i> Setujui User
                                                </button>
                                            </form>
                                            <form action="{{ route('user-management.reject', $user) }}" method="POST" class="mb-2">
                                                @csrf
                                                <button type="submit" class="btn btn-warning btn-sm w-100" 
                                                        onclick="return confirm('Tolak user ini?')">
                                                    <i class="fas fa-times"></i> Tolak User
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('user-management.toggle-status', $user) }}" method="POST" class="mb-2">
                                            @csrf
                                            <button type="submit" class="btn {{ $user->is_active ? 'btn-warning' : 'btn-info' }} btn-sm w-100" 
                                                    onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')">
                                                <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i> 
                                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                @endif
                            </div>
                            
                            <!-- User Information Section -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header pb-0">
                                        <h6>Informasi Detail</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Nama Lengkap</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ $user->name }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Email</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ $user->email }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Role</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ ucfirst($user->role) }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Status Approval</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ ucfirst($user->approval_status) }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Status Aktif</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Dapat Login</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">
                                                    {{ $user->canLogin() ? 'Ya' : 'Tidak' }}
                                                    @if(!$user->canLogin())
                                                        <small class="text-danger">
                                                            ({{ !$user->is_active ? 'Akun nonaktif' : 'Belum disetujui' }})
                                                        </small>
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Tanggal Bergabung</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ $user->created_at->format('d F Y, H:i') }}</p>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-sm-3">
                                                <p class="mb-0 text-sm font-weight-bold">Terakhir Update</p>
                                            </div>
                                            <div class="col-sm-9">
                                                <p class="text-muted mb-0">{{ $user->updated_at->format('d F Y, H:i') }}</p>
                                            </div>
                                        </div>
                                        
                                        @if($user->approvedBy)
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <p class="mb-0 text-sm font-weight-bold">Disetujui Oleh</p>
                                                </div>
                                                <div class="col-sm-9">
                                                    <p class="text-muted mb-0">{{ $user->approvedBy->name }}</p>
                                                </div>
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-sm-3">
                                                    <p class="mb-0 text-sm font-weight-bold">Tanggal Persetujuan</p>
                                                </div>
                                                <div class="col-sm-9">
                                                    <p class="text-muted mb-0">{{ $user->approved_at->format('d F Y, H:i') }}</p>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Activity Summary -->
                                <div class="card mt-3">
                                    <div class="card-header pb-0">
                                        <h6>Ringkasan Aktivitas</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 col-6">
                                                <div class="text-center">
                                                    <i class="ni ni-cart text-primary text-lg"></i>
                                                    <h4 class="font-weight-bolder">{{ $user->stockIns->count() }}</h4>
                                                    <p class="text-sm">Stock In</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="text-center">
                                                    <i class="ni ni-money-coins text-success text-lg"></i>
                                                    <h4 class="font-weight-bolder">{{ $user->sales->count() }}</h4>
                                                    <p class="text-sm">Penjualan</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="text-center">
                                                    <i class="ni ni-chart-bar-32 text-warning text-lg"></i>
                                                    <h4 class="font-weight-bolder">{{ $user->stockAdjustments->count() }}</h4>
                                                    <p class="text-sm">Penyesuaian</p>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-6">
                                                <div class="text-center">
                                                    <i class="fas fa-qrcode text-info text-lg"></i>
                                                    <h4 class="font-weight-bolder">{{ $user->createdBarcodes->count() }}</h4>
                                                    <p class="text-sm">Barcode</p>
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
        @include('layouts.footers.auth.footer')
    </div>
@endsection
