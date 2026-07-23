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
                    <td>Toko Berkah Jaya</td>
                    <td>Rp 3.450.000</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-088</b></td>
                    <td>Budi S.</td>
                    <td>UD Sinar Baru</td>
                    <td>Rp 5.800.000</td>
                    <td><span class="badge badge-success">Approved</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-087</b></td>
                    <td>Citra S.</td>
                    <td>CV Maju Makmur</td>
                    <td>Rp 2.125.000</td>
                    <td><span class="badge badge-orange">Processing</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-086</b></td>
                    <td>Deni R.</td>
                    <td>Grosir Sejahtera</td>
                    <td>Rp 7.400.000</td>
                    <td><span class="badge badge-success">Approved</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-085</b></td>
                    <td>Eka P.</td>
                    <td>Toko Rezeki Abadi</td>
                    <td>Rp 1.850.000</td>
                    <td><span class="badge badge-warning">Pending</span></td>
                </tr>
                <tr>
                    <td><b>SO-260722-084</b></td>
                    <td>Fahmi H.</td>
                    <td>Minimarket Sumber Makmur</td>
                    <td>Rp 4.200.000</td>
                    <td><span class="badge badge-success">Approved</span></td>
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
            
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--line);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar" style="width: 32px; height: 32px; font-size: 11px; background: var(--orange-100);">DR</div>
                    <div>
                        <b style="font-size: 13px; display: block;">Deni Rahmansyah</b>
                        <p style="margin: 2px 0 0; font-size: 11px; color: var(--muted);">Bekasi & Depok</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <b style="font-size: 13px; color: var(--orange-600);">Rp 310M</b>
                </div>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid var(--line);">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div class="avatar" style="width: 32px; height: 32px; font-size: 11px; background: var(--orange-100);">EP</div>
                    <div>
                        <b style="font-size: 13px; display: block;">Eka Pratama</b>
                        <p style="margin: 2px 0 0; font-size: 11px; color: var(--muted);">Tangerang Kota</p>
                    </div>
                </div>
                <div style="text-align: right;">
                    <b style="font-size: 13px; color: var(--orange-600);">Rp 295M</b>
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
                <tr><td>Toko Berkah Jaya</td><td>Rp 1.250.000</td><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><td>UD Sinar Baru</td><td>Rp 850.000</td><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><td>Grosir Sejahtera</td><td>Rp 2.400.000</td><td><span class="badge badge-danger">Overdue</span></td></tr>
                <tr><td>CV Maju Makmur</td><td>Rp 3.150.000</td><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><td>Toko Rezeki Abadi</td><td>Rp 920.000</td><td><span class="badge badge-warning">Pending</span></td></tr>
                <tr><td>Warung Makan Bu Ani</td><td>Rp 1.780.000</td><td><span class="badge badge-danger">Overdue</span></td></tr>
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
                <tr><td>Gula Pasir 1kg</td><td><b class="danger-text">12 pcs</b></td><td>Gudang Jakarta</td></tr>
                <tr><td>Minyak Goreng 2L</td><td><b class="danger-text">8 pcs</b></td><td>Gudang Bekasi</td></tr>
                <tr><td>Beras Premium 5kg</td><td><b style="color: var(--warning);">25 pcs</b></td><td>Gudang Jakarta</td></tr>
                <tr><td>Kopi Bubuk Robusta 250g</td><td><b style="color: var(--warning);">14 pcs</b></td><td>Gudang Bekasi</td></tr>
                <tr><td>Susu UHT Full Cream 1L</td><td><b class="danger-text">0 pcs</b></td><td>Gudang Jakarta</td></tr>
                <tr><td>Teh Celup Melati 25s</td><td><b style="color: var(--warning);">18 pcs</b></td><td>Gudang Tangerang</td></tr>
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
