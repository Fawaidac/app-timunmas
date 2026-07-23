@extends('layouts.app')

@section('title', 'Tagihan Sales - SFA Orange')
@section('page_title', 'Tagihan Sales')
@section('page_description', 'Pantau piutang, umur tagihan, dan jadwal penagihan pelanggan')

@section('content')
<div class="section-head">
    <h2>Tagihan Sales</h2>
    <p>Pantau piutang, umur tagihan, dan jadwal penagihan pelanggan.</p>
</div>

<section class="stat-grid">
    @foreach([
        ['Total Piutang','Rp 84,6 jt','▤'],
        ['Jatuh Tempo Hari Ini','Rp 12,4 jt','!'],
        ['Lewat Jatuh Tempo','Rp 19,3 jt','⌛'],
        ['Tertagih Bulan Ini','Rp 52,8 jt','✓'],
    ] as $stat)
        <article class="card stat-card">
            <div><div class="stat-label">{{ $stat[0] }}</div><div class="stat-value">{{ $stat[1] }}</div></div>
            <div class="stat-icon">{{ $stat[2] }}</div>
        </article>
    @endforeach
</section>

<article class="card top-gap">
    <div class="table-responsive">
        <table>
            <thead><tr><th>No. Invoice</th><th>Pelanggan</th><th>Jatuh Tempo</th><th>Sisa Tagihan</th><th>Umur</th><th>Status</th></tr></thead>
            <tbody>
            <tr><td>INV-2606-1088</td><td>UD Sinar Baru</td><td>21 Jul 2026</td><td>Rp 5.600.000</td><td>30 hari</td><td><span class="badge badge-warning">Jatuh tempo</span></td></tr>
            <tr><td>INV-2606-0974</td><td>CV Maju Makmur</td><td>18 Jul 2026</td><td>Rp 8.250.000</td><td>33 hari</td><td><span class="badge badge-danger">Terlambat</span></td></tr>
            <tr><td>INV-2607-0115</td><td>Toko Berkah Jaya</td><td>28 Jul 2026</td><td>Rp 3.450.000</td><td>23 hari</td><td><span class="badge badge-success">Belum jatuh tempo</span></td></tr>
            </tbody>
        </table>
    </div>
</article>
@endsection
