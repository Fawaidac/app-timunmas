@extends('layouts.admin')

@section('title', 'Detail Customer - Admin')
@section('page_title', 'Detail Customer')
@section('page_description', 'Informasi lengkap customer')

@push('styles')
@if($customer->latitude && $customer->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-preview {
        height: 250px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        margin-top: 12px;
        z-index: 1;
    }
</style>
@endif
@endpush

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $customer->name }}</h2>
        <p>Kode: {{ $customer->code }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.customers.edit', $customer->id) }}" class="button button-soft">✏ Edit</a>
        <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Yakin hapus customer ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="button" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;padding:8px 16px;cursor:pointer;">🗑 Hapus</button>
        </form>
        <a href="{{ route('admin.customers.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;">
    <article class="card">
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Kode Customer</label>
                <input type="text" class="form-control" value="{{ $customer->code }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Nama Customer</label>
                <input type="text" class="form-control" value="{{ $customer->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field" style="grid-column:1/-1;">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Alamat</label>
                <textarea class="form-control" rows="3" readonly style="background:#f9fafb;">{{ $customer->address ?? '-' }}</textarea>
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Telepon</label>
                <input type="text" class="form-control" value="{{ $customer->phone ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Email</label>
                <input type="text" class="form-control" value="{{ $customer->email ?? '-' }}" readonly style="background:#f9fafb;">
            </div>
        </div>

        @if($customer->latitude && $customer->longitude)
            <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <h4 style="margin:0;font-size:14px;font-weight:600;color:var(--ink);">📍 Peta Lokasi Customer</h4>
                    <a href="https://www.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank" class="button button-soft" style="font-size:11px;padding:4px 10px;">
                        ↗ Buka di Google Maps
                    </a>
                </div>
                <div id="map-preview"></div>
            </div>
        @endif
    </article>

    <article class="card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
            <div style="width:40px;height:40px;background:#fff7ed;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">💳</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Total Piutang</div>
                <div style="font-size:20px;font-weight:700;color:{{ $customer->current_debt > 0 ? '#c2410c' : '#065f46' }};">
                    Rp {{ number_format($customer->current_debt, 0, ',', '.') }}
                </div>
            </div>
        </div>
        
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0fdf4;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📍</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Koordinat GPS</div>
                @if($customer->latitude && $customer->longitude)
                    <div style="font-size:12px;font-weight:600;">{{ $customer->latitude }}, {{ $customer->longitude }}</div>
                @else
                    <div style="font-size:12px;color:var(--muted);font-style:italic;">Belum diatur</div>
                @endif
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📅</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Terdaftar</div>
                <div style="font-size:13px;font-weight:600;">{{ $customer->created_at->format('d M Y') }}</div>
            </div>
        </div>
    </article>
</div>
@endsection

@push('scripts')
@if($customer->latitude && $customer->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var lat = {{ $customer->latitude }};
        var lng = {{ $customer->longitude }};

        var map = L.map('map-preview').setView([lat, lng], 15);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        L.marker([lat, lng]).addTo(map)
            .bindPopup("<b>{{ $customer->name }}</b><br>{{ $customer->address }}")
            .openPopup();
    });
</script>
@endif
@endpush
