@extends('layouts.admin')

@section('title', 'Detail Barang - Admin')
@section('page_title', 'Detail Barang')
@section('page_description', 'Informasi lengkap produk')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $product->name }}</h2>
        <p>SKU: {{ $product->sku }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.products.edit', $product->id) }}" class="button button-soft">✏ Edit</a>
        <form action="{{ route('admin.products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin hapus barang ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="button" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;padding:8px 16px;cursor:pointer;">🗑 Hapus</button>
        </form>
        <a href="{{ route('admin.products.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 300px;gap:20px;align-items:start;">
    <article class="card">
        {{-- Placeholder image produk --}}
        <div style="background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:12px;width:100%;aspect-ratio:16/7;display:flex;align-items:center;justify-content:center;margin-bottom:20px;font-size:72px;">
            📦
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Nama Barang</label>
                <input type="text" class="form-control" value="{{ $product->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Kode SKU</label>
                <input type="text" class="form-control" value="{{ $product->sku }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Kategori</label>
                <input type="text" class="form-control" value="{{ $product->category ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Satuan</label>
                <input type="text" class="form-control" value="{{ $product->unit }}" readonly style="background:#f9fafb;">
            </div>
        </div>

        {{-- Stok per gudang --}}
        <div style="border-top:1px solid #f1f5f9;padding-top:20px;">
            <h4 style="margin:0 0 12px;font-size:15px;font-weight:600;">Stok per Gudang</h4>
            @forelse($product->warehouses as $warehouse)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#f8fafc;border-radius:8px;margin-bottom:8px;">
                    <div>
                        <div style="font-size:14px;font-weight:600;color:var(--ink);">{{ $warehouse->name }}</div>
                        <div style="font-size:12px;color:var(--muted);">{{ $warehouse->code }}</div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:20px;font-weight:700;color:var(--orange-600);">{{ $warehouse->pivot->stock_quantity }}</div>
                        <div style="font-size:11px;color:var(--muted);">{{ $product->unit }}</div>
                    </div>
                </div>
            @empty
                <p style="text-align:center;padding:20px;color:var(--muted);font-size:13px;">Belum ada stok di gudang manapun.</p>
            @endforelse
        </div>
    </article>

    <article class="card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
            <div style="width:40px;height:40px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">💰</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Harga Jual</div>
                <div style="font-size:22px;font-weight:700;color:var(--orange-600);">Rp {{ number_format($product->price, 0, ',', '.') }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📦</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Total Stok</div>
                <div style="font-size:18px;font-weight:700;color:var(--ink);">
                    {{ $product->warehouses->sum('pivot.stock_quantity') }} {{ $product->unit }}
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📅</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Ditambahkan</div>
                <div style="font-size:13px;font-weight:600;">{{ $product->created_at->format('d M Y') }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:40px;height:40px;background:#fdf4ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">🔄</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Terakhir diupdate</div>
                <div style="font-size:13px;font-weight:600;">{{ $product->updated_at->format('d M Y') }}</div>
            </div>
        </div>
    </article>
</div>
@endsection
