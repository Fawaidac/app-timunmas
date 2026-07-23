@extends('layouts.admin')

@section('title', 'Edit Pengguna - Admin')
@section('page_title', 'Edit Pengguna')
@section('page_description', 'Perbarui informasi akun pengguna')

@section('content')
<div class="section-head">
    <h2>Edit Pengguna</h2>
    <p>Perbarui informasi akun: {{ $user->name }}</p>
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

    <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label>Nama Lengkap <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Email <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
            </div>
            <div class="field">
                <label>Role <span style="color:#ef4444;">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="sales" {{ old('role', $user->role) === 'sales' ? 'selected' : '' }}>Sales</option>
                </select>
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Wilayah / Area</label>
                <input type="text" name="area" class="form-control" value="{{ old('area', $user->area) }}" placeholder="Contoh: Jakarta Selatan">
            </div>
            <div class="field">
                <label>Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx" maxlength="20">
            </div>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:16px;">
            <p style="margin:0 0 12px;font-size:13px;color:var(--muted);">Ganti Password (kosongkan jika tidak ingin mengubah)</p>
            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field" style="margin:0;">
                    <label>Password Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" minlength="8">
                </div>
                <div class="field" style="margin:0;">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                </div>
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.users.show', $user->id) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan</button>
        </div>
    </form>
</article>
@endsection
