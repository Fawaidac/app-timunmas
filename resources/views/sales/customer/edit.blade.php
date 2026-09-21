@extends('layouts.app')

@section('title', 'Edit Customer - Sales')
@section('page_title', 'Edit Customer')
@section('page_description', 'Perbarui data customer / outlet')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 300px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid var(--line);
        margin-top: 8px;
        z-index: 1;
    }
    .form-section-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--ink);
        margin: 22px 0 12px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--line);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .readonly-box {
        background: #f8fafc;
        border: 1px solid var(--line);
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        color: var(--muted);
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Edit Customer: {{ $customer->NM_CUST }}</h2>
    <p>Perbarui data customer / outlet. Pengelola sales tidak dapat diubah.</p>
</div>

<article class="card" style="max-width:860px;">

    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.customer.update', $customer->KD_CUST) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- 1. IDENTITAS --}}
        <div class="form-section-title" style="margin-top:0;">
            <span>🏢</span> Identitas Customer
        </div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:14px;">
            <div class="field">
                <label>Kode Customer</label>
                <div class="readonly-box">🔒 {{ $customer->KD_CUST }}</div>
            </div>
            <div class="field">
                <label>Nama Customer / Toko <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nm_cust" value="{{ old('nm_cust', $customer->NM_CUST) }}" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="field">
                <label>PIC / Penanggung Jawab</label>
                <input type="text" name="c_person" value="{{ old('c_person', $customer->C_PERSON) }}" maxlength="20">
            </div>
            <div class="field">
                <label>Kategori Customer</label>
                <select name="kd_kat">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ trim($k->KD_KAT) }}"
                            {{ old('kd_kat', $customer->KD_KAT) == trim($k->KD_KAT) ? 'selected' : '' }}>
                            {{ trim($k->KATEGORI) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- 2. KONTAK --}}
        <div class="form-section-title">
            <span>📞</span> Kontak
        </div>

        <div class="field">
            <label>Alamat Lengkap</label>
            <textarea name="alm_cust" rows="2" maxlength="65">{{ old('alm_cust', $customer->ALM_CUST) }}</textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
            <div class="field">
                <label>HP</label>
                <input type="text" name="hp" value="{{ old('hp', $customer->HP) }}" maxlength="14">
            </div>
            <div class="field">
                <label>Telepon</label>
                <input type="text" name="telp1" value="{{ old('telp1', $customer->TELP1) }}" maxlength="14">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="e_mail" value="{{ old('e_mail', $customer->E_MAIL) }}" maxlength="50">
            </div>
        </div>

        {{-- 3. WILAYAH --}}
        <div class="form-section-title">
            <span>🗺️</span> Wilayah
        </div>

        <div class="field" style="max-width:340px;">
            <label>Wilayah</label>
            <select name="kd_wil">
                <option value="">— Pilih Wilayah —</option>
                @foreach($wilayahList as $w)
                    <option value="{{ trim($w->KD_WIL) }}"
                        {{ old('kd_wil', $customer->KD_WIL) == trim($w->KD_WIL) ? 'selected' : '' }}>
                        {{ trim($w->WILAYAH) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 4. SALES (readonly) --}}
        <div class="form-section-title">
            <span>👤</span> Sales Pengelola
        </div>
        <div class="readonly-box" style="max-width:400px;">
            🔒 {{ $sales?->NM_PEG ?? \App\Helpers\SalesHelper::nama() }} ({{ $sales?->KD_PEG ?? \App\Helpers\SalesHelper::kdPeg() }}) — tidak dapat diubah
        </div>

        {{-- 5. GPS --}}
        <div class="form-section-title">
            <span>📍</span> Koordinat GPS (opsional)
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="field">
                <label>Latitude</label>
                <input type="text" id="latitude" name="latitude" value="{{ old('latitude', $customer->LATITUDE) }}" placeholder="-6.2088">
            </div>
            <div class="field">
                <label>Longitude</label>
                <input type="text" id="longitude" name="longitude" value="{{ old('longitude', $customer->LONGITUDE) }}" placeholder="106.8456">
            </div>
        </div>
        <div style="margin-top:6px;">
            <button type="button" id="btn-locate" class="button button-soft" style="padding:7px 14px;font-size:13px;">
                📡 Gunakan Lokasi Saya
            </button>
        </div>
        <div id="map"></div>

        {{-- Tombol --}}
        <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button button-primary">💾 Simpan Perubahan</button>
            <a href="{{ route('sales.customer.show', $customer->KD_CUST) }}" class="button button-soft">Batal</a>
        </div>
    </form>
</article>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const latInit = parseFloat(document.getElementById('latitude').value) || -6.2088;
    const lngInit = parseFloat(document.getElementById('longitude').value) || 106.8456;

    const map = L.map('map').setView([latInit, lngInit], 14);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker = null;

    function setMarker(lat, lng) {
        document.getElementById('latitude').value  = lat.toFixed(7);
        document.getElementById('longitude').value = lng.toFixed(7);
        if (marker) map.removeLayer(marker);
        marker = L.marker([lat, lng], { draggable: true }).addTo(map);
        marker.on('dragend', function(e) {
            const pos = e.target.getLatLng();
            document.getElementById('latitude').value  = pos.lat.toFixed(7);
            document.getElementById('longitude').value = pos.lng.toFixed(7);
        });
    }

    // Set marker awal jika ada koordinat
    if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
        setMarker(latInit, lngInit);
    }

    map.on('click', function(e) {
        setMarker(e.latlng.lat, e.latlng.lng);
    });

    document.getElementById('btn-locate').addEventListener('click', function() {
        if (!navigator.geolocation) return alert('Browser tidak mendukung geolokasi.');
        this.textContent = '⏳ Mendeteksi...';
        const btn = this;
        navigator.geolocation.getCurrentPosition(
            function(pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                map.setView([lat, lng], 16);
                setMarker(lat, lng);
                btn.textContent = '📡 Gunakan Lokasi Saya';
            },
            function() {
                alert('Gagal mendapatkan lokasi.');
                btn.textContent = '📡 Gunakan Lokasi Saya';
            }
        );
    });
</script>
@endpush
