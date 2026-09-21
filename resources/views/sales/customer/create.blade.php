@extends('layouts.app')

@section('title', 'Tambah Customer - Sales')
@section('page_title', 'Tambah Customer Baru')
@section('page_description', 'Daftarkan customer / outlet baru ke daftar Anda')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 320px;
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
    .sales-info-box {
        background: linear-gradient(135deg, var(--orange-50), var(--orange-100));
        border: 1px solid #fed7aa;
        border-radius: 10px;
        padding: 12px 16px;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 13px;
        color: var(--orange-900);
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Tambah Customer Baru</h2>
    <p>Customer akan otomatis terdaftar atas nama Anda sebagai sales pengelola.</p>
</div>

<article class="card" style="max-width: 860px;">

    {{-- Info sales otomatis --}}
    <div class="sales-info-box">
        <span style="font-size: 20px;">👤</span>
        <div>
            Customer ini akan ditetapkan ke: <strong>{{ $sales?->NM_PEG ?? \App\Helpers\SalesHelper::nama() }}</strong>
            (KD_PEG: <code>{{ $sales?->KD_PEG ?? \App\Helpers\SalesHelper::kdPeg() }}</code>) secara otomatis.
        </div>
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

    <form action="{{ route('sales.customer.store') }}" method="POST">
        @csrf

        {{-- 1. IDENTITAS --}}
        <div class="form-section-title" style="margin-top:0;">
            <span>🏢</span> Identitas Customer
        </div>

        <div style="display:grid;grid-template-columns:1fr 2fr;gap:14px;">
            <div class="field">
                <label>Kode Customer (auto)</label>
                <input type="text" name="kd_cust" value="{{ old('kd_cust', $nextKdCust) }}" maxlength="9" style="text-transform:uppercase;font-weight:700;">
                <small style="color:var(--muted);font-size:11px;">Bisa dikosongkan, sistem auto-generate.</small>
            </div>
            <div class="field">
                <label>Nama Customer / Toko <span style="color:var(--danger);">*</span></label>
                <input type="text" name="nm_cust" value="{{ old('nm_cust') }}" placeholder="Contoh: TOKO MAJU JAYA" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="field">
                <label>PIC / Penanggung Jawab</label>
                <input type="text" name="c_person" value="{{ old('c_person') }}" placeholder="Nama pemilik / penanggung jawab" maxlength="20">
            </div>
            <div class="field">
                <label>Kategori Customer</label>
                <select name="kd_kat">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ trim($k->KD_KAT) }}" {{ old('kd_kat') == trim($k->KD_KAT) ? 'selected' : '' }}>
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
            <textarea name="alm_cust" rows="2" placeholder="Jl. Contoh No. 1, Kelurahan, Kecamatan, Kota" maxlength="65">{{ old('alm_cust') }}</textarea>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
            <div class="field">
                <label>HP</label>
                <input type="text" name="hp" value="{{ old('hp') }}" placeholder="08xx-xxxx-xxxx" maxlength="14">
            </div>
            <div class="field">
                <label>Telepon</label>
                <input type="text" name="telp1" value="{{ old('telp1') }}" placeholder="021-xxxxxxx" maxlength="14">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="e_mail" value="{{ old('e_mail') }}" placeholder="nama@email.com" maxlength="50">
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
                    <option value="{{ trim($w->KD_WIL) }}" {{ old('kd_wil') == trim($w->KD_WIL) ? 'selected' : '' }}>
                        {{ trim($w->WILAYAH) }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- 4. KOORDINAT GPS --}}
        <div class="form-section-title">
            <span>📍</span> Koordinat GPS (opsional)
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
            <div class="field">
                <label>Latitude</label>
                <input type="text" id="latitude" name="latitude" value="{{ old('latitude') }}" placeholder="-6.2088">
            </div>
            <div class="field">
                <label>Longitude</label>
                <input type="text" id="longitude" name="longitude" value="{{ old('longitude') }}" placeholder="106.8456">
            </div>
        </div>
        <small style="color:var(--muted);font-size:12px;">
            💡 Klik peta di bawah untuk menandai lokasi outlet secara otomatis atau gunakan tombol "Lokasi Saya".
        </small>
        <div style="margin-top:8px;">
            <button type="button" id="btn-locate" class="button button-soft" style="padding:7px 14px;font-size:13px;">
                📡 Gunakan Lokasi Saya
            </button>
        </div>
        <div id="map"></div>

        {{-- Tombol --}}
        <div style="margin-top:24px;display:flex;gap:12px;flex-wrap:wrap;">
            <button type="submit" class="button button-primary">💾 Simpan Customer</button>
            <a href="{{ route('sales.customer.index') }}" class="button button-soft">Batal</a>
        </div>
    </form>
</article>
@endsection

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    const lat0 = parseFloat(document.getElementById('latitude').value) || -6.2088;
    const lng0 = parseFloat(document.getElementById('longitude').value) || 106.8456;

    const map = L.map('map').setView([lat0, lng0], 13);
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

    if (document.getElementById('latitude').value && document.getElementById('longitude').value) {
        setMarker(lat0, lng0);
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
                alert('Gagal mendapatkan lokasi. Pastikan izin lokasi diaktifkan.');
                btn.textContent = '📡 Gunakan Lokasi Saya';
            }
        );
    });
</script>
@endpush
