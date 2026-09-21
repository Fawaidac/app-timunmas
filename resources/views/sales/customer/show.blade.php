@extends('layouts.app')

@section('title', 'Detail Customer - Sales')
@section('page_title', 'Detail Customer')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .detail-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px 28px;
    }
    @media(max-width:640px) { .detail-grid { grid-template-columns: 1fr; } }
    .detail-item label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: var(--muted);
        display: block;
        margin-bottom: 3px;
    }
    .detail-item .val {
        font-size: 14px;
        color: var(--ink);
        font-weight: 500;
    }
    .detail-item .val.empty { color: #cbd5e1; font-style: italic; }
    #map-show { height: 260px; border-radius: 12px; border: 1px solid var(--line); margin-top: 8px; }
    .section-sep {
        font-size: 13px;
        font-weight: 700;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: .6px;
        margin: 24px 0 14px;
        padding-bottom: 6px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $customer->NM_CUST }}</h2>
        <p><code>{{ $customer->KD_CUST }}</code> · {{ $customer->KATEGORI ?? '—' }} · {{ $customer->WILAYAH ?? '—' }}</p>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('sales.customer.edit', $customer->KD_CUST) }}" class="button button-primary">✏️ Edit</a>
        <a href="{{ route('sales.customer.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:10px 16px;border-radius:8px;margin-bottom:16px;">
        ✅ {{ session('success') }}
    </div>
@endif

<article class="card" style="max-width:860px;">

    {{-- IDENTITAS --}}
    <div class="section-sep" style="margin-top:0;">
        <span>🏢</span> Identitas Customer
    </div>
    <div class="detail-grid">
        <div class="detail-item">
            <label>Kode Customer</label>
            <div class="val"><code>{{ $customer->KD_CUST }}</code></div>
        </div>
        <div class="detail-item">
            <label>Nama Customer</label>
            <div class="val" style="font-weight:700;">{{ $customer->NM_CUST }}</div>
        </div>
        <div class="detail-item">
            <label>PIC / Penanggung Jawab</label>
            <div class="val {{ empty($customer->C_PERSON) ? 'empty' : '' }}">{{ $customer->C_PERSON ?: 'Tidak ada' }}</div>
        </div>
        <div class="detail-item">
            <label>Kategori Customer</label>
            <div class="val">
                @if($customer->KATEGORI)
                    <span class="badge badge-warning">{{ $customer->KATEGORI }}</span>
                @else
                    <span class="empty">Tidak ada</span>
                @endif
            </div>
        </div>
    </div>

    {{-- KONTAK & ALAMAT --}}
    <div class="section-sep">
        <span>📍</span> Kontak & Alamat
    </div>
    <div class="detail-grid">
        <div class="detail-item" style="grid-column:1/-1;">
            <label>Alamat Lengkap</label>
            <div class="val {{ empty($customer->ALM_CUST) ? 'empty' : '' }}">{{ $customer->ALM_CUST ?: 'Tidak ada' }}</div>
        </div>
        <div class="detail-item">
            <label>Wilayah</label>
            <div class="val">
                @if($customer->WILAYAH)
                    📍 {{ $customer->WILAYAH }}
                @else
                    <span class="empty">Tidak ada</span>
                @endif
            </div>
        </div>
        <div class="detail-item">
            <label>No. HP</label>
            <div class="val {{ empty($customer->HP) ? 'empty' : '' }}">
                @if($customer->HP)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $customer->HP) }}" target="_blank" style="color:var(--success);font-weight:600;">
                        📱 {{ $customer->HP }} (WhatsApp)
                    </a>
                @else
                    <span class="empty">Tidak ada</span>
                @endif
            </div>
        </div>
        <div class="detail-item">
            <label>Telepon</label>
            <div class="val {{ empty($customer->TELP1) ? 'empty' : '' }}">{{ $customer->TELP1 ?: 'Tidak ada' }}</div>
        </div>
        <div class="detail-item">
            <label>Email</label>
            <div class="val {{ empty($customer->E_MAIL) ? 'empty' : '' }}">
                @if($customer->E_MAIL)
                    <a href="mailto:{{ $customer->E_MAIL }}" style="color:var(--orange-600);">{{ $customer->E_MAIL }}</a>
                @else
                    <span class="empty">Tidak ada</span>
                @endif
            </div>
        </div>
    </div>

    {{-- SALES PENGELOLA --}}
    <div class="section-sep">
        <span>👤</span> Sales Pengelola
    </div>
    <div class="detail-grid">
        <div class="detail-item">
            <label>Nama Sales</label>
            <div class="val" style="font-weight:700;">{{ $customer->NM_PEG ?? $sales?->NM_PEG ?? \App\Helpers\SalesHelper::nama() }}</div>
        </div>
        <div class="detail-item">
            <label>Kode Sales</label>
            <div class="val"><code>{{ $customer->KD_PEG ?? $sales?->KD_PEG ?? \App\Helpers\SalesHelper::kdPeg() }}</code></div>
        </div>
    </div>

    {{-- LOKASI GPS --}}
    <div class="section-sep">
        <span>🗺️</span> Lokasi GPS
    </div>
    @if($customer->LATITUDE && $customer->LONGITUDE)
        <div style="font-size:13px;color:var(--muted);margin-bottom:8px;">
            Latitude: <code>{{ $customer->LATITUDE }}</code> · Longitude: <code>{{ $customer->LONGITUDE }}</code>
            &nbsp;·&nbsp;
            <a href="https://maps.google.com/?q={{ $customer->LATITUDE }},{{ $customer->LONGITUDE }}" target="_blank" style="color:var(--orange-600);font-weight:600;">
                Buka di Google Maps ↗
            </a>
        </div>
        <div id="map-show"></div>
    @else
        <div style="background:#f8fafc;border:1px solid var(--line);border-radius:10px;padding:24px;text-align:center;color:var(--muted);font-size:13px;">
            📍 Koordinat GPS belum ditentukan untuk customer ini.<br>
            <a href="{{ route('sales.customer.edit', $customer->KD_CUST) }}" class="button button-soft" style="margin-top:10px;display:inline-block;">
                Atur Lokasi Sekarang
            </a>
        </div>
    @endif

</article>
@endsection

@if($customer->LATITUDE && $customer->LONGITUDE)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const lat = {{ $customer->LATITUDE }};
    const lng = {{ $customer->LONGITUDE }};
    const map = L.map('map-show').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    L.marker([lat, lng]).addTo(map)
        .bindPopup('<b>{{ $customer->NM_CUST }}</b><br>{{ $customer->ALM_CUST ?? "" }}')
        .openPopup();
</script>
@endpush
@endif
