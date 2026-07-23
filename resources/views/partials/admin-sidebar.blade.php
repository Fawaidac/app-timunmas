<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-mark">A</div>
        <div>
            <strong>ASRI</strong>
            <small>Admin Dashboard</small>
        </div>
    </div>

    <nav class="nav-menu">
        <div class="nav-label">Dashboard</div>
        <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="nav-icon ri-dashboard-3-line"></i><span>Overview</span>
        </a>

        <div class="nav-label">Sales Management</div>
        <a href="{{ route('admin.orders') }}" class="{{ request()->routeIs('admin.orders') ? 'active' : '' }}">
            <i class="nav-icon ri-shopping-cart-2-line"></i><span>All Orders</span>
        </a>
        <a href="{{ route('admin.visits') }}" class="{{ request()->routeIs('admin.visits') ? 'active' : '' }}">
            <i class="nav-icon ri-map-pin-2-line"></i><span>Sales Activity</span>
        </a>

        <div class="nav-label">Financial</div>
        <a href="{{ route('admin.invoices') }}" class="{{ request()->routeIs('admin.invoices') ? 'active' : '' }}">
            <i class="nav-icon ri-file-paper-2-line"></i><span>Tagihan</span>
        </a>
        <a href="{{ route('admin.payments') }}" class="{{ request()->routeIs('admin.payments') ? 'active' : '' }}">
            <i class="nav-icon ri-wallet-3-line"></i><span>Pembayaran</span>
        </a>

        <div class="nav-label">Master Data</div>
        <a href="{{ route('admin.products') }}" class="{{ request()->routeIs('admin.products') ? 'active' : '' }}">
            <i class="nav-icon ri-box-3-line"></i><span>Products</span>
        </a>
        <a href="{{ route('admin.warehouses') }}" class="{{ request()->routeIs('admin.warehouses') ? 'active' : '' }}">
            <i class="nav-icon ri-building-line"></i><span>Warehouses</span>
        </a>
        <a href="{{ route('admin.customers') }}" class="{{ request()->routeIs('admin.customers') ? 'active' : '' }}">
            <i class="nav-icon ri-team-line"></i><span>Customers</span>
        </a>
        <a href="{{ route('admin.users') }}" class="{{ request()->routeIs('admin.users') ? 'active' : '' }}">
            <i class="nav-icon ri-user-settings-line"></i><span>Users</span>
        </a>

        <div class="nav-label">Reports</div>
        <a href="{{ route('admin.reports') }}" class="{{ request()->routeIs('admin.reports') ? 'active' : '' }}">
            <i class="nav-icon ri-bar-chart-2-line"></i><span>Analytics</span>
        </a>
    </nav>

    <div class="sidebar-profile">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="user-details">
                <b>{{ auth()->user()->name }}</b>
                <span>{{ ucfirst(auth()->user()->role ?? 'Administrator') }}</span>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="logout-form">
            @csrf
            <button type="submit" class="button-logout">
                <i class="nav-icon ri-logout-box-r-line"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
