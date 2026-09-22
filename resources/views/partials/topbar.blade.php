<header class="topbar" style="position:relative; z-index:100;">
    <div class="topbar-left">
        <button class="icon-button mobile-menu-button" type="button" data-sidebar-open aria-label="Buka menu">☰</button>
        <div>
            <h1>@yield('page_title', 'Dashboard Sales')</h1>
            <p>@yield('page_description', 'Pantau aktivitas dan pencapaian penjualan hari ini')</p>
        </div>
    </div>

    <div class="top-actions" style="position:relative; display:flex; align-items:center; gap:8px;">
        <!-- Tombol & Dropdown Notifikasi -->
        <div style="position:relative;">
            <button class="icon-button" id="notifBtn" type="button" aria-label="Notifikasi" style="position:relative;">
                🔔
                <span id="notifBadge" class="badge-count" style="display:none; position:absolute; top:-4px; right:-4px; background:#ef4444; color:#fff; font-size:10px; font-weight:700; border-radius:10px; padding:2px 5px; min-width:16px; text-align:center; border:2px solid #fff;">0</span>
            </button>

            <!-- Dropdown Notifikasi Floating -->
            <div id="notifDropdown" style="display:none; position:absolute; right:0; top:45px; width:340px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); z-index:999; overflow:hidden;">
                <div style="padding:14px 16px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; background:#f8fafc;">
                    <span style="font-weight:700; font-size:14px; color:#1e293b;">🔔 Notifikasi</span>
                    <button type="button" onclick="markAllNotifRead()" style="background:none; border:none; color:#ea580c; font-size:12px; font-weight:600; cursor:pointer; padding:0;">Tandai Dibaca</button>
                </div>
                
                <div id="notifList" style="max-height:320px; overflow-y:auto; padding:0;">
                    <div style="padding:24px; text-align:center; color:#94a3b8; font-size:13px;">Mengambil notifikasi...</div>
                </div>

                <div style="padding:10px 16px; background:#f8fafc; border-top:1px solid #f1f5f9; text-align:center;">
                    <small style="color:#64748b; font-size:11px;">Otomatis dibersihkan jika file > 500 KB</small>
                </div>
            </div>
        </div>

        <!-- Tombol & Dropdown Pengaturan -->
        <div style="position:relative;">
            <button class="icon-button" id="settingsBtn" type="button" aria-label="Pengaturan">⚙</button>

            <!-- Dropdown Pengaturan Floating -->
            <div id="settingsDropdown" style="display:none; position:absolute; right:0; top:45px; width:260px; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; box-shadow:0 10px 25px -5px rgba(0,0,0,0.15), 0 8px 10px -6px rgba(0,0,0,0.1); z-index:999; overflow:hidden;">
                @php
                    $uName = \App\Helpers\SalesHelper::nama();
                    $uRole = auth()->guard('sales')->check() ? 'sales' : (auth()->user()->role ?? 'User');
                    $kdPeg = \App\Helpers\SalesHelper::kdPeg();
                @endphp
                <div style="padding:14px 16px; border-bottom:1px solid #f1f5f9; background:#f8fafc;">
                    <div style="font-weight:700; font-size:14px; color:#1e293b; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $uName }}</div>
                    <div style="font-size:12px; color:#64748b; margin-top:2px; display:flex; align-items:center; gap:6px;">
                        <span style="display:inline-block; padding:1px 6px; border-radius:4px; font-size:10px; font-weight:700; background:#ffedd5; color:#c2410c; text-transform:uppercase;">{{ $uRole }}</span>
                        @if($kdPeg)
                            <span>({{ $kdPeg }})</span>
                        @endif
                    </div>
                </div>

                <div style="padding:8px 0;">
                    <button type="button" onclick="toggleThemeMode()" style="width:100%; text-align:left; padding:10px 16px; background:none; border:none; font-size:13px; color:#334155; cursor:pointer; display:flex; align-items:center; justify-content:space-between;">
                        <span>🌗 Mode Tampilan</span>
                        <span id="themeModeLabel" style="font-size:11px; font-weight:600; color:#ea580c;">Terang</span>
                    </button>

                    <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('topbarLogoutForm').submit();" style="display:flex; align-items:center; gap:8px; padding:10px 16px; font-size:13px; color:#dc2626; text-decoration:none; font-weight:600; border-top:1px solid #f1f5f9; margin-top:4px;">
                        🚪 Keluar (Logout)
                    </a>
                    <form id="topbarLogoutForm" action="{{ route('logout') }}" method="POST" style="display:none;">@csrf</form>
                </div>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    const settingsBtn = document.getElementById('settingsBtn');
    const settingsDropdown = document.getElementById('settingsDropdown');

    // Toggle Notifikasi Dropdown
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = notifDropdown.style.display === 'block';
            notifDropdown.style.display = isOpen ? 'none' : 'block';
            if (settingsDropdown) settingsDropdown.style.display = 'none';
            if (!isOpen) fetchNotifications();
        });
    }

    // Toggle Pengaturan Dropdown
    if (settingsBtn && settingsDropdown) {
        settingsBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            const isOpen = settingsDropdown.style.display === 'block';
            settingsDropdown.style.display = isOpen ? 'none' : 'block';
            if (notifDropdown) notifDropdown.style.display = 'none';
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function() {
        if (notifDropdown) notifDropdown.style.display = 'none';
        if (settingsDropdown) settingsDropdown.style.display = 'none';
    });

    if (notifDropdown) {
        notifDropdown.addEventListener('click', function(e) { e.stopPropagation(); });
    }
    if (settingsDropdown) {
        settingsDropdown.addEventListener('click', function(e) { e.stopPropagation(); });
    }

    // Initial Notif Check
    fetchNotifications();
});

function fetchNotifications() {
    fetch('{{ route("notifications.index") }}', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) return;

        const badge = document.getElementById('notifBadge');
        const list = document.getElementById('notifList');

        if (badge) {
            if (data.unread_count > 0) {
                badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }

        if (list) {
            if (!data.notifications || data.notifications.length === 0) {
                list.innerHTML = '<div style="padding:28px 16px; text-align:center; color:#94a3b8; font-size:13px;">📭 Belum ada notifikasi baru</div>';
                return;
            }

            let html = '';
            data.notifications.forEach(item => {
                const bg = item.is_read ? '#ffffff' : '#fff7ed';
                html += `
                    <a href="${item.url || '#'}" onclick="onNotifClick(event, '${item.id}', '${item.url}')" style="display:flex; gap:12px; padding:12px 16px; border-bottom:1px solid #f1f5f9; background:${bg}; text-decoration:none; transition:background 0.15s ease;">
                        <span style="font-size:18px;">${item.icon || '🔔'}</span>
                        <div style="flex:1;">
                            <div style="font-size:12px; font-weight:700; color:#1e293b; margin-bottom:2px;">${item.title}</div>
                            <div style="font-size:12px; color:#475569; line-height:1.4;">${item.message}</div>
                            <div style="font-size:10px; color:#94a3b8; margin-top:4px;">${item.created_at || ''}</div>
                        </div>
                    </a>
                `;
            });
            list.innerHTML = html;
        }
    })
    .catch(err => console.error('Fetch notif error:', err));
}

function onNotifClick(e, id, url) {
    e.preventDefault();
    fetch('{{ route("notifications.read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: id })
    }).finally(() => {
        if (url && url !== '#') {
            window.location.href = url;
        }
    });
}

function markAllNotifRead() {
    fetch('{{ route("notifications.read") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ id: 'all' })
    })
    .then(res => res.json())
    .then(() => fetchNotifications())
    .catch(err => console.error(err));
}

function toggleThemeMode() {
    const isDark = document.body.classList.toggle('dark-theme');
    localStorage.setItem('theme_mode', isDark ? 'dark' : 'light');
    const label = document.getElementById('themeModeLabel');
    if (label) label.textContent = isDark ? 'Gelap' : 'Terlang';
}

// Restore saved theme mode
if (localStorage.getItem('theme_mode') === 'dark') {
    document.body.classList.add('dark-theme');
}
</script>
