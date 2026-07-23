@extends('layouts.admin')

@section('title', 'Tambah Customer - Admin')
@section('page_title', 'Tambah Customer Baru')
@section('page_description', 'Daftarkan customer atau outlet baru')

@section('content')
<div class="section-head">
    <h2>Tambah Customer Baru</h2>
    <p>Isi form di bawah untuk mendaftarkan customer baru.</p>
</div>

<article class="card" style="max-width:640px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Kode Customer <span style="color:#ef4444;">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: CUST-001" required maxlength="50">
            </div>
            <div class="field">
                <label>Nama Customer <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Toko Berkah Jaya" required maxlength="150">
            </div>
        </div>

        <div class="field">
            <label>Alamat Lengkap</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota">{{ old('address') }}</textarea>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="021-xxxxxxx" maxlength="20">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@contoh.com" maxlength="100">
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.customers.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Customer</button>
        </div>
    </form>
</article>
@endsection
