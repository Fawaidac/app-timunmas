@extends('layouts.admin')

@section('title', 'Warehouses - Admin')
@section('page_title', 'Warehouse Management')
@section('page_description', 'Kelola gudang dan distribusi stok')

@section('content')
<div class="section-head">
    <h2>Warehouse Management</h2>
    <p>Kelola gudang dan monitoring stok per lokasi</p>
</div>

<div class="toolbar">
    <button class="button button-primary">＋ Tambah Gudang</button>
</div>

<div class="visit-grid">
    <article class="card">
        <h3 style="margin: 0 0 10px; font-size: 18px;">Gudang A - Jakarta Selatan</h3>
        <p style="margin: 0; color: var(--muted); font-size: 13px;">Jl. Raya Pasar Minggu No. 45</p>
        <div style="margin-top: 15px; display: flex; justify-content: space-between;">
            <div>
                <p style="margin: 0; font-size: 11px; color: var(--muted);">Total Items</p>
                <b style="font-size: 20px; color: var(--orange-600);">234</b>
            </div>
            <div>
                <p style="margin: 0; font-size: 11px; color: var(--muted);">Capacity</p>
                <b style="font-size: 20px; color: var(--ink);">78%</b>
            </div>
        </div>
        <button class="button button-soft full-width" style="margin-top: 15px; padding: 9px; font-size: 11px;">Lihat Detail</button>
    </article>
    
    <article class="card">
        <h3 style="margin: 0 0 10px; font-size: 18px;">Gudang B - Tangerang</h3>
        <p style="margin: 0; color: var(--muted); font-size: 13px;">Jl. Industri Raya No. 12</p>
        <div style="margin-top: 15px; display: flex; justify-content: space-between;">
            <div>
                <p style="margin: 0; font-size: 11px; color: var(--muted);">Total Items</p>
                <b style="font-size: 20px; color: var(--orange-600);">189</b>
            </div>
            <div>
                <p style="margin: 0; font-size: 11px; color: var(--muted);">Capacity</p>
                <b style="font-size: 20px; color: var(--ink);">62%</b>
            </div>
        </div>
        <button class="button button-soft full-width" style="margin-top: 15px; padding: 9px; font-size: 11px;">Lihat Detail</button>
    </article>
</div>
@endsection
