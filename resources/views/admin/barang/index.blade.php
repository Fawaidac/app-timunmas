@extends('layouts.admin')

@section('title', 'Master Data Barang - Admin')
@section('page_title', 'Master Data Barang / Produk')
@section('page_description', 'Kelola stok, harga, dan pendaftaran produk baru')

@section('content')
<div class="section-head">
    <h2>Kelola Barang & Stok Produk</h2>
    <p>Monitor stok barang per gudang dan daftarkan produk baru ke dalam sistem.</p>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✔</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✖</span> {{ session('error') }}
    </div>
@endif

<div class="toolbar">
    <label class="search-box">
        <span>⌕</span>
        <input type="search" id="product-search" placeholder="Cari nama barang, SKU, atau kategori...">
    </label>
    <a href="{{ route('admin.products.create') }}" class="button button-primary">＋ Tambah Barang</a>
</div>

<div class="product-grid" id="product-grid">
    @forelse($products as $product)
        <article class="product-card" data-product="{{ strtolower($product->name . ' ' . $product->sku . ' ' . $product->category) }}">
            {{-- Placeholder image produk --}}
            <div class="product-image" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:12px;width:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;margin-bottom:12px;font-size:48px;">
                📦
            </div>
            <h4 style="margin:0 0 4px;font-size:14px;font-weight:600;line-height:1.4;">{{ $product->name }}</h4>
            <div class="sku" style="font-size:11px;color:var(--muted);margin-bottom:8px;">SKU: {{ $product->sku }}</div>
            @if($product->category)
                <div style="font-size:11px;color:var(--muted);margin-bottom:8px;">
                    <span style="background:#fff7ed;color:#c2410c;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;">{{ $product->category }}</span>
                </div>
            @endif
            <div class="stock-row" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div>
                    <div style="font-size:12px;color:var(--muted);">Harga</div>
                    <div style="font-size:15px;font-weight:700;color:var(--orange-600);">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:12px;color:var(--muted);">Stok</div>
                    <div style="font-size:15px;font-weight:700;color:var(--ink);">
                        {{ $product->warehouses->sum('pivot.stock_quantity') }} {{ $product->unit }}
                    </div>
                </div>
            </div>
            @if($product->warehouses->isNotEmpty())
                <div style="font-size:10px;color:var(--muted);margin-bottom:8px;">
                    📍 {{ $product->warehouses->first()->name }}
                </div>
            @endif
            <div style="display:flex;gap:6px;">
                <a href="{{ route('admin.products.show', $product->id) }}" class="button button-soft" style="flex:1;padding:7px;font-size:11px;text-align:center;">Detail</a>
                <a href="{{ route('admin.products.edit', $product->id) }}" class="button button-soft" style="flex:1;padding:7px;font-size:11px;text-align:center;">Edit</a>
                <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin hapus barang ini?');" style="flex:1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button" style="width:100%;padding:7px;font-size:11px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;cursor:pointer;">Hapus</button>
                </form>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📦</div>
            <p style="font-size:16px;font-weight:500;">Belum ada data barang</p>
            <a href="{{ route('admin.products.create') }}" class="button button-primary" style="margin-top:12px;">＋ Tambah Barang Pertama</a>
        </div>
    @endforelse
</div>

@push('scripts')
<script>
document.getElementById('product-search').addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#product-grid .product-card').forEach(card => {
        card.style.display = card.dataset.product.includes(q) ? '' : 'none';
    });
});
</script>
@endpush
@endsection
