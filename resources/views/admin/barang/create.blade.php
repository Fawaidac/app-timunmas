@extends('layouts.admin')

@section('title', 'Tambah Barang - Admin')
@section('page_title', 'Tambah Barang Baru')
@section('page_description', 'Daftarkan produk baru ke dalam sistem')

@section('content')
<div class="section-head">
    <h2>Tambah Barang Baru</h2>
    <p>Isi form di bawah untuk mendaftarkan produk baru beserta stok awal di gudang.</p>
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

    <form action="{{ route('admin.products.store') }}" method="POST">
        @csrf

        <div class="field">
            <label>Nama Barang <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Minyak Goreng Premium 2L" required>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Kode SKU <span style="color:#ef4444;">*</span></label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku') }}" placeholder="Contoh: MG-PREM-2L" required maxlength="50">
            </div>
            <div class="field">
                <label>Kategori</label>
                <input type="text" name="category" class="form-control" value="{{ old('category') }}" placeholder="Contoh: Sembako">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Harga Jual <span style="color:#ef4444;">*</span></label>
                <input type="number" name="price" class="form-control" value="{{ old('price') }}" placeholder="0" min="0" step="0.01" required>
            </div>
            <div class="field">
                <label>Satuan <span style="color:#ef4444;">*</span></label>
                <input type="text" name="unit" class="form-control" value="{{ old('unit') }}" placeholder="Contoh: pcs, kg, dus" required maxlength="20">
            </div>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:16px;">
            <p style="margin:0 0 12px;font-size:13px;font-weight:600;color:var(--ink);">Stok Awal di Gudang</p>
            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="field" style="margin:0;">
                    <label>Pilih Gudang <span style="color:#ef4444;">*</span></label>
                    <select name="warehouse_id" class="form-control" required>
                        <option value="">-- Pilih Gudang --</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}" {{ old('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="field" style="margin:0;">
                    <label>Jumlah Stok Awal <span style="color:#ef4444;">*</span></label>
                    <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', 0) }}" min="0" required>
                </div>
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="javascript:history.back()" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Barang</button>
        </div>
    </form>
</article>
@endsection
