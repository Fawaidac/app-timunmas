@extends('layouts.app')

@section('title', 'Pencarian Stok - SFA Orange')
@section('page_title', 'Pencarian Stok Barang')
@section('page_description', 'Cek stok aktual per gudang sebelum membuat order pelanggan')

@push('styles')
<style>
/* Styling Pagination Modern & Clean */
.pagination-wrapper {
    margin-top: 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.pagination-info {
    font-size: 13px;
    color: #64748b;
}

.pagination-container {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.pagination-container .page-item .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}

/* Hover state */
.pagination-container .page-item .page-link:hover {
    border-color: #f97316;
    color: #ea580c;
    background-color: #fff7ed;
}

/* Active page state */
.pagination-container .page-item.active .page-link {
    background-color: #f97316;
    border-color: #f97316;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.25);
}

/* Disabled state */
.pagination-container .page-item.disabled .page-link {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    color: #cbd5e1;
    cursor: not-allowed;
    pointer-events: none;
}
</style>
@endpush

@section('content')
<!-- Form Pencarian Server-Side -->
<form action="{{ route('sales.stok.index') }}" method="GET" class="toolbar">
    <label class="search-box" style="width: 100%; max-width: 400px;">
        <span>⌕</span>
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, SKU, atau kategori..." onchange="this.form.submit()">
    </label>
</form>

<div class="product-grid" id="product-grid">
    @forelse($products as $product)
        <article class="product-card">
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
                <a href="{{ route('sales.stok.show', $product->id) }}" class="button button-soft" style="flex:1;padding:7px;font-size:11px;text-align:center;">Detail</a>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📦</div>
            <p style="font-size:16px;font-weight:500;">Barang tidak ditemukan</p>
            <p style="font-size:13px;">Coba kata kunci pencarian yang berbeda.</p>
        </div>
    @endforelse
</div>

<!-- SECTION PAGINATION MODERN -->
@if($products->hasPages())
    <div class="pagination-wrapper">
        <div class="pagination-info">
            Menampilkan <b>{{ $products->firstItem() }}</b> - <b>{{ $products->lastItem() }}</b> dari total <b>{{ $products->total() }}</b> barang
        </div>

        <ul class="pagination-container">
            {{-- Tombol Previous --}}
            @if ($products->onFirstPage())
                <li class="page-item disabled"><span class="page-link">‹</span></li>
            @else
                <li class="page-item"><a class="page-link" href="{{ $products->previousPageUrl() }}" rel="prev">‹</a></li>
            @endif

            {{-- Angka Halaman --}}
            @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                @if ($page == $products->currentPage())
                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                @endif
            @endforeach

            {{-- Tombol Next --}}
            @if ($products->hasMorePages())
                <li class="page-item"><a class="page-link" href="{{ $products->nextPageUrl() }}" rel="next">›</a></li>
            @else
                <li class="page-item disabled"><span class="page-link">›</span></li>
            @endif
        </ul>
    </div>
@endif
@endsection