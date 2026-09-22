@extends('layouts.app')

@section('title', 'Tagihan Sales - SFA Orange')
@section('page_title', 'Tagihan Sales')
@section('page_description', 'Pantau piutang, order tempo, dan jadwal penagihan pelanggan')

@section('content')
<div class="section-head">
    <h2>Tagihan Sales</h2>
    <p>Pantau piutang faktur, order kredit tempo 7 hari, dan kelola titip pembayaran pelanggan.</p>
</div>

<section class="stat-grid">
    @php
        $stats = [
            ['Total Piutang & Order', 'Rp ' . number_format($totalPiutang / 1000000, 1, ',', '.') . ' jt', '▤'],
            ['Jatuh Tempo Hari Ini', 'Rp ' . number_format($jatuhTempoHariIni / 1000000, 1, ',', '.') . ' jt', '!'],
            ['Lewat Jatuh Tempo', 'Rp ' . number_format($lewatJatuhTempo / 1000000, 1, ',', '.') . ' jt', '⌛'],
            ['Tertagih Bulan Ini', 'Rp ' . number_format($tertagihBulanIni / 1000000, 1, ',', '.') . ' jt', '✓'],
        ];
    @endphp
    @foreach($stats as $stat)
        <article class="card stat-card">
            <div><div class="stat-label">{{ $stat[0] }}</div><div class="stat-value">{{ $stat[1] }}</div></div>
            <div class="stat-icon">{{ $stat[2] }}</div>
        </article>
    @endforeach
</section>

<!-- Filter Tabs & Search Bar -->
<div class="toolbar" style="margin-top:24px; display:flex; flex-wrap:wrap; justify-content:space-between; align-items:center; gap:12px;">
    <div style="display:flex; gap:8px; flex-wrap:wrap;">
        <a href="{{ route('sales.tagihan.index', array_merge(request()->query(), ['tab' => 'all', 'page' => 1])) }}" 
           class="button {{ $tab === 'all' ? 'button-primary' : 'button-soft' }}" style="padding:7px 14px; font-size:12px; border-radius:20px;">
           Semua Tagihan ({{ $counts['all'] }})
        </a>
        <a href="{{ route('sales.tagihan.index', array_merge(request()->query(), ['tab' => 'order', 'page' => 1])) }}" 
           class="button {{ $tab === 'order' ? 'button-primary' : 'button-soft' }}" style="padding:7px 14px; font-size:12px; border-radius:20px;">
           📦 Order Baru ({{ $counts['order'] }})
        </a>
        <a href="{{ route('sales.tagihan.index', array_merge(request()->query(), ['tab' => 'invoice', 'page' => 1])) }}" 
           class="button {{ $tab === 'invoice' ? 'button-primary' : 'button-soft' }}" style="padding:7px 14px; font-size:12px; border-radius:20px;">
           📑 Faktur Piutang ({{ $counts['invoice'] }})
        </a>
        <a href="{{ route('sales.tagihan.index', array_merge(request()->query(), ['tab' => 'overdue', 'page' => 1])) }}" 
           class="button {{ $tab === 'overdue' ? 'button-primary' : 'button-soft' }}" style="padding:7px 14px; font-size:12px; border-radius:20px;">
           ⚠️ Jatuh Tempo ({{ $counts['overdue'] }})
        </a>
    </div>

    <form action="{{ route('sales.tagihan.index') }}" method="GET" style="flex: 1; min-width: 260px; max-width: 380px;">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nomor order, faktur, atau customer..." onchange="this.form.submit()">
        </label>
    </form>
</div>

<div class="visit-grid" style="margin-top:16px;">
    @forelse($items as $item)
        <article class="visit-card" style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 16px; background: #fff; display:flex; flex-direction:column; justify-content:space-between; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
            <div>
                <div class="visit-top" style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px;">
                    <div>
                        <div style="display:flex; align-items:center; gap:6px; margin-bottom:2px;">
                            <span style="font-size:11px; font-weight:700; padding:2px 6px; border-radius:4px; background:{{ $item->_type === 'order' ? '#fff7ed; color:#c2410c;' : '#eff6ff; color:#1d4ed8;' }}">
                                {{ $item->_type_label }}
                            </span>
                            <h4 style="margin:0; font-size:14px; font-weight:700; color:#1e293b;">{{ $item->_nomor }}</h4>
                        </div>
                    </div>
                    <span class="badge badge-{{ $item->_badge_status }}" style="font-size:11px;">
                        {{ $item->_badge_label }}
                    </span>
                </div>

                <p style="font-weight:600; color:#1e293b; margin:6px 0 12px; font-size:14px; display:flex; align-items:center; gap:6px;">
                    <span>🏢</span> {{ $item->_nama_customer }}
                    @if($item->_kode_customer)
                        <span style="font-size:12px; color:#64748b; font-weight:normal;">({{ $item->_kode_customer }})</span>
                    @endif
                </p>

                <div class="meta-grid" style="display:grid; grid-template-columns:1fr 1fr; gap:8px; font-size:12px; background:#f8fafc; padding:10px 12px; border-radius:8px; border:1px solid #f1f5f9;">
                    <div>📅 Tgl: {{ $item->_tanggal ? ($item->_tanggal instanceof \Carbon\Carbon ? $item->_tanggal->format('d M Y') : \Carbon\Carbon::parse($item->_tanggal)->format('d M Y')) : '-' }}</div>
                    <div>⏰ JT: <strong style="color:{{ $item->_is_overdue ? '#b91c1c' : '#334155' }};">{{ $item->_due_date ? ($item->_due_date instanceof \Carbon\Carbon ? $item->_due_date->format('d M Y') : \Carbon\Carbon::parse($item->_due_date)->format('d M Y')) : '-' }}</strong></div>
                    <div>💰 Sisa: <strong style="color:#ea580c;">Rp {{ number_format($item->_sisa, 0, ',', '.') }}</strong></div>
                    <div>⏱ Umur: {{ $item->_umur_hari }} hari</div>
                    <div style="grid-column: 1 / -1; margin-top:2px; padding-top:4px; border-top:1px dashed #e2e8f0;">
                        @if($item->_pending_payment)
                            <span style="color:#b45309; font-weight:600;">⏳ Titipan: Rp {{ number_format($item->_pending_payment->JUMLAH, 0, ',', '.') }} (Pending Approval)</span>
                        @elseif($item->_rejected_payment)
                            <span style="color:#dc2626; font-weight:600;">✗ Pembayaran Ditolak (Silakan Bayar Lagi)</span>
                        @elseif($item->_latest_payment && $item->_latest_payment->STATUS === 'approved')
                            <span style="color:#16a34a; font-weight:600;">✓ Pembayaran Terakhir Disetujui</span>
                        @else
                            <span style="color:#64748b;">○ Belum Ada Titipan Bayar</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="button-row" style="margin-top:14px;">
                @if($item->_pending_payment)
                    <button disabled class="button button-soft full-width" style="padding:9px; font-size:12px; text-align:center; background:#fff3cd; color:#856404; cursor:not-allowed; width:100%; border-radius:8px;">
                        ⏳ Menunggu Konfirmasi Admin
                    </button>
                @elseif($item->_rejected_payment)
                    <a href="{{ route('sales.pembayaran.index', $item->_nomor) }}" 
                        class="button button-primary full-width" 
                        style="padding:9px; font-size:12px; text-align:center; background:#dc2626; width:100%; border-radius:8px; text-decoration:none; display:block;">
                        🔄 Bayar Lagi (Ditolak)
                    </a>
                @elseif($item->_sisa > 0.005)
                    <a href="{{ route('sales.pembayaran.index', $item->_nomor) }}" 
                        class="button button-primary full-width" 
                        style="padding:9px; font-size:12px; text-align:center; width:100%; border-radius:8px; text-decoration:none; display:block;">
                        💰 Titip Pembayaran
                    </a>
                @else
                    <button disabled class="button button-soft full-width" style="padding:9px; font-size:12px; text-align:center; background:#dcfce7; color:#166534; width:100%; border-radius:8px;">
                        ✓ Sudah Lunas
                    </button>
                @endif
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1; text-align:center; padding:60px 0; color:var(--muted);">
            <div style="font-size:48px; margin-bottom:12px;">📭</div>
            <p style="font-size:16px; font-weight:600; color:#334155;">Belum ada tagihan {{ $tab !== 'all' ? 'pada kategori ini' : '' }}</p>
            <p style="font-size:13px; color:#64748b; margin-top:4px;">Sales order baru dan faktur penjualan yang memiliki sisa tagihan akan tampil di sini.</p>
        </div>
    @endforelse
</div>

@include('partials.pagination', ['paginator' => $items, 'itemLabel' => 'tagihan'])
@endsection

