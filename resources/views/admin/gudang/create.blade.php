@extends('layouts.admin')

@section('title', 'Tambah Gudang - Admin')
@section('page_title', 'Tambah Gudang Baru')
@section('page_description', 'Daftarkan gudang baru ke dalam sistem')

@section('content')
<div class="section-head">
    <h2>Tambah Gudang Baru</h2>
    <p>Isi form di bawah untuk mendaftarkan gudang baru.</p>
</div>

<article class="card" style="width: 70%; max-width: 100%;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.warehouses.store') }}" method="POST">
        @csrf

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Kode Gudang <span style="color:#ef4444;">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: GDG-JKT-01" required maxlength="50">
            </div>
            <div class="field">
                <label>Nama Gudang <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Gudang Jakarta Selatan" required maxlength="100">
            </div>
        </div>

        <div class="field">
            <label>Alamat Gudang</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota">{{ old('address') }}</textarea>
        </div>

        {{-- <div class="field">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                <span>Gudang Aktif</span>
            </label>
        </div> --}}

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="javascript:history.back()" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Gudang</button>
        </div>
    </form>
</article>
@endsection
