@extends('layouts.app')

@section('title', 'Tagihan Sales - SFA Orange')
@section('page_title', 'Tagihan Sales')
@section('page_description', 'Pantau piutang, umur tagihan, dan jadwal penagihan pelanggan')

@section('content')
<div class="section-head">
    <h2>Tagihan Sales</h2>
    <p>Pantau piutang, umur tagihan, dan jadwal penagihan pelanggan.</p>
</div>

<section class="stat-grid">
    @php
        $stats = [
            ['Total Piutang', 'Rp ' . number_format($totalPiutang / 1000000, 1, ',', '.') . ' jt', '▤'],
            ['Jatuh Tempo Hari Ini', 'Rp ' . number_format($jatuhTempoHariIni / 1000000, 1, ',', '.') . ' jt', '!'],
            ['Lewat Jatuh Tempo', 'Rp ' . number_format($lewatJatuhTempo / 1000000, 1, ',', '.') . ' jt', '⌛'],
            ['Tertagih Bulan Ini', 'Rp ' . number_format($tertagihBulanIni / 1000000, 1, ',', '.') . ' jt', '✓'],
        ];
    @endphp
    @foreach($stats as $stat)
        <article class="card stat-card">
            <div><div class="stat-label">{{ $stat[0] }}</div><div class="stat-value">{{ $stat[1] }}</div></div>
            <div class="stat-icon">{{ $stat[2] }}</div>
        </article>
    @endforeach
</section>

<div class="toolbar" style="margin-top:24px;">
    <form action="{{ route('sales.tagihan.index') }}" method="GET" style="flex: 1; max-width: 400px;">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor tagihan atau kode customer..." onchange="this.form.submit()">
        </label>
    </form>
</div>

<div class="visit-grid" style="margin-top:16px;">
    @forelse($invoices as $invoice)
        <article class="visit-card">
            <div class="visit-top">
                <h4>{{ $invoice->invoice_number }}</h4>
                <span class="badge badge-{{ $invoice->badge_status }}">
                    {{ $invoice->badge_label }}
                </span>
            </div>

            <p style="font-weight:500;color:var(--text);margin-bottom:8px;">{{ $invoice->customer?->name ?? $invoice->KD_CUST ?? '-' }}</p>

            <div class="meta-grid">
                <div>📅 JT: {{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') : '-' }}</div>
                <div>💰 Rp {{ number_format($invoice->remaining_balance / 1000, 0, ',', '.') }}k</div>
                <div>⏱ Umur: {{ $invoice->umur_hari }} hari</div>
                <div>
                    @php
                        $statusLabels = [
                            'pending_approval' => '⏳ Menunggu',
                            'approved'         => '✓ Disetujui',
                            'rejected'         => '✗ Ditolak',
                        ];
                        $paymentStatus = $invoice->latestPayment?->status;
                    @endphp
                    {{ $paymentStatus ? ($statusLabels[$paymentStatus] ?? $paymentStatus) : '○ Belum Bayar' }}
                </div>
            </div>

            <div class="button-row">
                @php
                    $pendingPayment = $invoice->payments?->where('status', 'pending_approval')->first();
                    $rejectedPayment = $invoice->payments?->where('status', 'rejected')->first();
                @endphp
                
                @if($pendingPayment)
                    <button disabled class="button button-soft full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;background:#fff3cd;color:#856404;cursor:not-allowed;">
                        ⏳ Menunggu Konfirmasi
                    </button>
                @elseif($rejectedPayment)
                    <a href="{{ route('sales.pembayaran.index', $invoice->order_id) }}" 
                        class="button button-primary full-width" 
                        style="margin-top:12px;padding:9px;font-size:11px;text-align:center;background:#dc2626;">
                        🔄 Bayar Lagi
                    </a>
                @else
                    <a href="{{ route('sales.pembayaran.index', $invoice->order_id) }}" 
                        class="button button-primary full-width" 
                        style="margin-top:12px;padding:9px;font-size:11px;text-align:center;">
                        💰 Titip Pembayaran
                    </a>
                @endif
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📭</div>
            <p style="font-size:16px;font-weight:500;">Belum ada tagihan</p>
            <p style="font-size:14px;color:var(--muted);margin-top:4px;">Tagihan akan muncul setelah ada sales order yang disetujui</p>
        </div>
    @endforelse
</div>

@include('partials.pagination', ['paginator' => $invoices, 'itemLabel' => 'tagihan'])
@endsection
