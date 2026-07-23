@extends('layouts.app')

@section('title', 'Titip Pembayaran - SFA Orange')
@section('page_title', 'Titip Pembayaran')
@section('page_description', 'Catat penerimaan uang atau giro dari pelanggan dan proses serah terima ke kasir')

@section('content')
<div class="section-head">
    <h2>Titip Pembayaran Sales</h2>
    <p>Catat penerimaan uang/giro dari pelanggan dan proses serah terima ke kasir.</p>
</div>

<div class="two-column">
    <article class="card">
        <div class="card-title"><h3>Input Titip Pembayaran</h3></div>

        <div class="form-grid">
            <div class="field"><label>Pelanggan</label><select><option>UD Sinar Baru</option><option>CV Maju Makmur</option></select></div>
            <div class="field"><label>No. Invoice</label><select><option>INV-2606-1088</option></select></div>
            <div class="field"><label>Metode Pembayaran</label><select><option>Tunai</option><option>Transfer</option><option>Giro</option></select></div>
            <div class="field"><label>Jumlah Diterima</label><input type="text" value="5.600.000"></div>
            <div class="field"><label>Nomor Referensi</label><input type="text" placeholder="Nomor giro/transfer"></div>
            <div class="field"><label>Foto Bukti</label><input type="file"></div>
        </div>

        <button class="button button-primary top-gap-small">Simpan Titipan Pembayaran</button>
    </article>

    <article class="card">
        <div class="card-title"><h3>Ringkasan Hari Ini</h3></div>

        @foreach([
            ['1','Rp 2.500.000 • Tunai','Toko Berkah Jaya • Sudah disetor'],
            ['2','Rp 1.200.000 • Transfer','Grosir Sejahtera • Terverifikasi'],
            ['3','Rp 2.500.000 • Giro','UD Sinar Baru • Menunggu verifikasi'],
        ] as $payment)
            <div class="route-item">
                <div class="route-dot">{{ $payment[0] }}</div>
                <div><b>{{ $payment[1] }}</b><p>{{ $payment[2] }}</p></div>
            </div>
        @endforeach
    </article>
</div>
@endsection
