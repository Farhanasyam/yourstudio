@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'Edit User'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>Edit User: {{ $user->name }}</h6>
                            <a href="{{ route('user-management.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('user-management.update', $user) }}">
                            @csrf
                            @method('PUT')
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name" class="form-control-label">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input class="form-control @error('name') is-invalid @enderror" 
                                               type="text" 
                                               name="name" 
                                               id="name" 
                                               placeholder="Masukkan nama lengkap"
                                               value="{{ old('name', $user->name) }}" 
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
                                               value="{{ old('email', $user->email) }}" 
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
                                        <label for="password" class="form-control-label">Password Baru</label>
                                        <input class="form-control @error('password') is-invalid @enderror" 
                                               type="password" 
                                               name="password" 
                                               id="password" 
                                               placeholder="Kosongkan jika tidak ingin mengubah password">
                                        <small class="text-muted">Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.</small>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password_confirmation" class="form-control-label">Konfirmasi Password Baru</label>
                                        <input class="form-control" 
                                               type="password" 
                                               name="password_confirmation" 
                                               id="password_confirmation" 
                                               placeholder="Ulangi password baru">
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
                                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="kasir" {{ old('role', $user->role) == 'kasir' ? 'selected' : '' }}>Kasir</option>
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
                                            <option value="pending" {{ old('approval_status', $user->approval_status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ old('approval_status', $user->approval_status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ old('approval_status', $user->approval_status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Aktif
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="horizontal dark">

                            <!-- User Information -->
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-sm mb-3">Informasi User</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <p class="text-sm mb-1"><strong>Dibuat:</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                                            <p class="text-sm mb-1"><strong>Terakhir Update:</strong> {{ $user->updated_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            @if($user->approvedBy)
                                                <p class="text-sm mb-1"><strong>Disetujui oleh:</strong> {{ $user->approvedBy->name }}</p>
                                                <p class="text-sm mb-1"><strong>Tanggal Persetujuan:</strong> {{ $user->approved_at->format('d/m/Y H:i') }}</p>
                                            @else
                                                <p class="text-sm mb-1 text-muted"><em>Belum ada persetujuan</em></p>
                                            @endif
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
                                                <li>Mengubah status approval dari/ke "Approved" akan mempengaruhi kemampuan user untuk login</li>
                                                <li>Status aktif dapat diubah sewaktu-waktu untuk mengontrol akses user</li>
                                                <li>Password hanya akan diubah jika field password diisi</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end">
                                <a href="{{ route('user-management.index') }}" class="btn btn-secondary me-2">Batal</a>
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Update User
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
