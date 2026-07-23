@extends('layouts.admin')

@section('title', 'Customers - Admin')
@section('page_title', 'Customer Management')
@section('page_description', 'Kelola data customer dan outlet')

@section('content')
<div class="section-head">
    <h2>Master Data Customer</h2>
    <p>Kelola informasi customer dan riwayat transaksi</p>
</div>

<div class="toolbar">
    <label class="search-box search-grow">
        <span>⌕</span>
        <input type="search" placeholder="Cari customer...">
    </label>
    <button class="button button-primary">＋ Tambah Customer</button>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
            <tr><th>Kode</th><th>Nama Customer</th><th>Alamat</th><th>Telepon</th><th>Area</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <tr>
                <td><b>CUST-001</b></td>
                <td>Toko Berkah Jaya</td>
                <td>Jl. Wijaya II No. 18</td>
                <td>021-7234567</td>
                <td>Kebayoran Baru</td>
                <td><span class="badge badge-success">Active</span></td>
                <td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Edit</button></td>
            </tr>
            <tr>
                <td><b>CUST-002</b></td>
                <td>UD Sinar Baru</td>
                <td>Jl. Bangka Raya No. 47</td>
                <td>021-7198234</td>
                <td>Mampang</td>
                <td><span class="badge badge-success">Active</span></td>
                <td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Edit</button></td>
            </tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
