@extends('layouts.admin')

@section('title', 'Detail Pengguna - Admin')
@section('page_title', 'Detail Pengguna')
@section('page_description', 'Informasi akun pengguna')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $user->name }}</h2>
        <p>{{ $user->email }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.users.edit', $user->id) }}" class="button button-soft">✏ Edit</a>
        @if($user->id !== auth()->id())
            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Yakin hapus pengguna ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="button" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;padding:8px 16px;cursor:pointer;">🗑 Hapus</button>
            </form>
        @endif
        <a href="{{ route('admin.users.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;">
    <article class="card">
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Nama Lengkap</label>
                <input type="text" class="form-control" value="{{ $user->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Email</label>
                <input type="text" class="form-control" value="{{ $user->email }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Wilayah</label>
                <input type="text" class="form-control" value="{{ $user->area ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Telepon</label>
                <input type="text" class="form-control" value="{{ $user->phone ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
        </div>
    </article>

    <article class="card">
        <div style="text-align:center;padding:20px 0;border-bottom:1px solid #f1f5f9;margin-bottom:20px;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:700;color:var(--orange-600);margin:0 auto 10px;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <span class="badge {{ $user->role === 'admin' ? 'badge-success' : 'badge-orange' }}" style="font-size:12px;padding:4px 14px;">
                {{ ucfirst($user->role) }}
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📅</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Bergabung</div>
                <div style="font-size:13px;font-weight:600;">{{ $user->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </article>
</div>
@endsection
