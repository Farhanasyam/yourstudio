@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Tambah User'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Tambah User Baru</h6>
                            <a href="{{ route('user-management.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('user-management.store') }}">
                            @csrf
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input class="form-control @error('name') is-invalid @enderror" 
                                               type="text" 
                                               name="name" 
                                               id="name" 
                                               placeholder="Masukkan nama lengkap"
                                               value="{{ old('name') }}" 
                                               required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="email" class="form-control-label">Email <span class="text-danger">*</span></label>
                                        <input class="form-control @error('email') is-invalid @enderror" 
                                               type="email" 
                                               name="email" 
                                               id="email" 
                                               placeholder="contoh@email.com"
                                               value="{{ old('email') }}" 
                                               required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password" class="form-control-label">Password <span class="text-danger">*</span></label>
                                        <input class="form-control @error('password') is-invalid @enderror" 
                                               type="password" 
                                               name="password" 
                                               id="password" 
                                               placeholder="Minimal 8 karakter"
                                               required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="form-control-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                        <input class="form-control" 
                                               type="password" 
                                               name="password_confirmation" 
                                               id="password_confirmation" 
                                               placeholder="Ulangi password"
                                               required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="role" class="form-control-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-select @error('role') is-invalid @enderror" 
                                                name="role" 
                                                id="role" 
                                                required>
                                            <option value="">Pilih Role</option>
                                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="kasir" {{ old('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="approval_status" class="form-control-label">Status Approval <span class="text-danger">*</span></label>
                                        <select class="form-select @error('approval_status') is-invalid @enderror" 
                                                name="approval_status" 
                                                id="approval_status" 
                                                required>
                                            <option value="">Pilih Status</option>
                                            <option value="pending" {{ old('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('approval_status', 'approved') == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ old('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                        @error('approval_status')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="is_active" class="form-control-label">Status Aktif</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   name="is_active" 
                                                   id="is_active" 
                                                   value="1"
                                                   {{ old('is_active', '1') ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-group">
                                        <div class="alert alert-info" role="alert">
                                            <i class="fas fa-info-circle me-2"></i>
                                            <strong>Informasi:</strong>
                                            <ul class="mb-0 mt-2">
                                                <li>Jika status approval diset "Approved", user dapat langsung login ke sistem</li>
                                                <li>Jika status approval "Pending", user perlu menunggu persetujuan dari Super Admin</li>
                                                <li>Jika status approval "Rejected", user tidak dapat login ke sistem</li>
                                                <li>Status aktif dapat diubah sewaktu-waktu untuk mengontrol akses user</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('user-management.index') }}" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Simpan User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection
