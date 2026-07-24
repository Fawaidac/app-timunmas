@extends('layouts.admin')

@section('title', 'Reports & Analytics - Admin')
@section('page_title', 'Laporan & Analytics')
@section('page_description', 'Laporan menyeluruh performa penjualan dan operasional')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 280px;
        width: 100%;
        margin-top: 12px;
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Laporan & Analytics Business</h2>
    <p>Ringkasan performa penjualan, revenue, dan tren aktivitas seluruh tim</p>
</div>

<section class="stat-grid">
    @foreach([
        ['Total Revenue Bulan Ini', 'Rp ' . number_format($totalRevenueBulanIni ?? 0, 0, ',', '.'), $revenueGrowthHint ?? 'Bulan berjalan', '💰'],
        ['Total Order Disetujui', number_format($totalOrderDisetujui ?? 0) . ' Order', $orderHint ?? 'Target 1,000', '🛒'],
        ['Average Order Value', 'Rp ' . number_format($aov ?? 0, 0, ',', '.'), $aovGrowthHint ?? 'Per transaksi', '📈'],
        ['Kunjungan Selesai', number_format($kunjunganSelesaiPercent ?? 0, 1) . '%', 'Bulan berjalan', '✓'],
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
        <div class="chart-container">
            <canvas id="salesTrendChart"></canvas>
        </div>
    </article>
    <article class="card">
        <div class="card-title"><h3>Top 5 Performa Sales</h3></div>
        <div class="chart-container">
            <canvas id="salesPerformanceChart"></canvas>
        </div>
    </article>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Data dari Controller
        var trendLabels = @json($monthlyTrendLabels ?? []);
        var trendData = @json($monthlyTrendData ?? []);
        var salesLabels = @json($salesLabels ?? []);
        var salesData = @json($salesData ?? []);

        // Chart 1: Tren Penjualan Bulanan
        var ctxTrend = document.getElementById('salesTrendChart').getContext('2d');
        var gradientTrend = ctxTrend.createLinearGradient(0, 0, 0, 260);
        gradientTrend.addColorStop(0, 'rgba(234, 88, 12, 0.25)');
        gradientTrend.addColorStop(1, 'rgba(234, 88, 12, 0.0)');

        new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [{
                    label: 'Revenue Penjualan (Rp)',
                    data: trendData,
                    borderColor: '#ea580c',
                    borderWidth: 3,
                    backgroundColor: gradientTrend,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ea580c',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var val = context.raw || 0;
                                return 'Revenue: Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 12 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: function(val) {
                                if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' M';
                                if (val >= 1000000) return (val / 1000000).toFixed(0) + ' Jt';
                                return new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                }
            }
        });

        // Chart 2: Performa Per Sales
        var ctxSales = document.getElementById('salesPerformanceChart').getContext('2d');
        new Chart(ctxSales, {
            type: 'bar',
            data: {
                labels: salesLabels.length > 0 ? salesLabels : ['Belum ada data sales'],
                datasets: [{
                    label: 'Total Penjualan (Rp)',
                    data: salesData.length > 0 ? salesData : [0],
                    backgroundColor: '#f97316',
                    hoverBackgroundColor: '#ea580c',
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                var val = context.raw || 0;
                                return 'Penjualan: Rp ' + new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b', font: { size: 11 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            color: '#64748b',
                            font: { size: 11 },
                            callback: function(val) {
                                if (val >= 1000000000) return (val / 1000000000).toFixed(1) + ' M';
                                if (val >= 1000000) return (val / 1000000).toFixed(0) + ' Jt';
                                return new Intl.NumberFormat('id-ID').format(val);
                            }
                        }
                    }
                }
            }
        });
    });
</script>
@endpush