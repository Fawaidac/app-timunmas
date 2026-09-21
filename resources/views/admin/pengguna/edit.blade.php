@extends('layouts.admin')

@section('title', 'Edit Pengguna Admin')
@section('page_title', 'Edit Pengguna Admin')
@section('page_description', 'Perbarui informasi akun admin (MST_PENGGUNA)')

@section('content')
<div class="section-head">
    <h2>Edit Akun Admin</h2>
    <p>Perbarui akun: <b>{{ $user->NM_USER }}</b> (<code>MST_PENGGUNA</code>)</p>
</div>

<article class="card" style="max-width:600px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $user->NO_USER) }}" method="POST" id="editUserForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="source" value="admin">

        <div class="field">
            <label>Username (NM_USER) <span style="color:#ef4444;">*</span></label>
            <input type="text" name="nm_user" class="form-control"
                value="{{ old('nm_user', $user->NM_USER) }}"
                style="text-transform:uppercase;"
                required>
            <small style="color:var(--muted);font-size:11px;">Dipakai untuk login admin. Akan disimpan uppercase.</small>
        </div>

        {{-- Ganti Password --}}
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:16px;margin-top:16px;">
            <p style="margin:0 0 12px;font-size:13px;color:var(--muted);">Ganti Password (kosongkan jika tidak ingin mengubah)</p>
            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field" style="margin:0;">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control"
                        placeholder="Min. 4 karakter" minlength="4">
                </div>
                <div class="field" style="margin:0;">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control"
                        placeholder="Ulangi password baru">
                </div>
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan</button>
        </div>
    </form>
</article>
@endsection
