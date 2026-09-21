@extends('layouts.admin')

@section('title', 'Detail Customer - ' . $customer->NM_CUST)
@section('page_title', 'Detail Customer')
@section('page_description', 'Informasi lengkap data customer, outlet, lokasi GPS, dan piutang')

@push('styles')
@if($customer->latitude && $customer->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map-preview {
        height: 280px;
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
        <h2>{{ $customer->NM_CUST }}</h2>
        <p>Kode: <code>{{ $customer->KD_CUST }}</code> · {{ $customer->KATEGORI ?? 'Customer' }} · Wilayah: {{ $customer->WILAYAH ?? '—' }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.customers.edit', $customer->KD_CUST) }}" class="button button-soft">✏ Edit Customer</a>
        <form action="{{ route('admin.customers.destroy', $customer->KD_CUST) }}" method="POST"
              onsubmit="return confirm('Hapus customer ini? Tindakan tidak dapat dibatalkan.');">
            @csrf
            @method('DELETE')
            <button type="submit" style="padding:8px 14px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;cursor:pointer;font-size:13px;">
                🗑 Hapus
            </button>
        </form>
        <a href="{{ route('admin.customers.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<!-- Ringkasan Keuangan & Kredit Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;margin-bottom:20px;">
    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Total Piutang</div>
        <div style="font-size:22px;font-weight:800;color:{{ $customer->current_debt > 0 ? '#b91c1c' : '#059669' }};margin-top:4px;">
            Rp {{ number_format($customer->current_debt, 0, ',', '.') }}
        </div>
    </article>

    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Plafon Kredit (Limit)</div>
        <div style="font-size:22px;font-weight:800;color:#0f766e;margin-top:4px;">
            Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}
        </div>
    </article>

    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Sisa Plafon Kredit</div>
        @php
            $sisaLimit = max(0, $customer->credit_limit - $customer->current_debt);
        @endphp
        <div style="font-size:22px;font-weight:800;color:{{ $sisaLimit > 0 ? '#2563eb' : '#dc2626' }};margin-top:4px;">
            Rp {{ number_format($sisaLimit, 0, ',', '.') }}
        </div>
    </article>

    <article class="card" style="padding:16px;">
        <div style="font-size:11px;color:var(--muted);text-transform:uppercase;font-weight:600;">Tempo Pembayaran (TOP)</div>
        <div style="font-size:22px;font-weight:800;color:#475569;margin-top:4px;">
            {{ $customer->top_days }} Hari
        </div>
    </article>
</div>

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
    {{-- KOLOM KIRI: Identitas & Kontak & Sales --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                🏢 Profil & Kontak Customer
            </h3>
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted);width:38%;">Kode Customer</td>
                    <td style="padding:6px 0;"><b><code>{{ $customer->KD_CUST }}</code></b></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Nama Customer</td>
                    <td style="padding:6px 0;"><b>{{ $customer->NM_CUST }}</b></td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Contact Person (PIC)</td>
                    <td style="padding:6px 0;">{{ $customer->C_PERSON ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Kategori</td>
                    <td style="padding:6px 0;">{{ $customer->KATEGORI ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">No. HP / WA</td>
                    <td style="padding:6px 0;">{{ $customer->HP ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Telepon</td>
                    <td style="padding:6px 0;">{{ $customer->TELP1 ?: ($customer->TELP2 ?: '—') }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Email</td>
                    <td style="padding:6px 0;">{{ $customer->E_MAIL ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Website</td>
                    <td style="padding:6px 0;">{{ $customer->WEB_SITE ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Fax</td>
                    <td style="padding:6px 0;">{{ $customer->FAX ?? '—' }}</td>
                </tr>
            </table>
        </article>

        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                💼 Sales Penanggung Jawab
            </h3>
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:44px;height:44px;border-radius:50%;background:#eff6ff;color:#1d4ed8;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:700;">
                    👤
                </div>
                <div>
                    <div style="font-size:15px;font-weight:700;color:#1e293b;">
                        {{ $customer->sales?->NM_PEG ?? ($customer->NM_PEG ?? 'Belum Ditentukan') }}
                    </div>
                    <div style="font-size:12px;color:var(--muted);">
                        Kode: <code>{{ $customer->KD_PEG ?? '—' }}</code>
                        @if($customer->sales?->HP)
                            · 📱 {{ $customer->sales->HP }}
                        @endif
                    </div>
                </div>
            </div>
        </article>

        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                🏦 Perpajakan & Rekening Bank
            </h3>
            <table style="width:100%;font-size:13px;border-collapse:collapse;">
                <tr>
                    <td style="padding:6px 0;color:var(--muted);width:38%;">NPWP</td>
                    <td style="padding:6px 0;">{{ $customer->NPWP ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Nama PKP</td>
                    <td style="padding:6px 0;">{{ $customer->NM_PKP ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Alamat PKP</td>
                    <td style="padding:6px 0;">{{ $customer->ALM_PKP ?? '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Bank 1</td>
                    <td style="padding:6px 0;">{{ $customer->BANK1 ? $customer->BANK1 . ' - ' . $customer->NO_REK1 : '—' }}</td>
                </tr>
                <tr>
                    <td style="padding:6px 0;color:var(--muted);">Bank 2</td>
                    <td style="padding:6px 0;">{{ $customer->BANK2 ? $customer->BANK2 . ' - ' . $customer->NO_REK2 : '—' }}</td>
                </tr>
            </table>
        </article>
    </div>

    {{-- KOLOM KANAN: Lokasi & Peta GPS --}}
    <div style="display:flex;flex-direction:column;gap:20px;">
        <article class="card">
            <h3 style="font-size:15px;font-weight:700;margin:0 0 16px;padding-bottom:8px;border-bottom:1px solid #f1f5f9;color:#1e293b;">
                📍 Lokasi & Alamat Outlet
            </h3>

            <div style="margin-bottom:12px;">
                <div style="font-size:11px;color:var(--muted);">Alamat Lengkap:</div>
                <div style="font-size:14px;font-weight:600;color:#1e293b;margin-top:2px;">
                    {{ $customer->ALM_CUST ?? '—' }}
                </div>
            </div>

            <div style="margin-bottom:16px;">
                <div style="font-size:11px;color:var(--muted);">Wilayah Distribusi:</div>
                <div style="font-size:13px;font-weight:600;color:#334155;margin-top:2px;">
                    {{ $customer->WILAYAH ?? '—' }} (<code>{{ $customer->KD_WIL ?? '—' }}</code>)
                </div>
            </div>

            @if($customer->latitude && $customer->longitude)
                <div style="margin-top:16px;padding-top:16px;border-top:1px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-size:11px;color:var(--muted);">Koordinat GPS:</div>
                            <div style="font-size:12px;font-weight:700;color:#0284c7;">
                                {{ $customer->latitude }}, {{ $customer->longitude }}
                            </div>
                        </div>
                        <a href="https://www.google.com/maps?q={{ $customer->latitude }},{{ $customer->longitude }}" target="_blank"
                           class="button button-soft" style="font-size:11px;padding:4px 10px;">
                            ↗ Buka di Google Maps
                        </a>
                    </div>
                    <div id="map-preview"></div>
                </div>
            @else
                <div style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:10px;padding:20px;text-align:center;color:var(--muted);font-size:12px;">
                    📍 Belum ada titik koordinat GPS untuk outlet ini.<br>
                    <a href="{{ route('admin.customers.edit', $customer->KD_CUST) }}" style="color:#2563eb;font-weight:600;">Atur koordinat sekarang</a>
                </div>
            @endif
        </article>
    </div>
</div>

@push('scripts')
@if($customer->latitude && $customer->longitude)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $customer->latitude }};
    const lng = {{ $customer->longitude }};
    const map = L.map('map-preview').setView([lat, lng], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup("<b>{{ addslashes($customer->NM_CUST) }}</b><br>{{ addslashes($customer->ALM_CUST ?? '') }}")
        .openPopup();
});
</script>
@endif
@endpush
@endsection
