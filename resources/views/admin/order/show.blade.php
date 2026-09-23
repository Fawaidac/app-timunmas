@extends('layouts.admin')

@section('title', 'Detail Nota Penjualan - Sales Canvass')
@section('page_title', 'Detail Nota Penjualan')
@section('page_description', 'Informasi transaksi nota penjualan Sales Canvass')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $order->order_number }}</h2>
        <p>Customer: {{ $order->customer->name ?? $order->KD_CUST ?? '-' }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="javascript:history.back()" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">
    <article class="card">
        <h4 style="margin:0 0 16px;font-size:15px;font-weight:600;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Informasi Transaksi & Item Produk</h4>
        
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Nomor Nota</label>
                <input type="text" class="form-control" value="{{ $order->order_number }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tanggal Transaksi</label>
                <input type="text" class="form-control" value="{{ $order->order_date ? \Carbon\Carbon::parse($order->order_date)->format('d M Y') : '-' }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Customer</label>
                <input type="text" class="form-control" value="{{ $order->customer->name ?? $order->KD_CUST ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Sales</label>
                <input type="text" class="form-control" value="{{ $order->sales->name ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
        </div>

        <h5 style="margin:20px 0 12px;font-size:14px;font-weight:600;">Item Barang</h5>
        <div style="overflow-x:auto;">
            <table style="width:100%;font-size:13px;">
                <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <tr>
                        <th style="padding:10px;text-align:left;">Produk</th>
                        <th style="padding:10px;text-align:center;">Satuan</th>
                        <th style="padding:10px;text-align:center;">Qty Terjual</th>
                        <th style="padding:10px;text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px;">
                                <div style="font-weight:600;">{{ $item->NM_BRG ?? $item->product->name ?? '-' }}</div>
                                <div style="font-size:11px;color:var(--muted);">SKU: {{ $item->KD_BRG ?? $item->product->sku ?? '-' }}</div>
                            </td>
                            <td style="padding:10px;text-align:center;">
                                <span style="display:inline-block;background:#f1f5f9;border-radius:6px;padding:2px 10px;font-weight:600;font-size:12px;">{{ $item->SATUAN ?? '-' }}</span>
                            </td>
                            <td style="padding:10px;text-align:center;font-weight:700;">{{ $item->quantity }}</td>
                            <td style="padding:10px;text-align:right;font-weight:600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background:#fff7ed;border-top:2px solid #fed7aa;">
                    <tr>
                        <td colspan="3" style="padding:12px;text-align:right;font-size:15px;font-weight:700;color:var(--ink);">Total Nota:</td>
                        <td style="padding:12px;text-align:right;font-size:18px;font-weight:700;color:var(--orange-600);">
                            Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </article>

    <article class="card">
        <div style="text-align:center;padding:20px 0;border-bottom:1px solid #f1f5f9;margin-bottom:20px;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 12px;">
                🧾
            </div>
            @if($order->JNS_BYR === 'TUNAI')
                <span style="background:#d1fae5;color:#065f46;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">💵 Nota Penjualan Tunai (LUNAS)</span>
            @else
                <span style="background:#fef3c7;color:#92400e;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">⏳ Nota Penjualan Kredit (Tempo 7 Hari)</span>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">💰</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Total Order</div>
                <div style="font-size:18px;font-weight:700;color:var(--orange-600);">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📦</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Total Item</div>
                <div style="font-size:15px;font-weight:600;">{{ $order->items->count() }} produk</div>
            </div>
        </div>
    </article>
</div>
@endsection
