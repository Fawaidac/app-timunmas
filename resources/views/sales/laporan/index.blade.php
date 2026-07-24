@extends('layouts.app')

@section('title', 'Laporan Aktivitas - SFA Orange')
@section('page_title', 'Laporan Aktivitas Sales')
@section('page_description', 'Ringkasan produktivitas, pencapaian order, penagihan, dan efektivitas kunjungan')

@push('styles')
<style>
    .chart-container {
        position: relative;
        height: 260px;
        width: 100%;
        margin-top: 12px;
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Laporan Aktivitas Personal</h2>
    <p>Ringkasan performa kerja, tren penjualan mingguan, dan efektivitas kunjungan Anda bulan ini.</p>
</div>

<!-- GRID STATISTIK -->
<section class="stat-grid">
    <article class="card stat-card">
        <div>
            <div class="stat-label">Produktivitas</div>
            <div class="stat-value">{{ $productivity }}%</div>
            <div class="stat-hint">{{ $completedVisits }} dari {{ $totalVisits }} visit selesai</div>
        </div>
        <div class="stat-icon">↗</div>
    </article>

    <article class="card stat-card">
        <div>
            <div class="stat-label">Strike Rate</div>
            <div class="stat-value">{{ $strikeRate }}%</div>
            <div class="stat-hint">{{ $visitWithOrderCount }} visit menghasilkan order</div>
        </div>
        <div class="stat-icon">◎</div>
    </article>

    <article class="card stat-card">
        <div>
            <div class="stat-label">Average Order</div>
            <div class="stat-value">Rp {{ number_format($averageOrder, 0, ',', '.') }}</div>
            <div class="stat-hint">Dari {{ $totalOrderCount }} sales order</div>
        </div>
        <div class="stat-icon">🛒</div>
    </article>

    <article class="card stat-card">
        <div>
            <div class="stat-label">Collection Rate</div>
            <div class="stat-value">{{ $collectionRate }}%</div>
            <div class="stat-hint">Bulan berjalan</div>
        </div>
        <div class="stat-icon">✓</div>
    </article>
</section>

<!-- SECTION GRAFIK ANALYTICS -->
<div class="two-column top-gap" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; margin-top: 24px;">
    <!-- Grafik 1: Tren Penjualan Mingguan -->
    <article class="card">
        <div class="card-title">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">Penjualan Mingguan (Bulan Ini)</h3>
        </div>
        <div class="chart-container">
            <canvas id="weeklySalesChart"></canvas>
        </div>
    </article>

    <!-- Grafik 2: Komposisi Aktivitas Kunjungan -->
    <article class="card">
        <div class="card-title">
            <h3 style="margin: 0; font-size: 16px; font-weight: 700; color: #1e293b;">Komposisi Tujuan Kunjungan</h3>
        </div>
        <div class="chart-container">
            <canvas id="purposeCompositionChart"></canvas>
        </div>
    </article>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data dari Controller
    var weeklyLabels = @json($weeklySalesLabels ?? []);
    var weeklyData = @json($weeklySalesData ?? []);
    var purposeLabels = @json($chartPurposeLabels ?? []);
    var purposeData = @json($chartPurposeData ?? []);

    // 1. Chart Penjualan Mingguan (Bar Chart)
    var ctxWeekly = document.getElementById('weeklySalesChart').getContext('2d');
    new Chart(ctxWeekly, {
        type: 'bar',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Total Penjualan (Rp)',
                data: weeklyData,
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

    // 2. Chart Komposisi Tujuan Kunjungan (Doughnut Chart)
    var ctxPurpose = document.getElementById('purposeCompositionChart').getContext('2d');
    new Chart(ctxPurpose, {
        type: 'doughnut',
        data: {
            labels: purposeLabels,
            datasets: [{
                data: purposeData,
                backgroundColor: [
                    '#f97316', // Orange (Order)
                    '#3b82f6', // Blue (Penagihan)
                    '#10b981'  // Green (Merchandising)
                ],
                borderWidth: 2,
                borderColor: '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 16,
                        font: { size: 12 },
                        color: '#475569'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            var val = context.raw || 0;
                            return ' ' + context.label + ': ' + val + ' Kunjungan';
                        }
                    }
                }
            },
            cutout: '65%'
        }
    });
});
</script>
@endpush