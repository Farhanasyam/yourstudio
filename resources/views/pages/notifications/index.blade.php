@extends('layouts.app', ['title' => 'Notifikasi'])

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-lg-flex">
                        <div>
                            <h5 class="mb-0">Notifikasi</h5>
                            <p class="text-sm mb-0">
                                Semua notifikasi untuk Anda
                            </p>
                        </div>
                        <div class="ms-auto my-auto mt-lg-0 mt-4">
                            <div class="ms-auto my-auto">
                                @if(auth()->user()->unreadNotifications->count() > 0)
                                <form action="{{ route('notifications.mark-all-read') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn bg-gradient-primary btn-sm mb-0">
                                        Tandai Semua Dibaca
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body px-0 pb-0">
                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-flush" id="notifications-table">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Notifikasi</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tanggal</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Status</th>
                                        <th class="text-secondary opacity-7"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                    <tr class="{{ $notification->read_at ? '' : 'table-active' }}">
                                        <td>
                                            <div class="d-flex px-2 py-1">
                                                <div class="d-flex flex-column justify-content-center ms-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="icon icon-xs icon-shape bg-gradient-{{ $notification->data['color'] ?? 'primary' }} shadow text-center border-radius-sm me-2">
                                                            <i class="{{ $notification->data['icon'] ?? 'fa fa-bell' }} opacity-10 text-white"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-0 text-sm font-weight-bold">{{ $notification->data['title'] ?? 'Notifikasi' }}</h6>
                                                            <p class="text-xs text-secondary mb-0">{{ $notification->data['message'] ?? '' }}</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <p class="text-xs font-weight-bold mb-0">{{ $notification->created_at->format('d M Y') }}</p>
                                            <p class="text-xs text-secondary mb-0">{{ $notification->created_at->format('H:i') }}</p>
                                        </td>
                                        <td class="align-middle text-center text-sm">
                                            @if($notification->read_at)
                                                <span class="badge badge-sm bg-gradient-success">Dibaca</span>
                                            @else
                                                <span class="badge badge-sm bg-gradient-warning">Belum Dibaca</span>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            @if(isset($notification->data['action_url']))
                                                <a href="{{ route('notifications.mark-read', $notification->id) }}" 
                                                   class="btn btn-link text-dark px-3 mb-0">
                                                    <i class="fas fa-external-link-alt text-dark me-2" aria-hidden="true"></i>Lihat
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center">
                            {{ $notifications->links() }}
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fa fa-bell-slash fa-3x text-secondary mb-3"></i>
                            <h5 class="text-secondary">Tidak ada notifikasi</h5>
                            <p class="text-sm text-secondary">Semua notifikasi akan ditampilkan di sini</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
