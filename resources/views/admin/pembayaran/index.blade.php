@extends('layouts.admin')

@section('title', 'Payments - Admin')
@section('page_title', 'Verifikasi Pembayaran')
@section('page_description', 'Konfirmasi dan verifikasi setoran pembayaran dari tim sales')

@section('content')
<div class="section-head">
    <h2>Verifikasi Setoran Pembayaran</h2>
    <p>Kelola dan konfirmasi penerimaan tunai, transfer, dan giro dari sales</p>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
            <tr><th>Pelanggan</th><th>Sales</th><th>Metode</th><th>Jumlah</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <tr><td>Toko Berkah Jaya</td><td>Andi S.</td><td>Tunai</td><td>Rp 2.500.000</td><td><span class="badge badge-success">Disetorkan</span></td><td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Kuitansi</button></td></tr>
            <tr><td>Grosir Sejahtera</td><td>Andi S.</td><td>Transfer</td><td>Rp 1.200.000</td><td><span class="badge badge-success">Terverifikasi</span></td><td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Cek Bank</button></td></tr>
            <tr><td>UD Sinar Baru</td><td>Budi S.</td><td>Giro</td><td>Rp 2.500.000</td><td><span class="badge badge-warning">Menunggu</span></td><td><button class="button button-primary" style="padding: 7px 12px; font-size: 11px;">Verifikasi</button></td></tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
