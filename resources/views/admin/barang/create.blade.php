@extends('layouts.admin')

@section('title', 'Tambah Barang - Admin')
@section('page_title', 'Tambah Barang Baru')
@section('page_description', 'Daftarkan master barang, satuan bertingkat, harga, dan stok awal gudang')

@push('styles')
<style>
    .form-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 24px 0 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Tambah Barang Baru</h2>
    <p>Lengkapi formulir di bawah ini untuk menambahkan barang/produk baru ke database.</p>
</div>

<article class="card" style="max-width: 860px;">
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

        {{-- 1. IDENTITAS PRODUK --}}
        <div class="form-section-title" style="margin-top:0;">
            <span>📦</span> Identitas Utama Barang
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="field">
                <label>Kode Barang / SKU (KD_BRG) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="sku" value="{{ old('sku', $nextKdBrg) }}" required maxlength="20" style="text-transform:uppercase;font-weight:700;">
                <small style="color:var(--muted);font-size:11px;">Kode unik produk (max 20 karakter).</small>
            </div>
            <div class="field">
                <label>Nama Barang (NM_BRG) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" placeholder="Contoh: DESAKU BALADO 12.5 GR" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Jenis / Kategori (KD_JNS_BRG)</label>
                <select name="kd_jns_brg">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ trim($k->KD_JNS_BRG) }}" {{ old('kd_jns_brg') == trim($k->KD_JNS_BRG) ? 'selected' : '' }}>
                            {{ trim($k->NM_JNS_BRG) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Supplier (KD_SUPPL)</label>
                <select name="kd_suppl">
                    <option value="">— Pilih Supplier —</option>
                    @foreach($supplierList as $s)
                        <option value="{{ trim($s->KD_SUPPL) }}" {{ old('kd_suppl') == trim($s->KD_SUPPL) ? 'selected' : '' }}>
                            {{ trim($s->NM_SUPPL) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Status Aktif (STS_AKTIF)</label>
                <select name="sts_aktif">
                    <option value="AKTIF" {{ old('sts_aktif', 'AKTIF') === 'AKTIF' ? 'selected' : '' }}>AKTIF</option>
                    <option value="NONAKTIF" {{ old('sts_aktif') === 'NONAKTIF' ? 'selected' : '' }}>NONAKTIF</option>
                </select>
            </div>
        </div>

        {{-- 2. SATUAN & KONVERSI BERTINGKAT --}}
        <div class="form-section-title">
            <span>📏</span> Satuan & Konversi Bertingkat
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Satuan 1 (Utama / Terkecil) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="unit" value="{{ old('unit', 'PCS') }}" placeholder="PCS" required maxlength="5" style="text-transform:uppercase;">
                <small style="color:var(--muted);font-size:11px;">Satuan dasar stok (e.g. PCS, BTL, KG).</small>
            </div>
            <div class="field">
                <label>Satuan 2 (Menengah)</label>
                <input type="text" name="satuan2" value="{{ old('satuan2') }}" placeholder="PAK" maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="field">
                <label>Isi Satuan 2 (KAPASITAS2)</label>
                <input type="number" name="kapasitas2" value="{{ old('kapasitas2', 0) }}" min="0" step="1" placeholder="10">
                <small style="color:var(--muted);font-size:11px;">Jumlah Satuan 1 dalam 1 Satuan 2.</small>
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Satuan 3 (Besar / Karton)</label>
                <input type="text" name="satuan3" value="{{ old('satuan3') }}" placeholder="DUS" maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="field">
                <label>Isi Satuan 3 (KAPASITAS3)</label>
                <input type="number" name="kapasitas3" value="{{ old('kapasitas3', 0) }}" min="0" step="1" placeholder="120">
                <small style="color:var(--muted);font-size:11px;">Jumlah Satuan 1 dalam 1 Satuan 3.</small>
            </div>
        </div>

        {{-- 3. HARGA JUAL & BELI --}}
        <div class="form-section-title">
            <span>💰</span> Harga Jual & Harga Beli
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Harga Jual Utama (HARGA_JL) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="price" value="{{ old('price', 0) }}" min="0" step="0.01" placeholder="0" required>
                <small style="color:var(--muted);font-size:11px;">Harga jual per Satuan 1 (Rp).</small>
            </div>
            <div class="field">
                <label>Harga Beli / HPP (HARGA_BL)</label>
                <input type="number" name="harga_bl" value="{{ old('harga_bl', 0) }}" min="0" step="0.01" placeholder="0">
                <small style="color:var(--muted);font-size:11px;">Harga beli / modal per Satuan 1 (Rp).</small>
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Harga Jual Grosir 2 (HARGA_JL2)</label>
                <input type="number" name="harga_jl2" value="{{ old('harga_jl2', 0) }}" min="0" step="0.01" placeholder="0">
            </div>
            <div class="field">
                <label>Harga Jual Grosir 3 (HARGA_JL3)</label>
                <input type="number" name="harga_jl3" value="{{ old('harga_jl3', 0) }}" min="0" step="0.01" placeholder="0">
            </div>
        </div>

        {{-- 4. PENGATURAN STOK & GUDANG --}}
        <div class="form-section-title">
            <span>🏬</span> Pengaturan Stok & Gudang
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Gudang Penempatan Awal</label>
                <select name="warehouse_id">
                    <option value="">— Tanpa Stok Awal —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->name }}" {{ old('warehouse_id') == $w->name ? 'selected' : '' }}>
                            {{ $w->name }} ({{ $w->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Jumlah Stok Awal</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', 0) }}" min="0" step="1">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Stok Minimum (STOK_MIN)</label>
                <input type="number" name="stok_min" value="{{ old('stok_min', 0) }}" min="0" step="1">
                <small style="color:var(--muted);font-size:11px;">Batas peringatan stok menipis.</small>
            </div>
            <div class="field">
                <label>Lokasi Rak Gudang (RAK)</label>
                <input type="text" name="rak" value="{{ old('rak') }}" placeholder="Contoh: A-01-02" maxlength="20">
            </div>
            <div class="field">
                <label>Berat per Satuan (BERAT)</label>
                <input type="number" name="berat" value="{{ old('berat', 0) }}" min="0" step="0.01" placeholder="Gram / Kg">
            </div>
        </div>

        <div class="button-row" style="margin-top:32px;display:flex;gap:12px;">
            <a href="{{ route('admin.products.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Barang Baru</button>
        </div>
    </form>
</article>
@endsection
