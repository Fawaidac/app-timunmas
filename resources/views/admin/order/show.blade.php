@extends('layouts.admin')

@section('title', 'Detail Sales Order')
@section('page_title', 'Detail Sales Order')
@section('page_description', 'Informasi lengkap sales order')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $orders->order_number }}</h2>
        <p>Customer: {{ $orders->customer->name }}</p>
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

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">
    <article class="card">
        <h4 style="margin:0 0 16px;font-size:15px;font-weight:600;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Informasi Order</h4>
        
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Nomor Order</label>
                <input type="text" class="form-control" value="{{ $orders->order_number }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tanggal Order</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($orders->order_date)->format('d M Y') }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Customer</label>
                <input type="text" class="form-control" value="{{ $orders->customer->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Sales</label>
                <input type="text" class="form-control" value="{{ $orders->sales->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Jenis Pembayaran</label>
                <input type="text" class="form-control" value="{{ $orders->payment_type === 'cash' ? 'Cash' : 'Kredit' }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tempo Pembayaran</label>
                <input type="text" class="form-control" value="{{ $orders->payment_term_days }} hari" readonly style="background:#f9fafb;">
            </div>
        </div>

        <h5 style="margin:20px 0 12px;font-size:14px;font-weight:600;">Item Produk</h5>
        <div style="overflow-x:auto;">
            <table style="width:100%;font-size:13px;">
                <thead style="background:#f8fafc;border-bottom:2px solid #e2e8f0;">
                    <tr>
                        <th style="padding:10px;text-align:left;">Produk</th>
                        <th style="padding:10px;text-align:center;">Qty</th>
                        <th style="padding:10px;text-align:right;">Harga</th>
                        <th style="padding:10px;text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders->items as $item)
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:10px;">
                                <div style="font-weight:600;">{{ $item->product->name }}</div>
                                <div style="font-size:11px;color:var(--muted);">SKU: {{ $item->product->sku }}</div>
                            </td>
                            <td style="padding:10px;text-align:center;">{{ $item->quantity }} {{ $item->product->unit }}</td>
                            <td style="padding:10px;text-align:right;">Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</td>
                            <td style="padding:10px;text-align:right;font-weight:600;">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot style="background:#fff7ed;border-top:2px solid #fed7aa;">
                    <tr>
                        <td colspan="3" style="padding:12px;text-align:right;font-size:15px;font-weight:700;color:var(--ink);">Total Order:</td>
                        <td style="padding:12px;text-align:right;font-size:18px;font-weight:700;color:var(--orange-600);">
                            Rp {{ number_format($orders->total_amount, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </article>

    <article class="card">
        <div style="text-align:center;padding:20px 0;border-bottom:1px solid #f1f5f9;margin-bottom:20px;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 12px;">
                🛒
            </div>
            @if($orders->status === 'pending')
                <span style="background:#fef3c7;color:#92400e;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Pending</span>
            @elseif($orders->status === 'approved')
                <span style="background:#d1fae5;color:#065f46;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Approved</span>
            @elseif($orders->status === 'processing')
                <span style="background:#dbeafe;color:#1e40af;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Processing</span>
            @else
                <span style="background:#fee2e2;color:#991b1b;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Rejected</span>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">💰</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Total Order</div>
                <div style="font-size:18px;font-weight:700;color:var(--orange-600);">Rp {{ number_format($orders->total_amount, 0, ',', '.') }}</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📦</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Total Item</div>
                <div style="font-size:15px;font-weight:600;">{{ $orders->items->count() }} produk</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📅</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Dibuat</div>
                <div style="font-size:13px;font-weight:600;">{{ $orders->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>
    </article>
</div>
@endsection
