@extends('layouts.app')

@section('title', 'Check-in Sales - SFA Orange')
@section('page_title', 'Check-in Sales')
@section('page_description', 'Verifikasi lokasi GPS dan dokumentasikan kedatangan di outlet')

@section('content')
<div class="section-head">
    <h2>Check-in Sales</h2>
    <p>Verifikasi lokasi GPS dan dokumentasikan kedatangan di outlet.</p>
</div>

<div class="two-column">
    <article class="card">
        <div class="map-placeholder">
            <div>
                <div class="map-pin">📍</div>
                <b>Lokasi Anda Terdeteksi</b>
                <p>Jarak ke UD Sinar Baru: 38 meter</p>
            </div>
        </div>
    </article>

    <article class="card">
        <div class="card-title">
            <h3>Form Check-in</h3>
            <span class="badge badge-success">GPS Aktif</span>
        </div>

        <div class="field">
            <label>Pelanggan</label>
            <select><option>UD Sinar Baru</option></select>
        </div>

        <div class="field">
            <label>Tujuan Kunjungan</label>
            <select>
                <option>Penagihan & penerimaan pembayaran</option>
                <option>Pengambilan order</option>
                <option>Merchandising</option>
            </select>
        </div>

        <div class="field">
            <label>Catatan Awal</label>
            <textarea rows="5" placeholder="Masukkan catatan kunjungan..."></textarea>
        </div>

        <button class="button button-primary full-width">✓ Check-in Sekarang</button>
    </article>
</div>
@endsection
