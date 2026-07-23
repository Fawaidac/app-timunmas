@extends('layouts.app')

@section('title', 'Check-in Sales')
@section('page_title', 'Check-in Sales')
@section('page_description', 'Verifikasi lokasi GPS dan dokumentasikan kedatangan di outlet')

@section('content')
<div class="section-head">
    <h2>Check-in Sales</h2>
    <p>Verifikasi lokasi GPS untuk kunjungan ke: <strong>{{ $visit->customer->name }}</strong></p>
</div>

@if($errors->any())
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
        <ul style="margin:0;padding-left:18px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="two-column">
    <article class="card">
        <div style="position:relative;">
            <div class="map-placeholder" id="mapContainer" style="height:300px;border-radius:10px;overflow:hidden;">
                <div id="mapLoader" style="display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;">
                    <div class="map-pin">📍</div>
                    <b id="statusTitle">Mengambil Lokasi GPS...</b>
                    <p id="distanceInfo" style="display:none;">Jarak akan dihitung setelah GPS didapat</p>
                </div>
                <div id="map" style="height:100%;width:100%;display:none;"></div>
            </div>
            <button type="button" onclick="refreshGPS()" class="button button-soft" style="position:absolute;top:12px;right:12px;padding:6px 12px;font-size:12px;box-shadow:0 2px 8px rgba(0,0,0,0.15);background:white;z-index:1000;">
                🔄 Refresh GPS
            </button>
        </div>

        <div style="background:#fef3c7;border:1px solid #fde047;border-radius:10px;padding:16px;margin-top:16px;">
            <h5 style="margin:0 0 8px;font-size:14px;font-weight:600;color:#92400e;">📍 Lokasi Customer</h5>
            @if($visit->customer->latitude && $visit->customer->longitude)
                <p style="margin:0;font-size:13px;color:#78350f;">
                    {{ $visit->customer->latitude }}, {{ $visit->customer->longitude }}
                </p>
                <p style="margin:8px 0 0;font-size:12px;color:#78350f;">
                    Alamat: {{ $visit->customer->address ?? 'Tidak ada alamat' }}
                </p>
            @else
                <p style="margin:0;font-size:13px;color:#78350f;">
                    Customer belum memiliki koordinat GPS.
                </p>
            @endif
        </div>
    </article>

    <article class="card">
        <div class="card-title">
            <h3>Form Check-in</h3>
            <span class="badge badge-success" id="gpsStatus">⏳ Mencari GPS...</span>
        </div>

        <form action="{{ route('sales.kunjungan.checkin.store', $visit->id) }}" method="POST" id="checkinForm">
            @csrf
            <input type="hidden" name="checkin_latitude" id="checkin_latitude">
            <input type="hidden" name="checkin_longitude" id="checkin_longitude">

            <div class="field">
                <label>Pelanggan</label>
                <input type="text" class="form-control" value="{{ $visit->customer->code }} - {{ $visit->customer->name }}" readonly style="background:#f9fafb;cursor:not-allowed;">
            </div>

            <div class="field">
                <label>Tanggal Kunjungan</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}" readonly style="background:#f9fafb;cursor:not-allowed;">
            </div>

            <div class="field">
                <label>Tujuan Kunjungan</label>
                <input type="text" class="form-control" value="{{ $visit->purpose === 'merchandising' ? 'Merchandising' : ($visit->purpose === 'collection' ? 'Penagihan' : 'Order') }}" readonly style="background:#f9fafb;cursor:not-allowed;">
            </div>

            @if($visit->notes)
                <div class="field">
                    <label>Catatan</label>
                    <textarea class="form-control" rows="3" readonly style="background:#f9fafb;cursor:not-allowed;">{{ $visit->notes }}</textarea>
                </div>
            @endif

            <div id="coordinatesDisplay" style="background:#f0f9ff;border:1px solid #bfdbfe;border-radius:10px;padding:12px;margin-bottom:16px;display:none;">
                <p style="margin:0;font-size:12px;color:#1e40af;font-family:monospace;"></p>
            </div>

            <button type="submit" id="submitBtn" class="button button-primary full-width" disabled>
                ✓ Check-in Sekarang
            </button>

            <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft full-width" style="margin-top:12px;">
                Batal
            </a>
        </form>
    </article>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
let map = null;
let userMarker = null;
let customerMarker = null;

const statusTitle = document.getElementById('statusTitle');
const distanceInfo = document.getElementById('distanceInfo');
const gpsStatus = document.getElementById('gpsStatus');
const coordinatesDisplay = document.getElementById('coordinatesDisplay');
const submitBtn = document.getElementById('submitBtn');
const latInput = document.getElementById('checkin_latitude');
const lonInput = document.getElementById('checkin_longitude');
const mapLoader = document.getElementById('mapLoader');
const mapDiv = document.getElementById('map');

function initMap(lat, lon) {
    // Init map if not exists
    if (!map) {
        map = L.map('map').setView([lat, lon], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);
    } else {
        map.setView([lat, lon], 15);
    }

    // Clear existing markers
    if (userMarker) map.removeLayer(userMarker);
    if (customerMarker) map.removeLayer(customerMarker);

    // Add user marker (blue)
    userMarker = L.marker([lat, lon], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:#3b82f6;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [24, 24]
        })
    }).addTo(map).bindPopup('📍 Lokasi Anda');

    @if($visit->customer->latitude && $visit->customer->longitude)
    // Add customer marker (orange)
    customerMarker = L.marker([{{ $visit->customer->latitude }}, {{ $visit->customer->longitude }}], {
        icon: L.divIcon({
            className: 'custom-marker',
            html: '<div style="background:#f97316;width:24px;height:24px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,0.3);"></div>',
            iconSize: [24, 24]
        })
    }).addTo(map).bindPopup('🏪 {{ $visit->customer->name }}');

    // Fit bounds to show both markers
    map.fitBounds([
        [lat, lon],
        [{{ $visit->customer->latitude }}, {{ $visit->customer->longitude }}]
    ], { padding: [50, 50] });
    @endif

    // Show map, hide loader
    mapLoader.style.display = 'none';
    mapDiv.style.display = 'block';

    setTimeout(function() {
        map.invalidateSize();
        @if($visit->customer->latitude && $visit->customer->longitude)
        map.fitBounds([
            [lat, lon],
            [{{ $visit->customer->latitude }}, {{ $visit->customer->longitude }}]
        ], { padding: [50, 50] });
        @else
        map.setView([lat, lon], 15);
        @endif
    }, 100);
}

function getGPS() {
    if (navigator.geolocation) {
        statusTitle.textContent = 'Mengambil Lokasi GPS...';
        statusTitle.style.color = '#6b7280';
        gpsStatus.textContent = '⏳ Mencari GPS...';
        gpsStatus.className = 'badge badge-warning';

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;
                
                latInput.value = lat;
                lonInput.value = lon;
                
                statusTitle.textContent = 'Lokasi GPS Berhasil Didapat!';
                statusTitle.style.color = '#065f46';
                
                @if($visit->customer->latitude && $visit->customer->longitude)
                    const distance = calculateDistance(lat, lon, {{ $visit->customer->latitude }}, {{ $visit->customer->longitude }});
                    distanceInfo.textContent = 'Jarak ke {{ $visit->customer->name }}: ' + Math.round(distance) + ' meter';
                    distanceInfo.style.display = 'block';
                @else
                    distanceInfo.textContent = 'Koordinat customer tidak tersedia';
                    distanceInfo.style.display = 'block';
                @endif
                
                gpsStatus.textContent = '✓ GPS Aktif';
                gpsStatus.className = 'badge badge-success';
                
                coordinatesDisplay.querySelector('p').textContent = 'Koordinat: ' + lat.toFixed(6) + ', ' + lon.toFixed(6);
                coordinatesDisplay.style.display = 'block';
                coordinatesDisplay.style.background = '#d1fae5';
                coordinatesDisplay.style.borderColor = '#6ee7b7';
                
                submitBtn.disabled = false;

                // Initialize map
                initMap(lat, lon);
            },
            function(error) {
                statusTitle.textContent = 'Gagal Mengambil GPS';
                statusTitle.style.color = '#991b1b';
                distanceInfo.textContent = 'Error: ' + error.message;
                distanceInfo.style.display = 'block';
                
                gpsStatus.textContent = '✖ GPS Error';
                gpsStatus.className = 'badge badge-danger';
                
                coordinatesDisplay.style.display = 'block';
                coordinatesDisplay.style.background = '#fee2e2';
                coordinatesDisplay.style.borderColor = '#fca5a5';
                coordinatesDisplay.querySelector('p').textContent = 'Pastikan Anda mengizinkan akses lokasi di browser.';
                coordinatesDisplay.querySelector('p').style.color = '#991b1b';
                
                alert('Browser tidak bisa mengakses GPS. Pastikan Anda mengizinkan akses lokasi.');
            },
            {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    } else {
        statusTitle.textContent = 'Browser Tidak Mendukung GPS';
        statusTitle.style.color = '#991b1b';
        gpsStatus.textContent = '✖ Tidak Didukung';
        gpsStatus.className = 'badge badge-danger';
    }
}

function refreshGPS() {
    getGPS();
}

function calculateDistance(lat1, lon1, lat2, lon2) {
    const earthRadius = 6371000; // meter
    const dLat = deg2rad(lat2 - lat1);
    const dLon = deg2rad(lon2 - lon1);
    
    const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
              Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
              Math.sin(dLon / 2) * Math.sin(dLon / 2);
    
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    
    return earthRadius * c;
}

function deg2rad(deg) {
    return deg * (Math.PI / 180);
}

// Initial GPS fetch on page load
getGPS();
</script>
@endpush
@endsection
