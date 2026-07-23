@extends('layouts.admin')

@section('title', 'User Management - Admin')
@section('page_title', 'User Management')
@section('page_description', 'Kelola pengguna sistem dan hak akses')

@section('content')
<div class="section-head">
    <h2>User Management</h2>
    <p>Kelola akun pengguna, peran (role), dan wilayah operasional.</p>
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
    <label class="search-box search-grow">
        <span>⌕</span>
        <input type="search" placeholder="Cari pengguna...">
    </label>
    <a href="{{ route('admin.users.create') }}" class="button button-primary">＋ Tambah User</a>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Wilayah</th>
                    <th>Telepon</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--orange-600);">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <b>{{ $user->name }}</b>
                                @if($user->id === auth()->id())
                                    <span style="background:#dbeafe;color:#1d4ed8;padding:2px 6px;border-radius:20px;font-size:10px;">Saya</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge {{ $user->role === 'admin' ? 'badge-success' : 'badge-orange' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td>{{ $user->area ?? '-' }}</td>
                        <td>{{ $user->phone ?? '-' }}</td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('admin.users.show', $user->id) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Detail</a>
                                <a href="{{ route('admin.users.edit', $user->id) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Edit</a>
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="padding:6px 10px;font-size:11px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;cursor:pointer;">Hapus</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;padding:40px;color:var(--muted);">
                            Belum ada data pengguna.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>
@endsection
