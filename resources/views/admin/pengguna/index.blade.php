@extends('layouts.admin')

@section('title', 'Users - Admin')
@section('page_title', 'User Management')
@section('page_description', 'Kelola pengguna sistem dan hak akses')

@section('content')
<div class="section-head">
    <h2>User Management</h2>
    <p>Kelola akun pengguna, peran (role), dan wilayah operasional</p>
</div>

<div class="toolbar">
    <label class="search-box search-grow">
        <span>⌕</span>
        <input type="search" placeholder="Cari pengguna...">
    </label>
    <button class="button button-primary">＋ Tambah User</button>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
            <tr><th>Nama</th><th>Email</th><th>Role</th><th>Wilayah</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            <tr>
                <td><b>Andi Salesman</b></td>
                <td>andi@example.com</td>
                <td><span class="badge badge-orange">Sales</span></td>
                <td>Jakarta Selatan</td>
                <td><span class="badge badge-success">Active</span></td>
                <td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Edit</button></td>
            </tr>
            <tr>
                <td><b>Admin Master</b></td>
                <td>admin@example.com</td>
                <td><span class="badge badge-success">Admin</span></td>
                <td>Pusat</td>
                <td><span class="badge badge-success">Active</span></td>
                <td><button class="button button-soft" style="padding: 7px 12px; font-size: 11px;">Edit</button></td>
            </tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
