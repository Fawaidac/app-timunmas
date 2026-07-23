@extends('layouts.admin')

@section('title', 'Tambah Pengguna - Admin')
@section('page_title', 'Tambah Pengguna Baru')
@section('page_description', 'Buat akun pengguna baru')

@section('content')
<div class="section-head">
    <h2>Tambah Pengguna Baru</h2>
    <p>Buat akun baru untuk admin atau sales.</p>
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

    <form action="{{ route('admin.users.store') }}" method="POST">
        @csrf

        <div class="field">
            <label>Nama Lengkap <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Andi Salesman" required>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Email <span style="color:#ef4444;">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@contoh.com" required>
            </div>
            <div class="field">
                <label>Role <span style="color:#ef4444;">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="">Pilih Role</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="sales" {{ old('role') === 'sales' ? 'selected' : '' }}>Sales</option>
                </select>
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Wilayah / Area</label>
                <input type="text" name="area" class="form-control" value="{{ old('area') }}" placeholder="Contoh: Jakarta Selatan">
            </div>
            <div class="field">
                <label>Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" maxlength="20">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="Min. 8 karakter" required minlength="8">
            </div>
            <div class="field">
                <label>Konfirmasi Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password" required>
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Buat Pengguna</button>
        </div>
    </form>
</article>
@endsection
