@extends('layouts.admin')

@section('title', 'Tambah Customer - Admin')
@section('page_title', 'Tambah Customer Baru')
@section('page_description', 'Daftarkan data customer, outlet, koordinat GPS, dan limit kredit')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: 350px;
        width: 100%;
        border-radius: 12px;
        border: 1px solid #cbd5e1;
        margin-top: 10px;
        z-index: 1;
    }
    .form-section-title {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin: 24px 0 14px;
        padding-bottom: 8px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Tambah Customer Baru</h2>
    <p>Lengkapi formulir di bawah ini untuk menambahkan customer / outlet baru ke database.</p>
</div>

<article class="card" style="max-width: 900px;">
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

        {{-- 1. IDENTITAS UTAMA --}}
        <div class="form-section-title" style="margin-top:0;">
            <span>🏢</span> Identitas Utama Customer
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="field">
                <label>Kode Customer (KD_CUST) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="kd_cust" value="{{ old('kd_cust', $nextKdCust) }}" required maxlength="9" style="text-transform:uppercase;font-weight:700;">
                <small style="color:var(--muted);font-size:11px;">Otomatis digenerate (max 9 karakter).</small>
            </div>
            <div class="field">
                <label>Nama Customer / Toko (NM_CUST) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="nm_cust" value="{{ old('nm_cust') }}" placeholder="Contoh: TOKO REJEKI JAYA" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Penanggung Jawab / PIC (C_PERSON)</label>
                <input type="text" name="c_person" value="{{ old('c_person') }}" placeholder="Nama pemilik / penanggung jawab" maxlength="20">
            </div>
            <div class="field">
                <label>Kategori Customer (KD_KAT)</label>
                <select name="kd_kat">
                    <option value="">— Pilih Kategori —</option>
                    @foreach($kategoriList as $k)
                        <option value="{{ trim($k->KD_KAT) }}" {{ old('kd_kat') == trim($k->KD_KAT) ? 'selected' : '' }}>
                            [{{ trim($k->KD_KAT) }}] {{ trim($k->KATEGORI) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- 2. KONTAK --}}
        <div class="form-section-title">
            <span>📞</span> Informasi Kontak & Komunikasi
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>No HP / WhatsApp (HP)</label>
                <input type="text" name="hp" value="{{ old('hp') }}" placeholder="081234567890" maxlength="14">
            </div>
            <div class="field">
                <label>Telepon 1 (TELP1)</label>
                <input type="text" name="telp1" value="{{ old('telp1') }}" placeholder="0332-421234" maxlength="14">
            </div>
            <div class="field">
                <label>Telepon 2 (TELP2)</label>
                <input type="text" name="telp2" value="{{ old('telp2') }}" placeholder="0332-421235" maxlength="14">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Email (E_MAIL)</label>
                <input type="email" name="e_mail" value="{{ old('e_mail') }}" placeholder="toko@contoh.com" maxlength="50">
            </div>
            <div class="field">
                <label>Fax (FAX)</label>
                <input type="text" name="fax" value="{{ old('fax') }}" placeholder="0332-xxxxxx" maxlength="14">
            </div>
            <div class="field">
                <label>Website (WEB_SITE)</label>
                <input type="text" name="web_site" value="{{ old('web_site') }}" placeholder="www.tokorejeki.com" maxlength="50">
            </div>
        </div>

        {{-- 3. ALAMAT & PETA GPS --}}
        <div class="form-section-title">
            <span>📍</span> Alamat & Titik Koordinat GPS
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:2fr 1fr;gap:16px;">
            <div class="field">
                <label>Alamat Lengkap (ALM_CUST)</label>
                <input type="text" name="alm_cust" value="{{ old('alm_cust') }}" placeholder="Jl. Raya Bondowoso No. 123, RT 01 / RW 02" maxlength="65">
            </div>
            <div class="field">
                <label>Wilayah (KD_WIL)</label>
                <select name="kd_wil">
                    <option value="">— Pilih Wilayah —</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ trim($w->KD_WIL) }}" {{ old('kd_wil') == trim($w->KD_WIL) ? 'selected' : '' }}>
                            [{{ trim($w->KD_WIL) }}] {{ trim($w->WILAYAH) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Peta Leaflet -->
        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;margin-top:10px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <div>
                    <b style="font-size:13px;color:#334155;">Pilih Titik Lokasi pada Peta:</b>
                    <p style="margin:2px 0 0;font-size:11px;color:var(--muted);">Klik pada peta atau geser pin untuk menentukan koordinat presisi outlet.</p>
                </div>
                <button type="button" id="btn-geolocation" class="button button-soft" style="font-size:11px;padding:5px 12px;border-radius:20px;cursor:pointer;">
                    🎯 Ambil Lokasi Saya
                </button>
            </div>

            <!-- Search box peta -->
            <div style="position:relative;margin-bottom:10px;">
                <input type="text" id="map-search-input" placeholder="🔍 Cari lokasi, nama jalan, atau daerah di Indonesia..."
                       style="width:100%;border-radius:8px;padding:8px 12px;font-size:12px;border:1px solid #cbd5e1;background:#fff;">
                <div id="map-search-results" style="display:none;position:absolute;top:100%;left:0;right:0;background:#fff;border-radius:8px;border:1px solid #e2e8f0;box-shadow:0 8px 20px rgba(0,0,0,0.1);z-index:1000;max-height:180px;overflow-y:auto;padding:4px 0;"></div>
            </div>

            <div id="map"></div>

            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:12px;">
                <div class="field" style="margin:0;">
                    <label style="font-size:12px;">Latitude</label>
                    <input type="text" name="latitude" id="latitude" value="{{ old('latitude') }}" placeholder="-7.9135..." readonly style="background:#f1f5f9;">
                </div>
                <div class="field" style="margin:0;">
                    <label style="font-size:12px;">Longitude</label>
                    <input type="text" name="longitude" id="longitude" value="{{ old('longitude') }}" placeholder="113.8214..." readonly style="background:#f1f5f9;">
                </div>
            </div>
        </div>

        {{-- 4. SALES & KEBIJAKAN KREDIT --}}
        <div class="form-section-title">
            <span>💼</span> Sales & Kebijakan Kredit
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Sales Penanggung Jawab (KD_PEG)</label>
                <select name="kd_peg">
                    <option value="">— Pilih Sales —</option>
                    @foreach($salesList as $s)
                        <option value="{{ $s->KD_PEG }}" {{ old('kd_peg') == $s->KD_PEG ? 'selected' : '' }}>
                            [{{ $s->KD_PEG }}] {{ $s->NM_PEG }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Plafon Kredit (KRD_LIMIT)</label>
                <input type="number" name="krd_limit" value="{{ old('krd_limit', 0) }}" min="0" step="10000" placeholder="0">
                <small style="color:var(--muted);font-size:11px;">Batas maksimal piutang (Rp).</small>
            </div>
            <div class="field">
                <label>Tempo Pembayaran / TOP (TOP_LIMIT)</label>
                <input type="number" name="top_limit" value="{{ old('top_limit', 0) }}" min="0" max="999" placeholder="Hari (e.g. 30)">
                <small style="color:var(--muted);font-size:11px;">Term of payment dalam jumlah hari.</small>
            </div>
        </div>

        {{-- 5. PERPAJAKAN & REKENING BANK --}}
        <div class="form-section-title">
            <span>🏦</span> Informasi Perpajakan & Rekening Bank
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>NPWP</label>
                <input type="text" name="npwp" value="{{ old('npwp') }}" placeholder="xx.xxx.xxx.x-xxx.xxx" maxlength="27">
            </div>
            <div class="field">
                <label>Nama PKP (NM_PKP)</label>
                <input type="text" name="nm_pkp" value="{{ old('nm_pkp') }}" placeholder="Nama wajib pajak" maxlength="65">
            </div>
            <div class="field">
                <label>Alamat PKP (ALM_PKP)</label>
                <input type="text" name="alm_pkp" value="{{ old('alm_pkp') }}" placeholder="Alamat pada faktur pajak" maxlength="65">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Bank 1 (BANK1)</label>
                <input type="text" name="bank1" value="{{ old('bank1') }}" placeholder="BCA" maxlength="50">
            </div>
            <div class="field">
                <label>No Rekening 1 (NO_REK1)</label>
                <input type="text" name="no_rek1" value="{{ old('no_rek1') }}" placeholder="1234567890" maxlength="14">
            </div>
            <div class="field">
                <label>Bank 2 (BANK2)</label>
                <input type="text" name="bank2" value="{{ old('bank2') }}" placeholder="BRI" maxlength="50">
            </div>
            <div class="field">
                <label>No Rekening 2 (NO_REK2)</label>
                <input type="text" name="no_rek2" value="{{ old('no_rek2') }}" placeholder="0987654321" maxlength="14">
            </div>
        </div>

        <div class="button-row" style="margin-top:32px;display:flex;gap:12px;">
            <a href="{{ route('admin.customers.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Customer Baru</button>
        </div>
    </form>
</article>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const defaultLat = {{ old('latitude', -7.9135) }};
    const defaultLng = {{ old('longitude', 113.8214) }};
    const hasInitialCoord = {{ old('latitude') ? 'true' : 'false' }};

    const map = L.map('map').setView([defaultLat, defaultLng], hasInitialCoord ? 15 : 12);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let marker;

    function setCoord(lat, lng) {
        document.getElementById('latitude').value = Number(lat).toFixed(7);
        document.getElementById('longitude').value = Number(lng).toFixed(7);

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                setCoord(pos.lat, pos.lng);
            });
        }
    }

    if (hasInitialCoord) {
        setCoord(defaultLat, defaultLng);
    }

    map.on('click', function (e) {
        setCoord(e.latlng.lat, e.latlng.lng);
    });

    // Geolocation
    document.getElementById('btn-geolocation').addEventListener('click', function () {
        if (!navigator.geolocation) {
            alert('Browser tidak mendukung geolokasi.');
            return;
        }
        this.textContent = '⌛ Mencari lokasi...';
        navigator.geolocation.getCurrentPosition(
            function (pos) {
                const lat = pos.coords.latitude;
                const lng = pos.coords.longitude;
                map.setView([lat, lng], 16);
                setCoord(lat, lng);
                document.getElementById('btn-geolocation').textContent = '🎯 Lokasi Saya';
            },
            function () {
                alert('Gagal mendapatkan lokasi GPS saat ini.');
                document.getElementById('btn-geolocation').textContent = '🎯 Lokasi Saya';
            },
            { enableHighAccuracy: true }
        );
    });

    // Map Search with Nominatim
    let searchTimeout;
    const searchInput = document.getElementById('map-search-input');
    const searchResults = document.getElementById('map-search-results');

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        if (query.length < 3) {
            searchResults.style.display = 'none';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`https://nominatim.openstreetmap.org/search?format=json&countrycodes=id&limit=5&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (!data || data.length === 0) {
                        searchResults.innerHTML = '<div style="padding:8px 12px;font-size:12px;color:var(--muted);">Lokasi tidak ditemukan.</div>';
                        searchResults.style.display = 'block';
                        return;
                    }
                    data.forEach(item => {
                        const div = document.createElement('div');
                        div.style.cssText = 'padding:8px 12px;font-size:12px;cursor:pointer;border-bottom:1px solid #f1f5f9;';
                        div.textContent = item.display_name;
                        div.addEventListener('click', function () {
                            const lat = parseFloat(item.lat);
                            const lng = parseFloat(item.lon);
                            map.setView([lat, lng], 16);
                            setCoord(lat, lng);
                            searchResults.style.display = 'none';
                            searchInput.value = item.display_name.split(',')[0];
                        });
                        searchResults.appendChild(div);
                    });
                    searchResults.style.display = 'block';
                })
                .catch(() => {
                    searchResults.style.display = 'none';
                });
        }, 400);
    });

    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
            searchResults.style.display = 'none';
        }
    });
});
</script>
@endpush
@endsection
