@extends('layouts.admin')

@section('title', 'Invoices - Admin')
@section('page_title', 'Kelola Tagihan & Invoice')
@section('page_description', 'Monitoring invoice, tanggal jatuh tempo, dan status piutang')

@section('content')
<div class="section-head">
    <h2>Manajemen Tagihan & Invoice</h2>
    <p>Pantau piutang pelanggan dan jadwal penagihan sales</p>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
            <tr><th>No. Invoice</th><th>Pelanggan</th><th>Sales</th><th>Jatuh Tempo</th><th>Total Tagihan</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <tr><td>INV-2606-1088</td><td>UD Sinar Baru</td><td>Budi S.</td><td>21 Jul 2026</td><td>Rp 5.600.000</td><td><span class="badge badge-warning">Jatuh tempo</span></td><td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Kirim Reminder</button></td></tr>
            <tr><td>INV-2606-0974</td><td>CV Maju Makmur</td><td>Citra S.</td><td>18 Jul 2026</td><td>Rp 8.250.000</td><td><span class="badge badge-danger">Terlambat</span></td><td><button class="button button-primary" style="padding: 7px 12px; font-size: 11px;">Follow Up</button></td></tr>
            <tr><td>INV-2607-0115</td><td>Toko Berkah Jaya</td><td>Andi S.</td><td>28 Jul 2026</td><td>Rp 3.450.000</td><td><span class="badge badge-success">Belum jatuh tempo</span></td><td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Detail</button></td></tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
