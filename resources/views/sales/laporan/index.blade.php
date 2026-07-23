@extends('layouts.app')

@section('title', 'Laporan Aktivitas - SFA Orange')
@section('page_title', 'Laporan Aktivitas')
@section('page_description', 'Ringkasan produktivitas, penjualan, penagihan, dan efektivitas kunjungan')

@section('content')
<div class="section-head">
    <h2>Laporan Aktivitas</h2>
    <p>Ringkasan produktivitas, penjualan, penagihan, dan efektivitas kunjungan.</p>
</div>

<section class="stat-grid">
    @foreach([
        ['Produktivitas','87%','Target 85%','↗'],
        ['Strike Rate','72%','18 dari 25 visit','◎'],
        ['Average Order','Rp 3,8 jt','↑ 8,4%','🛒'],
        ['Collection Rate','91%','Bulan berjalan','✓'],
    ] as $stat)
        <article class="card stat-card">
            <div>
                <div class="stat-label">{{ $stat[0] }}</div>
                <div class="stat-value">{{ $stat[1] }}</div>
                <div class="stat-hint">{{ $stat[2] }}</div>
            </div>
            <div class="stat-icon">{{ $stat[3] }}</div>
        </article>
    @endforeach
</section>

<div class="two-column top-gap">
    <article class="card">
        <div class="card-title"><h3>Ringkasan Mingguan</h3></div>
        <div class="chart-placeholder">Area grafik penjualan mingguan</div>
    </article>
    <article class="card">
        <div class="card-title"><h3>Komposisi Aktivitas</h3></div>
        <div class="chart-placeholder">Area grafik komposisi aktivitas</div>
    </article>
</div>
@endsection
