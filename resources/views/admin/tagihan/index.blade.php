@extends('layouts.admin')

@section('title', 'Invoices - Admin')
@section('page_title', 'Kelola Tagihan & Invoice')
@section('page_description', 'Monitoring invoice, tanggal jatuh tempo, dan status piutang')

@section('content')
<div class="section-head">
    <h2>Manajemen Tagihan & Invoice</h2>
    <p>Pantau piutang pelanggan dan jadwal penagihan sales</p>
</div>

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px;">
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;font-size:20px;">💰</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Total Piutang</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">Rp {{ number_format($totalPiutang, 0, ',', '.') }}</h3>
            </div>
        </div>
    </article>
    
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#FEE2E2;display:flex;align-items:center;justify-content:center;font-size:20px;">⏰</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Jatuh Tempo Hari Ini</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">Rp {{ number_format($jatuhTempoHariIni, 0, ',', '.') }}</h3>
            </div>
        </div>
    </article>
    
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#FECACA;display:flex;align-items:center;justify-content:center;font-size:20px;">🚨</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Lewat Jatuh Tempo</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">Rp {{ number_format($lewatJatuhTempo, 0, ',', '.') }}</h3>
            </div>
        </div>
    </article>
    
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;font-size:20px;">✅</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Tertagih Bulan Ini</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">Rp {{ number_format($tertagihBulanIni, 0, ',', '.') }}</h3>
            </div>
        </div>
    </article>
</div>

<!-- Status Summary -->
<div style="display:flex;gap:16px;margin-bottom:16px;flex-wrap:wrap;">
    <div style="background:#FEF3C7;padding:8px 16px;border-radius:8px;font-size:13px;">
        <b>{{ $invoiceByStatus['unpaid'] }}</b> Belum Dibayar
    </div>
    {{-- <div style="background:#DBEAFE;padding:8px 16px;border-radius:8px;font-size:13px;">
        <b>{{ $invoiceByStatus['partially_paid'] }}</b> Dibayar Sebagian
    </div> --}}
    <div style="background:#D1FAE5;padding:8px 16px;border-radius:8px;font-size:13px;">
        <b>{{ $invoiceByStatus['paid'] }}</b> Lunas
    </div>
    <div style="background:#FEE2E2;padding:8px 16px;border-radius:8px;font-size:13px;">
        <b>{{ $invoiceByStatus['overdue'] }}</b> Terlambat
    </div>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Invoice</th>
                    <th>Pelanggan</th>
                    <th>Sales</th>
                    <th>Tanggal Invoice</th>
                    <th>Jatuh Tempo</th>
                    <th>Umur (hari)</th>
                    <th>Total</th>
                    <th>Sisa Tagihan</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $invoice)
                    <tr>
                        <td><b>{{ $invoice->invoice_number }}</b></td>
                        <td>{{ $invoice->customer->name }}</td>
                        <td>{{ $invoice->order->sales->name ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d M Y') }}</td>
                        <td>{{ $invoice->umur_hari }} hari</td>
                        <td>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                        <td>Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
                        <td>
                            <a href="" class="button button-soft" style="padding:6px 12px;font-size:11px;">{{  $invoice->badge_label  }}</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:60px 20px;text-align:center;color:var(--muted);">
                            <div style="font-size:48px;margin-bottom:12px;">📄</div>
                            <p style="font-size:16px;font-weight:500;">Belum ada invoice</p>
                            <p style="font-size:13px;">Invoice akan muncul setelah order dibuat</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>
@endsection
