<aside class="sidenav bg-white navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-2 fixed-start ms-4"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
            aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="{{ route('dashboard') }}">
            <img src="/img/yourstudio.png" class="navbar-brand-img h-100" alt="YourStudio Logo">
            <span class="ms-1 font-weight-bold">YourStudio</span>
        </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto h-auto" id="sidenav-collapse-main">
        <ul class="navbar-nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tv-2 text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            
            @if(auth()->user()->canManageItems())
            <li class="nav-item mt-2 d-flex align-items-center">
                <div class="ps-4">
                    <i class="fas fa-boxes" style="color: #f4645f;"></i>
                </div>
                <h6 class="ms-2 text-uppercase text-xs font-weight-bolder opacity-6 mb-0">Inventory Management</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'items.index' ? 'active' : '' }}" href="{{ route('items.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-box-2 text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Items</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'categories.index' ? 'active' : '' }}" href="{{ route('categories.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-tag text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Categories</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'suppliers.index' ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-delivery-fast text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Suppliers</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'stock-in.index' ? 'active' : '' }}" href="{{ route('stock-in.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-cart text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Stock In</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'stock-adjustments.index' ? 'active' : '' }}" href="{{ route('stock-adjustments.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-chart-bar-32 text-danger text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Stock Adjustment</span>
                </a>
            </li>
            @endif
            
            <li class="nav-item mt-2 d-flex align-items-center">
                <div class="ps-4">
                    <i class="fas fa-cash-register" style="color: #f4645f;"></i>
                </div>
                <h6 class="ms-2 text-uppercase text-xs font-weight-bolder opacity-6 mb-0">Sales Management</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'kasir.index' ? 'active' : '' }}" href="{{ route('kasir.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-money-coins text-primary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Cashier</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ request()->is('transaction-history*') ? 'active' : '' }}" href="/transaction-history">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-bullet-list-67 text-info text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Transaction History</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'sales.index' ? 'active' : '' }}" href="{{ route('sales.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-money-coins text-success text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Sales</span>
                </a>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'barcodes.index' ? 'active' : '' }}" href="{{ route('barcodes.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-camera-compact text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Barcode</span>
                </a>
            </li>
            
            @if(auth()->user()->canViewReports())
            <li class="nav-item mt-2 d-flex align-items-center">
                <div class="ps-4">
                    <i class="fas fa-chart-line" style="color: #f4645f;"></i>
                </div>
                <h6 class="ms-2 text-uppercase text-xs font-weight-bolder opacity-6 mb-0">Reports</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'reports.index' ? 'active' : '' }}" href="{{ route('reports.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-chart-pie-35 text-warning text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Reports</span>
                </a>
            </li>
            @endif
            
            <li class="nav-item mt-2 d-flex align-items-center">
                <div class="ps-4">
                    <i class="fas fa-cog" style="color: #f4645f;"></i>
                </div>
                <h6 class="ms-2 text-uppercase text-xs font-weight-bolder opacity-6 mb-0">Settings</h6>
            </li>
            
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'profile' ? 'active' : '' }}" href="{{ route('profile') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">Profile Settings</span>
                </a>
            </li>
            
            @if(auth()->user()->isSuperAdmin())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('user-management.*') ? 'active' : '' }}" href="{{ route('user-management.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-bullet-list-67 text-dark text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">User Management</span>
                </a>
            </li>
            @endif
            
            @if(auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
            <li class="nav-item">
                <a class="nav-link {{ Route::currentRouteName() == 'settings.index' ? 'active' : '' }}" href="{{ route('settings.index') }}">
                    <div class="icon icon-shape icon-sm border-radius-md text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="ni ni-settings-gear-65 text-secondary text-sm opacity-10"></i>
                    </div>
                    <span class="nav-link-text ms-1">System Settings</span>
                </a>
            </li>
            @endif
            

        </ul>
    </div>
</aside>
