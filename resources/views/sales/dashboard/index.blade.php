@extends('layouts.app')

@section('title', 'Dashboard Sales - SFA Orange')
@section('page_title', 'Dashboard Sales')
@section('page_description', 'Pantau aktivitas dan pencapaian penjualan hari ini')

@section('content')
<section class="hero">
    <div>
        <h2>Selamat pagi, Andi! 👋</h2>
        <p>Anda memiliki <b>8 kunjungan</b> terjadwal hari ini. Pastikan melakukan check-in di lokasi pelanggan.</p>
    </div>
    <div class="date-box">📅 {{ now()->translatedFormat('l, d F Y') }}</div>
</section>

<section class="stat-grid">
    @php
        $stats = [
            ['label' => 'Kunjungan Hari Ini', 'value' => '8', 'hint' => '3 sudah selesai', 'icon' => '⌖'],
            ['label' => 'Order Hari Ini', 'value' => 'Rp 18,4 jt', 'hint' => '↑ 12% dari kemarin', 'icon' => '🛒'],
            ['label' => 'Tagihan Jatuh Tempo', 'value' => '12', 'hint' => 'Rp 31,7 juta', 'icon' => '▤', 'danger' => true],
            ['label' => 'Pembayaran Dititipkan', 'value' => 'Rp 6,2 jt', 'hint' => '4 transaksi', 'icon' => '₿'],
        ];
    @endphp

    @foreach($stats as $stat)
        <article class="card stat-card">
            <div>
                <div class="stat-label">{{ $stat['label'] }}</div>
                <div class="stat-value">{{ $stat['value'] }}</div>
                <div class="stat-hint {{ !empty($stat['danger']) ? 'danger-text' : '' }}">{{ $stat['hint'] }}</div>
            </div>
            <div class="stat-icon">{{ $stat['icon'] }}</div>
        </article>
    @endforeach
</section>

<section class="two-column">
    <article class="card">
        <div class="card-title">
            <h3>Order Terbaru</h3>
            <a href="{{ route('sales.kunjungan.index') }}">Lihat semua →</a>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Pelanggan</th>
                    <th>Nilai</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <tr><td><b>SO-260721-018</b></td><td>Toko Berkah Jaya</td><td>Rp 3.450.000</td><td><span class="badge badge-success">Disetujui</span></td></tr>
                <tr><td><b>SO-260721-017</b></td><td>UD Sinar Baru</td><td>Rp 5.800.000</td><td><span class="badge badge-warning">Menunggu</span></td></tr>
                <tr><td><b>SO-260721-016</b></td><td>CV Maju Makmur</td><td>Rp 2.125.000</td><td><span class="badge badge-orange">Diproses</span></td></tr>
                <tr><td><b>SO-260721-015</b></td><td>Grosir Sejahtera</td><td>Rp 7.050.000</td><td><span class="badge badge-success">Disetujui</span></td></tr>
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <div class="card-title">
            <h3>Rute Kunjungan</h3>
            <a href="{{ route('sales.kunjungan.index') }}">Detail →</a>
        </div>

        @foreach([
            ['09', 'Toko Berkah Jaya', '09:00 • Kebayoran Baru • Selesai'],
            ['10', 'UD Sinar Baru', '10:30 • Mampang • Sedang dikunjungi'],
            ['13', 'CV Maju Makmur', '13:00 • Pancoran • Terjadwal'],
            ['15', 'Grosir Sejahtera', '15:30 • Tebet • Terjadwal'],
        ] as $route)
            <div class="route-item">
                <div class="route-dot">{{ $route[0] }}</div>
                <div>
                    <b>{{ $route[1] }}</b>
                    <p>{{ $route[2] }}</p>
                </div>
            </div>
        @endforeach
    </article>
</section>
@endsection
