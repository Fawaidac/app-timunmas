@extends('layouts.app')

@section('title', 'Pencarian Stok - SFA Orange')
@section('page_title', 'Pencarian Stok Barang')
@section('page_description', 'Cek stok aktual per gudang sebelum membuat order pelanggan')

@section('content')
<div class="toolbar">
    <label class="search-box">
        <span>⌕</span>
        <input type="search" id="product-search" placeholder="Cari nama barang, SKU, atau kategori...">
    </label>
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
                <a href="{{ route('sales.stok.show', $product->id) }}" class="button button-soft" style="flex:1;padding:7px;font-size:11px;text-align:center;">Detail</a>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📦</div>
            <p style="font-size:16px;font-weight:500;">Belum ada data barang</p>
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

