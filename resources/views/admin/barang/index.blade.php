@extends('layouts.admin')

@section('title', 'Master Data Barang - Admin')
@section('page_title', 'Master Data Barang / Produk')
@section('page_description', 'Kelola stok, harga, dan pendaftaran produk baru')

@section('content')
<div class="section-head">
    <h2>Kelola Barang & Stok Produk</h2>
    <p>Monitor stok barang per gudang dan daftarkan produk baru ke dalam sistem.</p>
</div>

@include('partials.product-grid', ['showAddButton' => true])

<!-- Modal Tambah Barang Baru -->
<div id="add-product-modal" style="display: none; position: fixed; inset: 0; z-index: 50; background: rgba(15, 23, 42, 0.5); place-items: center; padding: 20px;">
    <div class="card" style="width: 100%; max-width: 540px; background: #fff; border-radius: 18px; padding: 24px; box-shadow: var(--shadow);">
        <div class="card-title">
            <h3>＋ Tambah Barang Baru</h3>
            <button type="button" class="icon-button" style="width: 32px; height: 32px;" onclick="document.getElementById('add-product-modal').style.display='none'">✕</button>
        </div>
        
        <form onsubmit="event.preventDefault(); alert('Produk baru berhasil ditambahkan!'); document.getElementById('add-product-modal').style.display='none';">
            <div class="field">
                <label>Nama Barang / Produk</label>
                <input type="text" placeholder="Contoh: Berkas Cap Pandan 5kg" required>
            </div>
            
            <div class="form-grid">
                <div class="field">
                    <label>Kode SKU</label>
                    <input type="text" placeholder="SKU-XXX-001" required>
                </div>
                <div class="field">
                    <label>Kategori</label>
                    <select>
                        <option>Sembako</option>
                        <option>Minuman</option>
                        <option>Makanan Ringan</option>
                        <option>Bumbu Dapur</option>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="field">
                    <label>Jumlah Stok Awal</label>
                    <input type="number" value="100" min="1" required>
                </div>
                <div class="field">
                    <label>Lokasi Gudang</label>
                    <select>
                        <option>Gudang Jakarta</option>
                        <option>Gudang Bekasi</option>
                        <option>Gudang Tangerang</option>
                    </select>
                </div>
            </div>
            
            <div class="button-row" style="margin-top: 20px;">
                <button type="button" class="button button-soft" style="flex: 1;" onclick="document.getElementById('add-product-modal').style.display='none'">Batal</button>
                <button type="submit" class="button button-primary" style="flex: 2;">Simpan Barang Baru</button>
            </div>
        </form>
    </div>
</div>
@endsection
