@extends('layouts.admin')

@section('title', 'Dashboard Admin - Timun Mas')
@section('page_title', 'Dashboard Overview')
@section('page_description', 'Ringkasan performa sales dan operasional bisnis real-time')

@section('content')
<section class="hero" style="margin-bottom: 22px;">
    <div>
        <h2>Selamat datang kembali, Administrator! 👋</h2>
        <p>Pantau performa penjualan, pesanan masuk, dan aktivitas tim sales hari ini.</p>
    </div>
    <div class="date-box">📅 {{ now()->translatedFormat('l, d F Y') }}</div>
</section>

<section class="stat-grid" style="margin-top: 0; margin-bottom: 22px;">
    <article class="card stat-card">
        <div>
            <div class="stat-label">Total Orders</div>
            <div class="stat-value">{{ number_format($totalOrders, 0, ',', '.') }}</div>
            <div class="stat-hint">{{ $ordersGrowth >= 0 ? '↗' : '↘' }} {{ abs($ordersGrowth) }}% dari bulan lalu</div>
        </div>
        <div class="stat-icon">🛒</div>
    </article>

    <article class="card stat-card">
        <div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">Rp {{ number_format($totalRevenue / 1000000, 1, ',', '.') }}M</div>
            <div class="stat-hint">{{ $revenueGrowth >= 0 ? '↗' : '↘' }} {{ abs($revenueGrowth) }}% dari bulan lalu</div>
        </div>
        <div class="stat-icon">💰</div>
    </article>

    <article class="card stat-card">
        <div>
            <div class="stat-label">Sales Aktif</div>
            <div class="stat-value">{{ $activeSalesToday }} / {{ $totalSales }}</div>
            <div class="stat-hint">Sales beroperasi hari ini</div>
        </div>
        <div class="stat-icon">👥</div>
    </article>

    <article class="card stat-card">
        <div>
            <div class="stat-label">Pending Approval</div>
            <div class="stat-value">{{ $pendingOrders }} Order</div>
            <div class="stat-hint danger-text">Membutuhkan persetujuan</div>
        </div>
        <div class="stat-icon">⏳</div>
    </article>
</section>

<section class="two-column" style="margin-top: 0; margin-bottom: 22px;">
    <article class="card">
        <div class="card-title">
            <h3>Recent Orders</h3>
            <a href="{{ route('admin.orders') }}">View All →</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                <tr>
                    <th>No. Order</th>
                    <th>Sales</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($recentOrders as $order)
                <tr>
                    <td><b>{{ $order->order_number }}</b></td>
                    <td>{{ $order->sales->name ?? '-' }}</td>
                    <td>{{ $order->customer->name }}</td>
                    <td>Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                    <td>
                        @if($order->status === 'pending')
                            <span class="badge badge-warning">Pending</span>
                        @elseif($order->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @elseif($order->status === 'processing')
                            <span class="badge badge-orange">Processing</span>
                        @elseif($order->status === 'completed')
                            <span class="badge badge-success">Completed</span>
                        @else
                            <span class="badge badge-secondary">{{ ucfirst($order->status) }}</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:var(--muted);">Belum ada order</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>
    
    <article class="card">
        <div class="card-title">
            <h3>Top Performing Sales</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($topSales as $sales)
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--line);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar" style="width: 32px; height: 32px; font-size: 11px; background: var(--orange-100);">
                        {{ strtoupper(substr($sales->name, 0, 1)) }}{{ strtoupper(substr(explode(' ', $sales->name)[1] ?? $sales->name, 0, 1)) }}
                    </div>
                    <div>
                        <b style="font-size: 13px; display: block;">{{ $sales->name }}</b>
                        <p style="margin: 2px 0 0; font-size: 11px; color: var(--muted);">{{ $sales->email ?? '-' }}</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <b style="font-size: 13px; color: var(--orange-600);">Rp {{ number_format(($sales->total_revenue ?? 0) / 1000000, 1, ',', '.') }}M</b>
                </div>
            </div>
            @empty
            <div style="text-align:center;padding:40px;color:var(--muted);">Belum ada data penjualan bulan ini</div>
            @endforelse
        </div>
    </article>
</section>

<section class="two-column" style="grid-template-columns: 1fr 1fr; margin-top: 0;">
    <article class="card">
        <div class="card-title">
            <h3>Pending Payments</h3>
            <a href="{{ route('admin.payments') }}">View All →</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                <tr>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse($pendingPayments as $invoice)
                <tr>
                    <td>{{ $invoice->customer->name }}</td>
                    <td>Rp {{ number_format($invoice->remaining_balance, 0, ',', '.') }}</td>
                    <td>
                        @if($invoice->status === 'overdue')
                            <span class="badge badge-danger">Overdue</span>
                        @else
                            <span class="badge badge-warning">Pending</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;padding:40px;color:var(--muted);">Semua pembayaran sudah lunas</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>
    
    <article class="card">
        <div class="card-title">
            <h3>Low Stock Alert</h3>
            <a href="">View All →</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                <tr>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Warehouse</th>
                </tr>
                </thead>
                <tbody>
                @forelse($lowStockProducts as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>
                        @if($product->stock == 0)
                            <b class="danger-text">{{ $product->stock }} pcs</b>
                        @elseif($product->stock < 10)
                            <b class="danger-text">{{ $product->stock }} pcs</b>
                        @else
                            <b style="color: var(--warning);">{{ $product->stock }} pcs</b>
                        @endif
                    </td>
                    <td>{{ $product->warehouse->name ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" style="text-align:center;padding:40px;color:var(--muted);">Semua stok aman</td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
