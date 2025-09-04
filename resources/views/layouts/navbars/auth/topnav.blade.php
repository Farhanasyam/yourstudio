<!-- Navbar -->
<nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl
        {{ str_contains(Request::url(), 'virtual-reality') == true ? ' mt-3 mx-3 bg-primary' : '' }}" id="navbarBlur"
        data-scroll="false">
    <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
                <li class="breadcrumb-item text-sm"><a class="opacity-5 text-white" href="{{ route('dashboard') }}">Pages</a></li>
                <li class="breadcrumb-item text-sm text-white active" aria-current="page">{{ $title ?? 'Dashboard' }}</li>
            </ol>
            <h6 class="font-weight-bolder text-white mb-0">{{ $title ?? 'Dashboard' }}</h6>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
            <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                <div class="input-group">
                    <span class="input-group-text text-body"><i class="fas fa-search" aria-hidden="true"></i></span>
                    <input type="text" class="form-control" placeholder="Search...">
                </div>
            </div>
            <ul class="navbar-nav justify-content-end">
                <li class="nav-item d-flex align-items-center me-3">
                    <a href="{{ route('profile') }}" class="nav-link text-white font-weight-bold px-0">
                        <i class="fas fa-user me-sm-1"></i>
                        <span class="d-sm-inline d-none">{{ auth()->user()->name }}</span>
                    </a>
                </li>
                <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
                    <a href="javascript:;" class="nav-link text-white p-0" id="iconNavbarSidenav">
                        <div class="sidenav-toggler-inner">
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                            <i class="sidenav-toggler-line bg-white"></i>
                        </div>
                    </a>
                </li>

                <li class="nav-item dropdown pe-2 d-flex align-items-center mx-3">
                    <a href="javascript:;" class="nav-link text-white p-0 position-relative" id="dropdownMenuButton"
                        data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell cursor-pointer"></i>
                        <span class="badge badge-sm bg-gradient-danger position-absolute top-0 start-100 translate-middle rounded-pill" 
                              id="notification-count" style="display: none;">0</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end px-2 py-3 me-sm-n4" 
                        aria-labelledby="dropdownMenuButton" id="notifications-dropdown">
                        <li class="mb-2">
                            <div class="d-flex justify-content-between align-items-center px-3">
                                <h6 class="text-sm font-weight-bold mb-0">Notifikasi</h6>
                                <button class="btn btn-link text-sm p-0" id="mark-all-read" style="display: none;">
                                    Tandai Semua Dibaca
                                </button>
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <div id="notifications-list">
                            <li class="text-center py-3">
                                <span class="text-sm text-secondary">Tidak ada notifikasi</span>
                            </li>
                        </div>
                        <li><hr class="dropdown-divider"></li>
                        <li class="text-center">
                            <a class="dropdown-item text-sm" href="{{ route('notifications.index') }}">
                                Lihat Semua Notifikasi
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="nav-item d-flex align-items-center ms-3">
                    <form role="form" method="post" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <a href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="nav-link text-white font-weight-bold px-0">
                            <i class="fas fa-sign-out-alt me-sm-1"></i>
                            <span class="d-sm-inline d-none">Logout</span>
                        </a>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load notifications on page load
    loadNotifications();
    
    // Reload notifications every 30 seconds
    setInterval(loadNotifications, 30000);
    
    // Mark all as read functionality
    document.getElementById('mark-all-read').addEventListener('click', function() {
        fetch('{{ route("notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        });
    });
    
    function loadNotifications() {
        // Get unread count
        fetch('{{ route("api.notifications.unread-count") }}')
        .then(response => response.json())
        .then(data => {
            const countBadge = document.getElementById('notification-count');
            const markAllBtn = document.getElementById('mark-all-read');
            
            if (data.count > 0) {
                countBadge.textContent = data.count;
                countBadge.style.display = 'block';
                markAllBtn.style.display = 'block';
            } else {
                countBadge.style.display = 'none';
                markAllBtn.style.display = 'none';
            }
        });
        
        // Get notifications
        fetch('{{ route("api.notifications") }}')
        .then(response => response.json())
        .then(data => {
            const notificationsList = document.getElementById('notifications-list');
            
            if (data.notifications.length === 0) {
                notificationsList.innerHTML = `
                    <li class="text-center py-3">
                        <span class="text-sm text-secondary">Tidak ada notifikasi</span>
                    </li>
                `;
            } else {
                notificationsList.innerHTML = data.notifications.map(notification => {
                    const notificationData = notification.data;
                    const timeAgo = getTimeAgo(notification.created_at);
                    
                    return `
                        <li class="mb-2">
                            <a class="dropdown-item border-radius-md notification-item" 
                               href="javascript:;" 
                               data-notification-id="${notification.id}"
                               data-action-url="${notificationData.action_url || ''}">
                                <div class="d-flex py-1">
                                    <div class="my-auto">
                                        <div class="avatar avatar-sm bg-gradient-${notificationData.color || 'primary'} me-3">
                                            <i class="${notificationData.icon || 'fa fa-bell'} text-white"></i>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column justify-content-center">
                                        <h6 class="text-sm font-weight-normal mb-1">
                                            <span class="font-weight-bold">${notificationData.title}</span>
                                        </h6>
                                        <p class="text-xs text-secondary mb-1">${notificationData.message}</p>
                                        <p class="text-xs text-secondary mb-0">
                                            <i class="fa fa-clock me-1"></i>
                                            ${timeAgo}
                                        </p>
                                    </div>
                                </div>
                            </a>
                        </li>
                    `;
                }).join('');
                
                // Add click handlers to notification items
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const notificationId = this.getAttribute('data-notification-id');
                        const actionUrl = this.getAttribute('data-action-url');
                        
                        // Mark as read
                        fetch(`/notifications/${notificationId}/mark-read-ajax`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && actionUrl) {
                                window.location.href = actionUrl;
                            }
                        });
                    });
                });
            }
        });
    }
    
    function getTimeAgo(dateString) {
        const now = new Date();
        const date = new Date(dateString);
        const diffInSeconds = Math.floor((now - date) / 1000);
        
        if (diffInSeconds < 60) {
            return 'Baru saja';
        } else if (diffInSeconds < 3600) {
            const minutes = Math.floor(diffInSeconds / 60);
            return `${minutes} menit yang lalu`;
        } else if (diffInSeconds < 86400) {
            const hours = Math.floor(diffInSeconds / 3600);
            return `${hours} jam yang lalu`;
        } else {
            const days = Math.floor(diffInSeconds / 86400);
            return `${days} hari yang lalu`;
        }
    }
});
</script>
<!-- End Navbar -->
