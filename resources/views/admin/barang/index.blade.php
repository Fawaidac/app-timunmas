@extends('layouts.admin')

@section('title', 'Master Data Barang - Admin')
@section('page_title', 'Master Data Barang / Produk')
@section('page_description', 'Kelola stok, harga jual, harga beli, dan pendaftaran produk baru')

@section('content')
<div class="section-head">
    <h2>Master Data Barang & Produk</h2>
    <p>Kelola data master barang (<code>BARANG</code>), multi-satuan, harga bertingkat, dan stok per gudang.</p>
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

<!-- Toolbar Pencarian & Filter Server-Side -->
<div class="toolbar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-bottom:20px;">
    <form action="{{ route('admin.products.index') }}" method="GET" class="search-form" style="display:flex;flex-wrap:wrap;gap:8px;flex:1;max-width:800px;">
        <label class="search-box" style="flex:2;min-width:240px;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, SKU, kategori, supplier...">
        </label>

        <select name="kategori" style="flex:1;min-width:140px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:#fff;">
            <option value="">— Semua Kategori —</option>
            @foreach($kategoriList as $k)
                <option value="{{ trim($k->NM_JNS_BRG) }}" {{ request('kategori') == trim($k->NM_JNS_BRG) ? 'selected' : '' }}>
                    {{ trim($k->NM_JNS_BRG) }}
                </option>
            @endforeach
        </select>

        <select name="supplier" style="flex:1;min-width:150px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:#fff;">
            <option value="">— Semua Supplier —</option>
            @foreach($supplierList as $s)
                <option value="{{ trim($s->KD_SUPPL) }}" {{ request('supplier') == trim($s->KD_SUPPL) ? 'selected' : '' }}>
                    {{ trim($s->NM_SUPPL) }}
                </option>
            @endforeach
        </select>
    </form>

    <a href="{{ route('admin.products.create') }}" class="button button-primary">＋ Tambah Barang</a>
</div>

<!-- Product Grid View -->
<div class="product-grid" id="product-grid">
    @forelse($products as $product)
        @php
            $totalStok = $product->warehouses->sum('pivot.stock_quantity');
            $isLowStock = ($totalStok <= ($product->STOK_MIN ?? 0) && ($product->STOK_MIN ?? 0) > 0);
        @endphp
        <article class="product-card" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                {{-- Product Image Placeholder --}}
                <div class="product-image" style="background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:12px;width:100%;aspect-ratio:1/1;display:flex;align-items:center;justify-content:center;margin-bottom:12px;font-size:44px;position:relative;">
                    📦
                    <span class="badge {{ ($product->STS_AKTIF ?? 'AKTIF') === 'AKTIF' ? 'badge-success' : 'badge-danger' }}" style="position:absolute;top:8px;right:8px;font-size:10px;">
                        {{ $product->STS_AKTIF ?? 'AKTIF' }}
                    </span>
                </div>

                {{-- Product Title & SKU --}}
                <h4 style="margin:0 0 4px;font-size:14px;font-weight:700;line-height:1.4;color:var(--ink);" title="{{ $product->name }}">
                    {{ Str::limit($product->name, 45) }}
                </h4>
                <div class="sku" style="font-size:11px;color:var(--muted);margin-bottom:6px;">
                    SKU: <code>{{ $product->sku }}</code>
                    @if($product->RAK)
                        · Rak: <span>{{ $product->RAK }}</span>
                    @endif
                </div>

                {{-- Category & Supplier Badges --}}
                <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:10px;">
                    @if($product->category)
                        <span style="background:#fff7ed;color:#c2410c;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:600;">
                            {{ $product->category }}
                        </span>
                    @endif
                    @if($product->supplier_name)
                        <span style="background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;" title="{{ $product->supplier_name }}">
                            🏭 {{ Str::limit($product->supplier_name, 18) }}
                        </span>
                    @endif
                </div>

                {{-- Price & Stock Information --}}
                <div class="stock-row" style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;background:#f8fafc;padding:10px;border-radius:10px;">
                    <div>
                        <div style="font-size:11px;color:var(--muted);">Harga Jual</div>
                        <div style="font-size:15px;font-weight:700;color:var(--orange-600);">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </div>
                        @if($product->buy_price > 0)
                            <div style="font-size:10px;color:var(--muted);margin-top:2px;">
                                Beli: Rp {{ number_format($product->buy_price, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:11px;color:var(--muted);">Total Stok</div>
                        <div style="font-size:15px;font-weight:700;color:{{ $isLowStock ? 'var(--danger)' : 'var(--ink)' }};">
                            {{ number_format($totalStok, 0, ',', '.') }} <span style="font-size:12px;font-weight:500;">{{ $product->unit }}</span>
                        </div>
                        @if($isLowStock)
                            <div style="font-size:10px;color:var(--danger);font-weight:600;margin-top:2px;">
                                ⚠ Min. {{ (int) $product->STOK_MIN }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div style="display:flex;gap:6px;align-items:center;padding-top:10px;border-top:1px solid #f1f5f9;margin-top:4px;">
                <a href="{{ route('admin.products.show', $product->sku) }}" class="button button-soft" style="flex:1;padding:7px;font-size:11px;text-align:center;">
                    Detail
                </a>
                <a href="{{ route('admin.products.edit', $product->sku) }}" class="button button-soft" style="flex:1;padding:7px;font-size:11px;text-align:center;">
                    Edit
                </a>
                <form action="{{ route('admin.products.destroy', $product->sku) }}" method="POST"
                      onsubmit="return confirm('Hapus barang [{{ $product->name }}]? Tindakan ini tidak dapat dibatalkan.');" style="margin:0;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="button button-soft" style="padding:7px 10px;font-size:11px;background:#fee2e2;color:#991b1b;border-color:#fca5a5;cursor:pointer;">
                        🗑️
                    </button>
                </form>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 20px;color:var(--muted);background:#fff;border-radius:16px;border:1px solid var(--line);">
            <div style="font-size:48px;margin-bottom:12px;">📦</div>
            <p style="font-size:16px;font-weight:600;color:var(--ink);">Belum ada data barang</p>
            <p style="font-size:13px;margin-bottom:16px;">Coba gunakan kata kunci pencarian yang berbeda atau tambahkan barang baru.</p>
            <a href="{{ route('admin.products.create') }}" class="button button-primary">＋ Tambah Barang Sekarang</a>
        </div>
    @endforelse
</div>

<!-- SECTION PAGINATION -->
@include('partials.pagination', ['paginator' => $products, 'itemLabel' => 'barang'])

@endsection
