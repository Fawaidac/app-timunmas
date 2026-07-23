@extends('layouts.admin')

@section('title', 'Detail Kunjungan')
@section('page_title', 'Detail Kunjungan')
@section('page_description', 'Informasi lengkap kunjungan sales')

{{-- Import Leaflet CSS --}}
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
@endpush

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $visit->customer->name }}</h2>
        <p>Kunjungan: {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="javascript:history.back()" class="button button-soft">← Kembali</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px; align-items: start;">
    
    <!-- CARD 1 -->
    <article class="card">
        <h4 style="margin:0 0 16px;font-size:15px;font-weight:600;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Informasi Kunjungan</h4>
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Customer</label>
                <input type="text" class="form-control" value="{{ $visit->customer->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Kode Customer</label>
                <input type="text" class="form-control" value="{{ $visit->customer->code }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tanggal Kunjungan</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tujuan</label>
                <input type="text" class="form-control" value="{{ ucfirst($visit->purpose) }}" readonly style="background:#f9fafb;">
            </div>
        </div>

        @if($visit->checkin_time)
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-bottom:20px;">
                <h5 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#065f46;">✓ Check-in Berhasil</h5>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:#166534;margin-bottom:2px;">Waktu Check-in</div>
                        <div style="font-size:14px;font-weight:600;color:#065f46;">{{ \Carbon\Carbon::parse($visit->checkin_time)->format('H:i:s') }}</div>
                    </div>
                    @if($visit->distance_meters)
                        <div>
                            <div style="font-size:11px;color:#166534;margin-bottom:2px;">Jarak dari Customer</div>
                            <div style="font-size:14px;font-weight:600;color:#065f46;">{{ round($visit->distance_meters) }} meter</div>
                        </div>
                    @endif
                    <div style="grid-column:1/-1;">
                        <div style="font-size:11px;color:#166534;margin-bottom:2px;">Koordinat GPS</div>
                        <div style="font-size:12px;color:#065f46;">{{ $visit->checkin_latitude }}, {{ $visit->checkin_longitude }}</div>
                    </div>
                </div>
            </div>
        @endif

        @if($visit->order)
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px;margin-bottom:20px;">
                <h5 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#c2410c;">🛒 Sales Order Dibuat</h5>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:#9a3412;margin-bottom:2px;">Nomor Order</div>
                        <div style="font-size:14px;font-weight:700;color:#c2410c;">{{ $visit->order->order_number }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#9a3412;margin-bottom:2px;">Total</div>
                        <div style="font-size:14px;font-weight:700;color:#c2410c;">Rp {{ number_format($visit->order->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <div style="font-size:11px;color:#9a3412;margin-bottom:2px;">Status</div>
                        <span style="background:#fed7aa;color:#9a3412;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                            {{ ucfirst($visit->order->status) }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        @if($visit->notes)
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Catatan</label>
                <textarea class="form-control" rows="3" readonly style="background:#f9fafb;">{{ $visit->notes }}</textarea>
            </div>
        @endif
    </article>

    <!-- CARD 2 (MAPS) -->
    <article class="card">
        <h4 style="margin:0 0 16px;font-size:15px;font-weight:600;padding-bottom:12px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <span>📍 Peta Lokasi Kunjungan</span>
            @if($visit->status === 'scheduled')
                <span style="background:#fef3c7;color:#92400e;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Dijadwalkan</span>
            @elseif($visit->status === 'in_progress')
                <span style="background:#dbeafe;color:#1e40af;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Berlangsung</span>
            @elseif($visit->status === 'completed')
                <span style="background:#d1fae5;color:#065f46;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Selesai</span>
            @else
                <span style="background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">Batal</span>
            @endif
        </h4>

        <!-- Element Peta OpenStreetMap -->
        <div id="visitMap" style="width: 100%; height: 320px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 16px; z-index: 1;"></div>

        <!-- Legend / Keterangan Warna Pin -->
        <div style="display:flex; gap:16px; font-size:12px; color:var(--muted); background:#f8fafc; padding:10px 12px; border-radius:8px;">
            <div style="display:flex; align-items:center; gap:6px;">
                <span style="width:12px; height:12px; background:#2563eb; border-radius:50%; display:inline-block;"></span>
                <b>Customer:</b> {{ $visit->customer->name }}
            </div>
            @if($visit->checkin_latitude)
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="width:12px; height:12px; background:#dc2626; border-radius:50%; display:inline-block;"></span>
                    <b>Posisi Check-in</b>
                </div>
            @endif
        </div>
    </article>
</div>
@endsection

{{-- Script OpenStreetMap via Leaflet JS --}}
@push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // LatLong Customer
            const custLat = {{ $visit->customer->latitude ?? -6.200000 }};
            const custLng = {{ $visit->customer->longitude ?? 106.816666 }};

            // LatLong Check-in User
            const checkinLat = {{ $visit->checkin_latitude ?? 'null' }};
            const checkinLng = {{ $visit->checkin_longitude ?? 'null' }};

            // Inisialisasi Peta
            const map = L.map('visitMap').setView([custLat, custLng], 15);

            // Tile Layer dari OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Custom Icon Warna Biru untuk Customer
            const blueIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            // Custom Icon Warna Merah untuk Check-in Sales
            const redIcon = L.icon({
                iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                iconSize: [25, 41],
                iconAnchor: [12, 41],
                popupAnchor: [1, -34],
                shadowSize: [41, 41]
            });

            const markers = [];

            // Marker 1: Customer (Biru)
            if (custLat && custLng) {
                const custMarker = L.marker([custLat, custLng], { icon: blueIcon })
                    .addTo(map)
                    .bindPopup('<b>Toko/Customer:</b><br>{{ $visit->customer->name }}');
                markers.push(custMarker);
            }

            // Marker 2: Check-in Sales (Merah) - Jika sudah check-in
            if (checkinLat && checkinLng) {
                const checkinMarker = L.marker([checkinLat, checkinLng], { icon: redIcon })
                    .addTo(map)
                    .bindPopup('<b>Posisi Check-in Sales:</b><br>{{ $visit->sales->name }}');
                markers.push(checkinMarker);
            }

            // Garis penghubung antara lokasi Customer dan Check-in
            if (custLat && custLng && checkinLat && checkinLng) {
                const polyline = L.polyline([
                    [custLat, custLng],
                    [checkinLat, checkinLng]
                ], { color: '#f97316', weight: 3, dashArray: '6, 8' }).addTo(map);

                // Buat zoom peta otomatis mencakup kedua marker
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.2));
            }
        });
    </script>
@endpush