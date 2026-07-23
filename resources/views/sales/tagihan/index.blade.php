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

<article class="card top-gap">
    <div class="table-responsive">
        <table>
            <thead><tr><th>No. Invoice</th><th>Pelanggan</th><th>Jatuh Tempo</th><th>Sisa Tagihan</th><th>Umur</th><th>Status</th><th>Status Pembayaran</th></tr></thead>
            <tbody>
            @forelse($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->customer->name }}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                <td>Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
                <td>{{ $invoice->umur_hari }} hari</td>
                <td><span class="badge badge-{{ $invoice->badge_status }}">{{ $invoice->badge_label }}</span></td>
                <td>
                    @php
                        $statusLabels = [
                            'pending_approval' => 'Menunggu Persetujuan',
                            'approved'         => 'Disetujui',
                            'rejected'         => 'Ditolak',
                        ];
                        $paymentStatus = $invoice->latestPayment?->status;
                    @endphp

                    <a href="" class="button button-soft" style="padding:6px 12px;font-size:11px;">
                        💰 {{ $paymentStatus ? ($statusLabels[$paymentStatus] ?? $paymentStatus) : 'Belum Ada Bayar' }}
                    </a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:40px 20px;color:#999;">
                    📭 Belum ada tagihan
                </td>
            </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</article>
@endsection
