@extends('layouts.app')

@section('title', 'Detail Stok Barang - ' . $product->NM_BRG)
@section('page_title', 'Detail Stok & Harga Barang')
@section('page_description', 'Informasi lengkap data barang, harga bertingkat, satuan, dan rincian stok gudang')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $product->NM_BRG }}</h2>
        <p>SKU: <code>{{ $product->KD_BRG }}</code> · {{ $product->category ?? 'Barang' }} · Supplier: {{ $product->supplier_name ?? '—' }}</p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        {{-- <a href="{{ route('sales.order.create') }}" class="button button-primary">🛒 Buat Order</a> --}}
        <a href="{{ route('sales.stok.index') }}" class="button button-soft">← Kembali ke Stok</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<!-- Ringkasan Harga & Stok Cards -->
@php
    $marginRp = max(0, $product->price - $product->buy_price);
    $marginPr = $product->buy_price > 0 ? ($marginRp / $product->buy_price) * 100 : 0;
    $totalStok = $product->warehouses->sum('pivot.stock_quantity');
@endphp
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:20px;">
    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Harga Jual Utama</div>
        <div style="font-size:22px;font-weight:800;color:var(--orange-600);margin-top:4px;">
            Rp {{ number_format($product->price, 0, ',', '.') }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px;">per {{ $product->unit }}</div>
    </article>

    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Harga Beli / HPP</div>
        <div style="font-size:22px;font-weight:800;color:#0f766e;margin-top:4px;">
            Rp {{ number_format($product->buy_price, 0, ',', '.') }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px;">per {{ $product->unit }}</div>
    </article>

    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Margin Keuntungan</div>
        <div style="font-size:22px;font-weight:800;color:#059669;margin-top:4px;">
            Rp {{ number_format($marginRp, 0, ',', '.') }}
        </div>
        <div style="font-size:11px;color:#059669;font-weight:600;margin-top:2px;">+{{ number_format($marginPr, 1) }}%</div>
    </article>

    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Total Stok Tersedia</div>
        <div style="font-size:22px;font-weight:800;color:{{ $totalStok <= ($product->STOK_MIN ?? 0) ? '#dc2626' : '#1e293b' }};margin-top:4px;">
            {{ number_format($totalStok, 0, ',', '.') }} {{ $product->unit }}
        </div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px;">
            @if(($product->STOK_MIN ?? 0) > 0)
                Min. Stok: {{ (int) $product->STOK_MIN }} {{ $product->unit }}
            @else
                Semua gudang
            @endif
        </div>
    </article>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
    {{-- KOLOM KIRI: Identitas & Satuan & Spesifikasi --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                📦 Profil & Spesifikasi Barang
            </h3>
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted);width:38%;">Kode SKU / Barang</td>
                    <td style="padding:6px 0;"><b><code>{{ $product->KD_BRG }}</code></b></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Nama Barang</td>
                    <td style="padding:6px 0;"><b>{{ $product->NM_BRG }}</b></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Kategori / Jenis</td>
                    <td style="padding:6px 0;">{{ $product->category ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Supplier</td>
                    <td style="padding:6px 0;">{{ $product->supplier_name ?? '—' }} (<code>{{ $product->KD_SUPPL ?? '—' }}</code>)</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Status Aktif</td>
                    <td style="padding:6px 0;">
                        <span class="badge {{ ($product->STS_AKTIF ?? 'AKTIF') === 'AKTIF' ? 'badge-success' : 'badge-danger' }}">
                            {{ $product->STS_AKTIF ?? 'AKTIF' }}
                        </span>
                    </td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Lokasi Rak Gudang</td>
                    <td style="padding:6px 0;">{{ $product->RAK ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Berat Produk</td>
                    <td style="padding:6px 0;">{{ (float) $product->BERAT > 0 ? (float) $product->BERAT . ' Gram/Kg' : '—' }}</td>
                </tr>
            </table>
        </article>

        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                📏 Satuan & Konversi Bertingkat
            </h3>
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted);width:38%;">Satuan Utama (1)</td>
                    <td style="padding:6px 0;"><b>{{ $product->SATUAN1 ?: 'PCS' }}</b> (Dasar perhitungan stok)</td>
                </tr>
                @if($product->SATUAN2)
                    <tr>
                        <td style="padding:6px 0;color:var(--muted);">Satuan 2 (Menengah)</td>
                        <td style="padding:6px 0;">
                            <b>1 {{ $product->SATUAN2 }}</b> = {{ (int) $product->KAPASITAS2 }} {{ $product->SATUAN1 }}
                        </td>
                    </tr>
                @endif
                @if($product->SATUAN3)
                    <tr>
                        <td style="padding:6px 0;color:var(--muted);">Satuan 3 (Besar)</td>
                        <td style="padding:6px 0;">
                            <b>1 {{ $product->SATUAN3 }}</b> = {{ (int) $product->KAPASITAS3 }} {{ $product->SATUAN1 }}
                        </td>
                    </tr>
                @endif
            </table>
        </article>
    </div>

    {{-- KOLOM KANAN: Harga Bertingkat & Stok per Gudang --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                🏷 Daftar Harga Bertingkat
            </h3>
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted);width:45%;">Harga Jual Retail (1)</td>
                    <td style="padding:6px 0;font-weight:700;color:var(--orange-600);">
                        Rp {{ number_format($product->price, 0, ',', '.') }}
                    </td>
                </tr>
                @if($product->HARGA_JL2 > 0)
                    <tr>
                        <td style="padding:6px 0;color:var(--muted);">Harga Grosir 2</td>
                        <td style="padding:6px 0;font-weight:600;color:#0f766e;">
                            Rp {{ number_format((float)$product->HARGA_JL2, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
                @if($product->HARGA_JL3 > 0)
                    <tr>
                        <td style="padding:6px 0;color:var(--muted);">Harga Grosir 3</td>
                        <td style="padding:6px 0;font-weight:600;color:#0f766e;">
                            Rp {{ number_format((float)$product->HARGA_JL3, 0, ',', '.') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Harga Beli / HPP</td>
                    <td style="padding:6px 0;color:#475569;">
                        Rp {{ number_format($product->buy_price, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </article>

        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                🏬 Rincian Stok per Gudang
            </h3>
            @forelse($product->warehouses as $warehouse)
                <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;margin-bottom:8px;">
                    <div>
                        <div style="font-size:14px;font-weight:700;color:var(--ink);">{{ $warehouse->name }}</div>
                        <div style="font-size:11px;color:var(--muted);">Kode: <code>{{ $warehouse->code }}</code></div>
                    </div>
                    <div style="text-align:right;">
                        <div style="font-size:18px;font-weight:800;color:{{ $warehouse->pivot->stock_quantity > 0 ? 'var(--orange-600)' : 'var(--muted)' }};">
                            {{ number_format($warehouse->pivot->stock_quantity, 0, ',', '.') }}
                        </div>
                        <div style="font-size:11px;color:var(--muted);">{{ $product->unit }}</div>
                    </div>
                </div>
            @empty
                <div style="text-align:center;padding:24px;color:var(--muted);font-size:13px;background:#f8fafc;border-radius:8px;">
                    Belum ada pencatatan stok di gudang manapun.
                </div>
            @endforelse
        </article>
    </div>
</div>
@endsection
