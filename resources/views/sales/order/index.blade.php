@extends('layouts.app')

@section('title', 'Sales Order')
@section('page_title', 'Daftar Sales Order')
@section('page_description', 'Kelola order dari kunjungan sales')

@section('content')
<div class="section-head">
    <h2>Order Sales</h2>
    <p>Buat dan pantau pesanan pelanggan dari kunjungan lapangan.</p>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<div class="toolbar">
    <label class="search-box">
        <span>⌕</span>
        <input type="search" placeholder="Cari nomor order atau pelanggan...">
    </label>
    <div style="display:flex;gap:12px;">
        <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft">← Kembali ke Kunjungan</a>
        <a href="{{ route('sales.order.create') }}" class="button button-primary">＋ Tambah Order Baru</a>
    </div>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Nomor Order</th>
                    <th>Customer</th>
                    <th>Tanggal</th>
                    <th>Pembayaran</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td><b>{{ $order->order_number }}</b></td>
                        <td>{{ $order->customer->name }}</td>
                        <td>{{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}</td>
                        <td>{{ ucfirst($order->payment_type) }}{{ $order->payment_type === 'credit' ? ' (' . $order->payment_term_days . 'h)' : '' }}</td>
                        <td style="text-align:right;">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td>
                            @if($order->status === 'pending')
                                <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Pending</span>
                            @elseif($order->status === 'approved')
                                <span style="background:#d1fae5;color:#065f46;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Approved</span>
                            @elseif($order->status === 'processing')
                                <span style="background:#dbeafe;color:#1e40af;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Processing</span>
                            @else
                                <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Rejected</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('sales.order.show', $order->id) }}" class="button button-soft" style="padding:6px 12px;font-size:11px;">Titip Pembayaran</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:60px 20px;text-align:center;color:var(--muted);">
                            <div style="font-size:48px;margin-bottom:12px;">🛒</div>
                            <p style="font-size:16px;font-weight:500;">Belum ada sales order</p>
                            <p style="font-size:13px;">Order akan muncul setelah dibuat dari kunjungan</p>
                        </td>
                    </tr>
                @endforelse
        </table>
    </div>
</article>
@endsection
