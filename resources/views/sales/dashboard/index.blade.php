@extends('layouts.app')

@section('title', 'Dashboard Sales - SFA Orange')
@section('page_title', 'Dashboard Sales')
@section('page_description', 'Pantau aktivitas dan pencapaian penjualan hari ini')

@section('content')
<section class="hero">
    <div>
        <h2>{{ $greeting }}, {{ $userName }}! 👋</h2>
        <p>Anda memiliki <b>{{ $totalKunjunganHariIni }} kunjungan</b> terjadwal hari ini. 
        @if($kunjunganSelesai > 0)
            {{ $kunjunganSelesai }} sudah selesai.
        @else
            Pastikan melakukan check-in di lokasi pelanggan.
        @endif
        </p>
    </div>
    <div class="date-box">📅 {{ now()->translatedFormat('l, d F Y') }}</div>
</section>

<section class="stat-grid">
    @php
        $stats = [
            ['label' => 'Kunjungan Hari Ini', 'value' => $totalKunjunganHariIni, 'hint' => $kunjunganSelesai . ' sudah selesai', 'icon' => '⌖'],
            ['label' => 'Order Hari Ini', 'value' => 'Rp ' . number_format($totalOrderHariIni / 1000000, 1, ',', '.') . ' jt', 'hint' => ($percentageChange >= 0 ? '↑' : '↓') . ' ' . number_format(abs($percentageChange), 0) . '% dari kemarin', 'icon' => '🛒'],
            ['label' => 'Tagihan Jatuh Tempo', 'value' => $jumlahTagihanJatuhTempo, 'hint' => 'Rp ' . number_format($nilaiTagihanJatuhTempo / 1000000, 1, ',', '.') . ' juta', 'icon' => '▤', 'danger' => $jumlahTagihanJatuhTempo > 0],
            ['label' => 'Pembayaran Dititipkan', 'value' => 'Rp ' . number_format($nilaiPembayaranDititipkan / 1000000, 1, ',', '.') . ' jt', 'hint' => $jumlahPembayaranDititipkan . ' transaksi', 'icon' => '₿'],
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
                @forelse($orderTerbaru as $order)
                <tr>
                    <td><b>{{ $order->order_number }}</b></td>
                    <td>{{ $order->customer->name }}</td>
                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $order->badge_class }}">{{ $order->badge_label }}</span></td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;padding:20px;color:#999;">
                        Belum ada order
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <div class="card-title">
            <h3>Rute Kunjungan</h3>
            <a href="{{ route('sales.kunjungan.index') }}">Detail →</a>
        </div>

        @forelse($ruteKunjungan as $visit)
            <div class="route-item">
                <div class="route-dot">{{ $visit->hour }}</div>
                <div>
                    <b>{{ $visit->customer->name }}</b>
                    <p>{{ $visit->time_label }} • {{ $visit->customer->address ?? 'Lokasi' }} • {{ $visit->status_label }}</p>
                </div>
            </div>
        @empty
            <div style="text-align:center;padding:20px;color:#999;">
                Tidak ada kunjungan terjadwal hari ini
            </div>
        @endforelse
    </article>
</section>
@endsection
