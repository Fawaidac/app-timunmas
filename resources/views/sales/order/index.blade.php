@extends('layouts.app')

@section('title', 'Order Sales - SFA Orange')
@section('page_title', 'Order Sales')
@section('page_description', 'Buat dan pantau pesanan pelanggan dari kunjungan lapangan')

@section('content')
<div class="section-head">
    <h2>Order Sales</h2>
    <p>Buat dan pantau pesanan pelanggan dari kunjungan lapangan.</p>
</div>

<div class="toolbar">
    <label class="search-box">
        <span>⌕</span>
        <input type="search" placeholder="Cari nomor order atau pelanggan...">
    </label>
    <button class="button button-primary">＋ Buat Order Baru</button>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
            <tr><th>No. Order</th><th>Tanggal</th><th>Pelanggan</th><th>Item</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <tr><td><b>SO-260721-018</b></td><td>21 Jul 2026</td><td>Toko Berkah Jaya</td><td>7</td><td>Rp 3.450.000</td><td><span class="badge badge-success">Disetujui</span></td><td><a href="{{ route('pembayaran') }}" class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Titip Pembayaran</a></td></tr>
            <tr><td><b>SO-260721-017</b></td><td>21 Jul 2026</td><td>UD Sinar Baru</td><td>12</td><td>Rp 5.800.000</td><td><span class="badge badge-warning">Menunggu</span></td><td><a href="{{ route('pembayaran') }}" class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Titip Pembayaran</a></td></tr>
            <tr><td><b>SO-260721-016</b></td><td>21 Jul 2026</td><td>CV Maju Makmur</td><td>4</td><td>Rp 2.125.000</td><td><span class="badge badge-orange">Diproses</span></td><td><a href="{{ route('pembayaran') }}" class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Titip Pembayaran</a></td></tr>
            <tr><td><b>SO-260720-089</b></td><td>20 Jul 2026</td><td>Grosir Sejahtera</td><td>15</td><td>Rp 7.050.000</td><td><span class="badge badge-danger">Revisi</span></td><td><a href="{{ route('pembayaran') }}" class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Titip Pembayaran</a></td></tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
