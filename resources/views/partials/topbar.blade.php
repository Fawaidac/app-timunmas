<header class="topbar">
    <div class="topbar-left">
        <button class="icon-button mobile-menu-button" type="button" data-sidebar-open aria-label="Buka menu">☰</button>
        <div>
            <h1>@yield('page_title', 'Dashboard Sales')</h1>
            <p>@yield('page_description', 'Pantau aktivitas dan pencapaian penjualan hari ini')</p>
        </div>
    </div>

    <div class="top-actions">
        <button class="icon-button" type="button" aria-label="Notifikasi">🔔</button>
        <button class="icon-button" type="button" aria-label="Pengaturan">⚙</button>
    </div>
</header>
