@extends('layouts.admin')

@section('title', 'User Management - Admin')
@section('page_title', 'User Management')
@section('page_description', 'Kelola akun Admin (MST_PENGGUNA) dan Sales (PEGAWAI)')

@section('content')
<div class="section-head">
    <h2>User Management</h2>
    <p>Akun <strong>Admin</strong> dari <code>MST_PENGGUNA</code> · Akun <strong>Sales</strong> dari <code>PEGAWAI</code></p>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✔</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✖</span> {{ session('error') }}
    </div>
@endif

<div class="toolbar">
    <form action="{{ route('admin.users.index') }}" method="GET" style="flex: 1; max-width: 420px;">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ $search ?? '' }}" placeholder="Cari username, kode, nama, kontak, alamat..." onchange="this.form.submit()">
        </label>
    </form>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.users.create.admin') }}" class="button button-soft">＋ Admin Baru</a>
        <a href="{{ route('admin.users.create.sales') }}" class="button button-primary">＋ Pegawai Sales</a>
    </div>
</div>

<article class="card">
    <div class="table-responsive">
        <table id="userTable">
            <thead>
                <tr>
                    <th>Nama & Akun</th>
                    <th>Sumber Tabel</th>
                    <th>Role</th>
                    <th>Kontak (HP/Email)</th>
                    <th>Alamat & Wilayah</th>
                    <th>Status Login</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:linear-gradient(135deg,{{ $u['role'] === 'admin' ? '#dbeafe,#bfdbfe' : '#fff7ed,#fed7aa' }});color:{{ $u['role'] === 'admin' ? '#1d4ed8' : '#c2410c' }};">
                                    {{ strtoupper(substr($u['nama'], 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:14px;color:#1e293b;">
                                        {{ $u['nama'] }}
                                        @if($u['source'] === 'admin' && $u['id'] == auth()->guard('web')->id())
                                            <span style="background:#dbeafe;color:#1d4ed8;padding:2px 6px;border-radius:20px;font-size:10px;margin-left:4px;font-weight:600;">Saya</span>
                                        @endif
                                    </div>
                                    <div style="font-size:11px;color:var(--muted);display:flex;gap:6px;align-items:center;">
                                        @if($u['kd_peg'])
                                            <span>KD: <code>{{ $u['kd_peg'] }}</code></span>
                                        @endif
                                        @if($u['source'] === 'admin')
                                            <span>User: <code>{{ $u['username'] }}</code></span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($u['source'] === 'admin')
                                <span style="background:#eff6ff;color:#1d4ed8;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;">MST_PENGGUNA</span>
                            @else
                                <span style="background:#fff7ed;color:#c2410c;padding:3px 8px;border-radius:6px;font-size:11px;font-weight:600;">PEGAWAI</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $u['role'] === 'admin' ? 'badge-success' : 'badge-orange' }}">
                                {{ ucfirst($u['role']) }}
                            </span>
                        </td>
                        <td>
                            @if($u['hp'] || $u['e_mail'])
                                <div style="font-size:12px;display:flex;flex-direction:column;gap:2px;">
                                    @if($u['hp'])
                                        <div><span style="color:var(--muted);">📱</span> {{ $u['hp'] }}</div>
                                    @endif
                                    @if($u['e_mail'])
                                        <div style="color:var(--muted);font-size:11px;"><span style="color:var(--muted);">✉</span> {{ $u['e_mail'] }}</div>
                                    @endif
                                </div>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($u['alm_peg'] || $u['kd_wil'])
                                <div style="font-size:12px;max-width:220px;line-height:1.3;">
                                    @if($u['alm_peg'])
                                        <div>{{ $u['alm_peg'] }}</div>
                                    @endif
                                    @if($u['kd_wil'])
                                        <span style="display:inline-block;background:#f1f5f9;color:#475569;font-size:10px;padding:1px 5px;border-radius:4px;margin-top:2px;">
                                            Wilayah: {{ $u['kd_wil'] }}
                                        </span>
                                    @endif
                                </div>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            @if($u['has_password'])
                                <span style="display:inline-flex;align-items:center;gap:4px;color:#16a34a;font-size:12px;font-weight:600;">
                                    <span>✔</span> Aktif
                                </span>
                            @else
                                <span style="display:inline-flex;align-items:center;gap:4px;color:#dc2626;font-size:12px;font-weight:500;">
                                    <span>✖</span> Belum ada password
                                </span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <a href="{{ route('admin.users.edit', $u['id']) }}?source={{ $u['source'] }}"
                                   class="button button-soft" style="padding:6px 10px;font-size:11px;">Edit</a>

                                @if(!($u['source'] === 'admin' && $u['id'] == auth()->guard('web')->id()))
                                    <form action="{{ route('admin.users.destroy', $u['id']) }}" method="POST"
                                          onsubmit="return confirm('{{ $u['source'] === 'sales' ? 'Cabut akses login sales ini?' : 'Hapus akun admin ini?' }}')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="source" value="{{ $u['source'] }}">
                                        <button type="submit" style="padding:6px 10px;font-size:11px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;cursor:pointer;">
                                            {{ $u['source'] === 'sales' ? 'Cabut' : 'Hapus' }}
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">
                            Tidak ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $users, 'itemLabel' => 'pengguna'])
</article>

@endsection