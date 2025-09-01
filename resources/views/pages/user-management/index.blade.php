@extends('layouts.app', ['class' => 'g-sidenav-show bg-gray-100'])

@section('content')
    @include('layouts.navbars.auth.topnav', ['title' => 'User Management'])
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header pb-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6>User Management</h6>
                            <a href="{{ route('user-management.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Tambah User
                            </a>
                        </div>
                    </div>
                    <div class="card-body px-0 pt-0 pb-2">
                        <!-- Filter Section -->
                        <div class="row mx-3 mb-3">
                            <div class="col-12">
                                <form method="GET" class="row g-3">
                                    <div class="col-md-3">
                                        <input type="text" name="search" class="form-control" placeholder="Cari nama atau email..." value="{{ request('search') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <select name="role" class="form-select">
                                            <option value="">Semua Role</option>
                                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                            <option value="kasir" {{ request('role') == 'kasir' ? 'selected' : '' }}>Kasir</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="approval_status" class="form-select">
                                            <option value="">Semua Status</option>
                                            <option value="pending" {{ request('approval_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                                            <option value="approved" {{ request('approval_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                                            <option value="rejected" {{ request('approval_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <select name="is_active" class="form-select">
                                            <option value="">Semua Status Aktif</option>
                                            <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Aktif</option>
                                            <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Nonaktif</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-info btn-sm me-2">Filter</button>
                                        <a href="{{ route('user-management.index') }}" class="btn btn-secondary btn-sm">Reset</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive p-0">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">User</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Role</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Approval</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Status Aktif</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Approved By</th>
                                        <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Dibuat</th>
                                        <th class="text-secondary opacity-7">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $user)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <img src="{{ asset('img/team-2.jpg') }}" class="avatar avatar-sm me-3" alt="user">
                                                    </div>
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">{{ $user->name }}</h6>
                                                        <p class="text-xs text-secondary mb-0">{{ $user->email }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0">
                                                    <span class="badge badge-sm 
                                                        {{ $user->role == 'admin' ? 'bg-gradient-info' : 'bg-gradient-warning' }}">
                                                        {{ ucfirst($user->role) }}
                                                    </span>
                                                </p>
                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                @if($user->approval_status == 'pending')
                                                    <span class="badge badge-sm bg-gradient-warning">Pending</span>
                                                @elseif($user->approval_status == 'approved')
                                                    <span class="badge badge-sm bg-gradient-success">Approved</span>
                                                @else
                                                    <span class="badge badge-sm bg-gradient-danger">Rejected</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="badge badge-sm {{ $user->is_active ? 'bg-gradient-success' : 'bg-gradient-secondary' }}">
                                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                @if($user->approvedBy)
                                                    <span class="text-xs">{{ $user->approvedBy->name }}</span>
                                                    <br><small class="text-xs text-secondary">{{ $user->approved_at?->format('d/m/Y H:i') }}</small>
                                                @else
                                                    <span class="text-xs text-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="align-middle text-center">
                                                <span class="text-xs">{{ $user->created_at->format('d/m/Y') }}</span>
                                            </td>
                                            <td class="align-middle">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                        Aksi
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li><a class="dropdown-item" href="{{ route('user-management.show', $user) }}">
                                                            <i class="fas fa-eye"></i> Lihat</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('user-management.edit', $user) }}">
                                                            <i class="fas fa-edit"></i> Edit</a></li>
                                                        
                                                        @if($user->approval_status == 'pending')
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('user-management.approve', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item text-success" 
                                                                            onclick="return confirm('Setujui user ini?')">
                                                                        <i class="fas fa-check"></i> Setujui
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('user-management.reject', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item text-warning" 
                                                                            onclick="return confirm('Tolak user ini?')">
                                                                        <i class="fas fa-times"></i> Tolak
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif

                                                        @if($user->id != auth()->id())
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <form action="{{ route('user-management.toggle-status', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    <button type="submit" class="dropdown-item {{ $user->is_active ? 'text-warning' : 'text-info' }}" 
                                                                            onclick="return confirm('{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }} user ini?')">
                                                                        <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i> 
                                                                        {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                                    </button>
                                                                </form>
                                                            </li>
                                                            <li>
                                                                <form action="{{ route('user-management.destroy', $user) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="dropdown-item text-danger" 
                                                                            onclick="return confirm('Hapus user ini?')">
                                                                        <i class="fas fa-trash"></i> Hapus
                                                                    </button>
                                                                </form>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4">
                                                <div class="text-center">
                                                    <i class="fas fa-users text-muted" style="font-size: 3rem;"></i>
                                                    <p class="text-muted mt-2">Tidak ada user yang ditemukan</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        
                        @if($users->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @include('layouts.footers.auth.footer')
    </div>
@endsection

@push('js')
<script>
    // Auto submit form when filter changes
    document.querySelectorAll('select[name]').forEach(function(select) {
        select.addEventListener('change', function() {
            this.form.submit();
        });
    });
</script>
@endpush
