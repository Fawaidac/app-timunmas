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
    @php
        $stats = [
            ['label' => 'Total Orders', 'value' => '1,247', 'hint' => '↗ +12% dari bulan lalu', 'icon' => '🛒'],
            ['label' => 'Total Revenue', 'value' => 'Rp 2.4M', 'hint' => '↗ +8% dari bulan lalu', 'icon' => '💰'],
            ['label' => 'Sales Aktif', 'value' => '24 / 28', 'hint' => 'Sales beroperasi hari ini', 'icon' => '👥'],
            ['label' => 'Pending Approval', 'value' => '18 Order', 'hint' => 'Membutuhkan persetujuan', 'icon' => '⏳', 'danger' => true],
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
                <tr>
                    <td><b>SO-260722-089</b></td>
                    <td>Andi S.</td>
                    <td>Toko Berkah</td>
                    <td>Rp 3.4M</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-088</b></td>
                    <td>Budi S.</td>
                    <td>UD Sinar</td>
                    <td>Rp 5.8M</td>
                    <td><span class="badge badge-success">Approved</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-087</b></td>
                    <td>Citra S.</td>
                    <td>CV Maju</td>
                    <td>Rp 2.1M</td>
                    <td><span class="badge badge-orange">Processing</span></td>
                </tr>
                </tbody>
            </table>
        </div>
    </article>
    
    <article class="card">
        <div class="card-title">
            <h3>Top Performing Sales</h3>
        </div>
        
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--line);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar" style="width: 32px; height: 32px; font-size: 11px; background: var(--orange-100);">AS</div>
                    <div>
                        <b style="font-size: 13px; display: block;">Andi Salesman</b>
                        <p style="margin: 2px 0 0; font-size: 11px; color: var(--muted);">Jakarta Selatan</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <b style="font-size: 13px; color: var(--orange-600);">Rp 420M</b>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--line);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar" style="width: 32px; height: 32px; font-size: 11px; background: var(--orange-100);">BS</div>
                    <div>
                        <b style="font-size: 13px; display: block;">Budi Sales</b>
                        <p style="margin: 2px 0 0; font-size: 11px; color: var(--muted);">Jakarta Pusat</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <b style="font-size: 13px; color: var(--orange-600);">Rp 380M</b>
                </div>
            </div>
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--line);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar" style="width: 32px; height: 32px; font-size: 11px; background: var(--orange-100);">CS</div>
                    <div>
                        <b style="font-size: 13px; display: block;">Citra Seller</b>
                        <p style="margin: 2px 0 0; font-size: 11px; color: var(--muted);">Jakarta Utara</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <b style="font-size: 13px; color: var(--orange-600);">Rp 345M</b>
                </div>
            </div>
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
                <tr><td>Toko Berkah</td><td>Rp 1.2M</td><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><td>UD Sinar</td><td>Rp 850K</td><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><td>Grosir Jaya</td><td>Rp 2.4M</td><td><span class="badge badge-danger">Overdue</span></td></tr>
                </tbody>
            </table>
        </div>
    </article>
    
    <article class="card">
        <div class="card-title">
            <h3>Low Stock Alert</h3>
            <a href="{{ route('admin.products') }}">View All →</a>
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
                <tr><td>Gula Pasir 1kg</td><td><b class="danger-text">12 pcs</b></td><td>Gudang A</td></tr>
                <tr><td>Minyak Goreng 2L</td><td><b class="danger-text">8 pcs</b></td><td>Gudang B</td></tr>
                <tr><td>Beras Premium 5kg</td><td><b style="color: var(--warning);">25 pcs</b></td><td>Gudang A</td></tr>
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
