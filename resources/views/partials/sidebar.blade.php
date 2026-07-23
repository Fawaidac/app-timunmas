<aside class="sidebar" id="sidebar">
    <div class="brand">
        <div class="brand-mark">S</div>
        <div>
            <strong>ASRI</strong>
            <small>Sales Force Automation</small>
        </div>
    </div>

    <nav class="nav-menu">
        <div class="nav-label">Utama</div>
        <a href="{{ route('sales.dashboard') }}" class="{{ request()->routeIs('sales.dashboard') ? 'active' : '' }}">
            <i class="nav-icon ri-dashboard-3-line"></i><span>Dashboard</span>
        </a>
        <a href="{{ route('sales.kunjungan.index') }}" class="{{ request()->routeIs('sales.kunjungan.index') || request()->routeIs('sales.checkin') || request()->routeIs('sales.order') || request()->routeIs('sales.pembayaran') ? 'active' : '' }}">
            <i class="nav-icon ri-map-pin-2-line"></i><span>Kunjungan Sales</span>
        </a>

        <div class="nav-label">Transaksi</div>
        <a href="{{ route('sales.tagihan.index') }}" class="{{ request()->routeIs('sales.tagihan.index') ? 'active' : '' }}">
            <i class="nav-icon ri-file-paper-2-line"></i><span>Tagihan Sales</span>
        </a>

        <div class="nav-label">Informasi</div>
        <a href="{{ route('sales.stok.index') }}" class="{{ request()->routeIs('sales.stok.index') ? 'active' : '' }}">
            <i class="nav-icon ri-box-3-line"></i><span>Pencarian Stok</span>
        </a>
        <a href="{{ route('sales.laporan.index') }}" class="{{ request()->routeIs('sales.laporan.index') ? 'active' : '' }}">
            <i class="nav-icon ri-bar-chart-2-line"></i><span>Laporan Aktivitas</span>
        </a>
    </nav>
    
    <div class="sidebar-profile">
        <div class="user-info">
            <div class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
            <div class="user-details">
                <b>{{ auth()->user()->name }}</b>
                <span>{{ auth()->user()->area ?? 'Sales Team' }}</span>
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
