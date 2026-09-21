@extends('layouts.admin')

@section('title', 'Edit Barang - Admin')
@section('page_title', 'Edit Barang')
@section('page_description', 'Perbarui informasi master barang, satuan, harga, dan stok')

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
    <h2>Edit Barang: {{ $product->NM_BRG }}</h2>
    <p>Perbarui informasi master produk [<code>{{ $product->KD_BRG }}</code>].</p>
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

    <form action="{{ route('admin.products.update', $product->KD_BRG) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- 1. IDENTITAS PRODUK --}}
        <div class="form-section-title" style="margin-top:0;">
            <span>📦</span> Identitas Utama Barang
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="field">
                <label>Kode Barang / SKU (KD_BRG)</label>
                <input type="text" value="{{ $product->KD_BRG }}" readonly style="background:#f1f5f9;cursor:not-allowed;font-weight:700;">
                <small style="color:var(--muted);font-size:11px;">Primary key di database, tidak dapat diubah.</small>
            </div>
            <div class="field">
                <label>Nama Barang (NM_BRG) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" value="{{ old('name', $product->NM_BRG) }}" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Jenis / Kategori (KD_JNS_BRG)</label>
                <select name="kd_jns_brg">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ trim($k->KD_JNS_BRG) }}"
                            {{ old('kd_jns_brg', trim((string)$product->KD_JNS_BRG)) == trim($k->KD_JNS_BRG) || old('kd_jns_brg') == trim($k->NM_JNS_BRG) || trim((string)$product->JNS_BRG) == trim($k->NM_JNS_BRG) ? 'selected' : '' }}>
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
                        <option value="{{ trim($s->KD_SUPPL) }}"
                            {{ old('kd_suppl', trim((string)$product->KD_SUPPL)) == trim($s->KD_SUPPL) ? 'selected' : '' }}>
                            {{ trim($s->NM_SUPPL) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Status Aktif (STS_AKTIF)</label>
                <select name="sts_aktif">
                    <option value="AKTIF" {{ old('sts_aktif', $product->STS_AKTIF) === 'AKTIF' ? 'selected' : '' }}>AKTIF</option>
                    <option value="NONAKTIF" {{ old('sts_aktif', $product->STS_AKTIF) === 'NONAKTIF' || old('sts_aktif', $product->STS_AKTIF) === 'TIDAK' ? 'selected' : '' }}>NONAKTIF</option>
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
                <input type="text" name="unit" value="{{ old('unit', $product->SATUAN1 ?: 'PCS') }}" placeholder="PCS" required maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="field">
                <label>Satuan 2 (Menengah)</label>
                <input type="text" name="satuan2" value="{{ old('satuan2', $product->SATUAN2) }}" placeholder="PAK" maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="field">
                <label>Isi Satuan 2 (KAPASITAS2)</label>
                <input type="number" name="kapasitas2" value="{{ old('kapasitas2', (float)$product->KAPASITAS2) }}" min="0" step="1">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Satuan 3 (Besar / Karton)</label>
                <input type="text" name="satuan3" value="{{ old('satuan3', $product->SATUAN3) }}" placeholder="DUS" maxlength="5" style="text-transform:uppercase;">
            </div>
            <div class="field">
                <label>Isi Satuan 3 (KAPASITAS3)</label>
                <input type="number" name="kapasitas3" value="{{ old('kapasitas3', (float)$product->KAPASITAS3) }}" min="0" step="1">
            </div>
        </div>

        {{-- 3. HARGA JUAL & BELI --}}
        <div class="form-section-title">
            <span>💰</span> Harga Jual & Harga Beli
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Harga Jual Utama (HARGA_JL) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="price" value="{{ old('price', (float)$product->HARGA_JL) }}" min="0" step="0.01" required>
            </div>
            <div class="field">
                <label>Harga Beli / HPP (HARGA_BL)</label>
                <input type="number" name="harga_bl" value="{{ old('harga_bl', (float)$product->HARGA_BL) }}" min="0" step="0.01">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Harga Jual Grosir 2 (HARGA_JL2)</label>
                <input type="number" name="harga_jl2" value="{{ old('harga_jl2', (float)$product->HARGA_JL2) }}" min="0" step="0.01">
            </div>
            <div class="field">
                <label>Harga Jual Grosir 3 (HARGA_JL3)</label>
                <input type="number" name="harga_jl3" value="{{ old('harga_jl3', (float)$product->HARGA_JL3) }}" min="0" step="0.01">
            </div>
        </div>

        {{-- 4. PENGATURAN STOK & GUDANG --}}
        <div class="form-section-title">
            <span>🏬</span> Pengaturan Stok & Gudang
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Pilih Gudang untuk Koreksi Stok</label>
                <select name="warehouse_id">
                    <option value="">— Tidak Mengubah Stok Gudang —</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->name }}" {{ old('warehouse_id', $selectedStock?->GUDANG) == $w->name ? 'selected' : '' }}>
                            {{ $w->name }} ({{ $w->code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Jumlah Stok di Gudang Terpilih</label>
                <input type="number" name="stock_quantity" value="{{ old('stock_quantity', (float)($selectedStock?->QTY_AKHIR ?? 0)) }}" min="0" step="1">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Stok Minimum (STOK_MIN)</label>
                <input type="number" name="stok_min" value="{{ old('stok_min', (float)$product->STOK_MIN) }}" min="0" step="1">
            </div>
            <div class="field">
                <label>Lokasi Rak Gudang (RAK)</label>
                <input type="text" name="rak" value="{{ old('rak', $product->RAK) }}" placeholder="Contoh: A-01-02" maxlength="20">
            </div>
            <div class="field">
                <label>Berat per Satuan (BERAT)</label>
                <input type="number" name="berat" value="{{ old('berat', (float)$product->BERAT) }}" min="0" step="0.01">
            </div>
        </div>

        <div class="button-row" style="margin-top:32px;display:flex;gap:12px;">
            <a href="{{ route('admin.products.show', $product->KD_BRG) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan Barang</button>
        </div>
    </form>
</article>
@endsection
