@extends('layouts.admin')

@section('title', 'Reports & Analytics - Admin')
@section('page_title', 'Laporan & Analytics')
@section('page_description', 'Laporan menyeluruh performa penjualan dan operasional')

@section('content')
<div class="section-head">
    <h2>Laporan & Analytics Business</h2>
    <p>Ringkasan performa penjualan, revenue, dan tren aktivitas seluruh tim</p>
</div>

<section class="stat-grid">
    @foreach([
        ['Total Revenue Bulan Ini','Rp 2,4 Miliar','↑ 8,4%','💰'],
        ['Total Order Disetujui','1,247 Order','Target 1,200','🛒'],
        ['Average Order Value','Rp 4,2 Juta','↑ 12%','📈'],
        ['Kunjungan Selesai','94.2%','Bulan berjalan','✓'],
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
        <div class="card-title"><h3>Tren Penjualan Bulanan</h3></div>
        <div class="chart-placeholder">Area grafik tren penjualan bulanan</div>
    </article>
    <article class="card">
        <div class="card-title"><h3>Performa Per Wilayah</h3></div>
        <div class="chart-placeholder">Area grafik performa per wilayah</div>
    </article>
</div>
@endsection
