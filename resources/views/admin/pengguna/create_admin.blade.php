@extends('layouts.admin')
@section('title', 'Buat Akun Admin')
@section('content')
<div class="section-head">
    <h2>Buat Akun Admin Baru</h2>
    <p>Akun akan disimpan ke tabel <code>MST_PENGGUNA</code>.</p>
</div>

<article class="card" style="max-width:600px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.store.admin') }}" method="POST">
        @csrf

        <div class="field">
            <label>Username (NM_USER) <span style="color:#ef4444;">*</span></label>
            <input type="text" name="nm_user" value="{{ old('nm_user') }}"
                placeholder="Contoh: BUDI.ADMIN" style="text-transform:uppercase;" required>
            <small style="color:var(--muted);font-size:11px;">Dipakai untuk login. Disimpan uppercase.</small>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password" placeholder="Min. 4 karakter" required minlength="4">
            </div>
            <div class="field">
                <label>Konfirmasi Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Buat Akun Admin</button>
        </div>
    </form>
</article>
@endsection
