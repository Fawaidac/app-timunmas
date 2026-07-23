@extends('layouts.admin')

@section('title', 'All Orders - Admin')
@section('page_title', 'Sales Orders Management')
@section('page_description', 'Monitor dan kelola semua order dari tim sales')

@section('content')
<div class="section-head">
    <h2>All Orders</h2>
    <p>Semua pesanan dari seluruh tim sales</p>
</div>

<div class="toolbar">
    <label class="search-box search-grow">
        <span>⌕</span>
        <input type="search" placeholder="Cari nomor order atau pelanggan...">
    </label>
    <button class="button button-primary">＋ Buat Order Manual</button>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
            <tr><th>No. Order</th><th>Tanggal</th><th>Sales</th><th>Pelanggan</th><th>Item</th><th>Total</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <tr>
                <td><b>SO-260721-018</b></td>
                <td>21 Jul 2026</td>
                <td>Andi S.</td>
                <td>Toko Berkah Jaya</td>
                <td>7</td>
                <td>Rp 3.450.000</td>
                <td><span class="badge badge-warning">Pending</span></td>
                <td>
                    <button class="button button-primary" style="padding: 7px 12px; font-size: 11px;">Approve</button>
                </td>
            </tr>
            <tr>
                <td><b>SO-260721-017</b></td>
                <td>21 Jul 2026</td>
                <td>Budi S.</td>
                <td>UD Sinar Baru</td>
                <td>12</td>
                <td>Rp 5.800.000</td>
                <td><span class="badge badge-success">Approved</span></td>
                <td>
                    <button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Detail</button>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
