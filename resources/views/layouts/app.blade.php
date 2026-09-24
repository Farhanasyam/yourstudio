<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="/img/yourstudio.png">
    <link rel="icon" type="image/png" href="/img/yourstudio.png">
    <title>
        YourStudio
    </title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
    <meta name="user-id" content="{{ auth()->id() }}">
    @endauth
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <!-- Nucleo Icons -->
    <link href="/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" integrity="sha512-DTOQO9RWCH3ppGqcWaEA1BIZOC6xxalwEsw9c2QQeAIftl+Vegovlnee1c9QX4TctnWMn13TZye+giMm8e2LwA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <!-- CSS Files -->
    <link id="pagestyle" href="/assets/css/argon-dashboard.css" rel="stylesheet" />
    <link href="/assets/css/responsive-tables.css" rel="stylesheet" />
    <style>
        /* Sidebar: always use native scrolling so every menu stays reachable on small/touch screens.
           PerfectScrollbar (enabled on Windows) forces overflow:hidden via .ps, which cuts off the menu. */
        #sidenav-main,
        #sidenav-main.ps {
            overflow-y: auto !important;
            overflow-x: hidden !important;
            -webkit-overflow-scrolling: touch;
            overscroll-behavior: contain;
            max-height: calc(100vh - 1rem);
        }
        #sidenav-main .navbar-collapse,
        #sidenav-main .navbar-collapse.ps {
            height: auto !important;
            overflow: visible !important;
            padding-bottom: 1rem;
        }
        #sidenav-main > .ps__rail-x,
        #sidenav-main > .ps__rail-y,
        #sidenav-main .navbar-collapse > .ps__rail-x,
        #sidenav-main .navbar-collapse > .ps__rail-y {
            display: none !important;
        }
        @media (max-width: 1199.98px) {
            /* Close button inside the sidebar on mobile */
            .g-sidenav-pinned #iconSidenav {
                display: block !important;
            }
        }
    </style>
</head>

<body class="{{ $class ?? '' }}">

    @guest
        @yield('content')
    @endguest

    @auth
        @if (in_array(request()->route()->getName(), ['login', 'register']))
            @yield('content')
        @else
            @if (!in_array(request()->route()->getName(), ['profile']))
                <div class="min-height-300 bg-primary position-absolute w-100"></div>
            @elseif (in_array(request()->route()->getName(), ['profile']))
                <div class="position-absolute w-100 min-height-300 top-0" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/profile-layout-header.jpg'); background-position-y: 50%;">
                    <span class="mask bg-primary opacity-6"></span>
                </div>
            @endif
            @include('layouts.navbars.auth.sidenav')
                <main class="main-content border-radius-lg">
                    @yield('content')
                </main>
            
            {{-- Include Global CRUD Modals --}}
            @include('components.modals.crud-modals')
        @endif
    @endauth

    <!--   Core JS Files   -->
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/plugins/perfect-scrollbar.min.js"></script>
    <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = {
                damping: '0.5'
            }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>
    <!-- Github buttons -->
    <script async defer src="https://buttons.github.io/buttons.js"></script>
    <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="/assets/js/argon-dashboard.js"></script>
    <script>
        // argon-dashboard.js closes the mobile sidebar on any click whose target isn't one of the
        // hamburger's line elements, so tapping the gap between the lines (or empty space inside
        // the sidebar) opened and immediately closed it. Keep those clicks from reaching <html>.
        ['iconNavbarSidenav', 'sidenav-main'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('click', function (e) { e.stopPropagation(); });
        });
    </script>

    <!-- Prevent Back Button After Login -->
    @auth
    <script>
        // Prevent back button after login
        window.history.pushState(null, null, window.location.href);
        window.onpopstate = function () {
            window.history.pushState(null, null, window.location.href);
        };
        
        // Prevent form resubmission on refresh
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
    </script>
    @endauth
    
    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Sweet Alert Delete & Helpers -->
    <script src="/assets/js/sweet-alert-delete.js"></script>
    <script src="/assets/js/swal-helpers.js"></script>
    <script src="/assets/js/responsive-tables.js"></script>
    @auth
    {{-- Offline cashier queue: syncs pending sales from any page --}}
    <script src="/assets/js/kasir-offline.js"></script>
    @endauth

    @stack('scripts')
    {{-- Several pages (sales charts, report form, transaction edit, user filters) push to "js" --}}
    @stack('js')
</body>

</html>
