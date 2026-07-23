@extends('layouts.admin')

@section('title', 'Tambah Customer - Admin')
@section('page_title', 'Tambah Customer Baru')
@section('page_description', 'Daftarkan customer atau outlet baru')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 350px;
        width: 100%;
        border-radius: 16px;
        border: 1px solid #cbd5e1;
        margin-top: 10px;
        z-index: 1;
    }
    .map-search-input:focus {
        border-color: #3b82f6 !important;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        background: #ffffff !important;
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Tambah Customer Baru</h2>
    <p>Isi form di bawah untuk mendaftarkan customer baru.</p>
</div>

<article class="card" style="width: 80%; max-width: 100%;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.customers.store') }}" method="POST">
        @csrf

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Kode Customer <span style="color:#ef4444;">*</span></label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}" placeholder="Contoh: CUST-001" required maxlength="50">
            </div>
            <div class="field">
                <label>Nama Customer <span style="color:#ef4444;">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Contoh: Toko Berkah Jaya" required maxlength="150">
            </div>
        </div>

        <div class="field">
            <label>Alamat Lengkap</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota">{{ old('address') }}</textarea>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" placeholder="021-xxxxxxx" maxlength="20">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="email@contoh.com" maxlength="100">
            </div>
        </div>

        <!-- Section Maps / Koordinat GPS -->
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <div>
                    <h4 style="margin:0;font-size:15px;font-weight:600;color:var(--ink);">📍 Lokasi Koordinat (Indonesia)</h4>
                    <p style="margin:2px 0 0;font-size:12px;color:var(--muted);">Ketik alamat di pencarian, klik pada peta, atau geser marker untuk menentukan lokasi customer.</p>
                </div>
                <button type="button" id="btn-geolocation" class="button button-soft" style="font-size:12px;padding:6px 14px;border-radius:20px;cursor:pointer;">
                    🎯 Lokasi Saya
                </button>
            </div>

            <!-- Modern Live Search Bar khusus Indonesia -->
            <div style="position: relative; margin-bottom: 10px;">
                <div style="position: relative; display: flex; align-items: center;">
                    <span style="position: absolute; left: 16px; font-size: 15px; color: #94a3b8; pointer-events: none;">🔍</span>
                    <input type="text" id="map-search-input" class="form-control map-search-input" placeholder="Cari nama jalan, toko, daerah, atau kota di Indonesia..." 
                           style="border-radius: 30px; padding: 11px 40px 11px 44px; font-size: 13.5px; border: 1px solid #cbd5e1; background: #ffffff; box-shadow: 0 3px 12px rgba(0, 0, 0, 0.04); transition: all 0.2s ease;">
                    <span id="search-spinner" style="position: absolute; right: 16px; display: none; font-size: 14px;">⌛</span>
                </div>
                <!-- Dropdown Hasil Pencarian Live -->
                <div id="map-search-results" style="display: none; position: absolute; top: calc(100% + 6px); left: 0; right: 0; background: #ffffff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 12px 28px rgba(0,0,0,0.12); z-index: 1000; max-height: 240px; overflow-y: auto; padding: 6px 0;"></div>
            </div>

            <div id="map"></div>

            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:14px;">
                <div class="field">
                    <label style="font-size:12px;color:var(--muted);">Latitude</label>
                    <input type="text" id="latitude" name="latitude" class="form-control" value="{{ old('latitude') }}" placeholder="Contoh: -6.2088" readonly style="background:#f8fafc;border-radius:10px;">
                </div>
                <div class="field">
                    <label style="font-size:12px;color:var(--muted);">Longitude</label>
                    <input type="text" id="longitude" name="longitude" class="form-control" value="{{ old('longitude') }}" placeholder="Contoh: 106.8456" readonly style="background:#f8fafc;border-radius:10px;">
                </div>
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.customers.index') }}" class="button button-soft" style="flex:1;text-align:center;border-radius:10px;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;border-radius:10px;">Simpan Customer</button>
        </div>
    </form>
</article>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var defaultLat = {{ old('latitude', '-6.2088') }};
        var defaultLng = {{ old('longitude', '106.8456') }};
        var hasInitialValue = {{ old('latitude') ? 'true' : 'false' }};

        var map = L.map('map').setView([defaultLat, defaultLng], hasInitialValue ? 15 : 12);

        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);

        function updatePosition(lat, lng) {
            var formattedLat = parseFloat(lat).toFixed(8);
            var formattedLng = parseFloat(lng).toFixed(8);
            document.getElementById('latitude').value = formattedLat;
            document.getElementById('longitude').value = formattedLng;
        }

        if (hasInitialValue) {
            updatePosition(defaultLat, defaultLng);
        }

        // Event drag marker
        marker.on('dragend', function (e) {
            var position = marker.getLatLng();
            updatePosition(position.lat, position.lng);
        });

        // Event klik di peta
        map.on('click', function (e) {
            marker.setLatLng(e.latlng);
            updatePosition(e.latlng.lat, e.latlng.lng);
        });

        // Geolocation Button
        document.getElementById('btn-geolocation').addEventListener('click', function () {
            if (navigator.geolocation) {
                this.disabled = true;
                this.innerHTML = '⌛ Mengambil lokasi...';
                
                navigator.geolocation.getCurrentPosition(function (position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    map.setView([lat, lng], 16);
                    marker.setLatLng([lat, lng]);
                    updatePosition(lat, lng);

                    var btn = document.getElementById('btn-geolocation');
                    btn.disabled = false;
                    btn.innerHTML = '🎯 Lokasi Saya';
                }, function (error) {
                    alert('Gagal mengambil lokasi: ' + error.message);
                    var btn = document.getElementById('btn-geolocation');
                    btn.disabled = false;
                    btn.innerHTML = '🎯 Lokasi Saya';
                });
            } else {
                alert('Browser Anda tidak mendukung geolokasi.');
            }
        });

        // Fitur Live Search khusus Indonesia (countrycodes=id)
        var searchInput = document.getElementById('map-search-input');
        var searchResults = document.getElementById('map-search-results');
        var searchSpinner = document.getElementById('search-spinner');
        var searchTimeout = null;

        function performSearch(query) {
            if (!query || query.trim().length < 2) {
                searchResults.style.display = 'none';
                searchResults.innerHTML = '';
                return;
            }

            searchSpinner.style.display = 'block';

            fetch('https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&q=' + encodeURIComponent(query) + '&limit=5')
                .then(function(res) { return res.json(); })
                .then(function(data) {
                    searchSpinner.style.display = 'none';
                    searchResults.innerHTML = '';

                    if (data && data.length > 0) {
                        data.forEach(function(item) {
                            var el = document.createElement('div');
                            el.style.cssText = 'padding: 10px 18px; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease; color: #1e293b;';
                            var title = item.display_name.split(',')[0];
                            el.innerHTML = '<div style="font-weight: 600; color: #0f172a;">' + title + '</div>' +
                                           '<div style="font-size: 11px; color: #64748b; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">' + item.display_name + '</div>';
                            
                            el.addEventListener('mouseenter', function() { el.style.background = '#f1f5f9'; });
                            el.addEventListener('mouseleave', function() { el.style.background = '#ffffff'; });
                            
                            el.addEventListener('click', function() {
                                var lat = parseFloat(item.lat);
                                var lon = parseFloat(item.lon);
                                map.setView([lat, lon], 16);
                                marker.setLatLng([lat, lon]);
                                updatePosition(lat, lon);
                                searchInput.value = title;
                                searchResults.style.display = 'none';
                            });

                            searchResults.appendChild(el);
                        });
                        searchResults.style.display = 'block';
                    } else {
                        searchResults.style.display = 'none';
                    }
                })
                .catch(function() {
                    searchSpinner.style.display = 'none';
                    searchResults.style.display = 'none';
                });
        }

        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            var query = this.value;
            searchTimeout = setTimeout(function() {
                performSearch(query);
            }, 400);
        });

        searchInput.addEventListener('change', function() {
            clearTimeout(searchTimeout);
            performSearch(this.value);
        });

        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                clearTimeout(searchTimeout);
                performSearch(this.value);
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });
    });
</script>
@endpush
