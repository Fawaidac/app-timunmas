@extends('layouts.app')

@section('title', 'Kunjungan Sales')
@section('page_title', 'Daftar Kunjungan')
@section('page_description', 'Kelola jadwal kunjungan dan check-in customer')

@section('content')
<div class="section-head">
    <h2>Kunjungan Sales</h2>
    <p>Jadwalkan kunjungan, check-in GPS, dan buat sales order.</p>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✔</span> {{ session('success') }}
    </div>
@endif

<div class="toolbar">
    <form action="{{ route('sales.kunjungan.index') }}" method="GET" class="search-form" style="flex: 1; max-width: 400px;">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama customer...">
        </label>
    </form>
    <a href="{{ route('sales.kunjungan.create') }}" class="button button-primary">＋ Jadwalkan Kunjungan</a>
</div>

<div class="visit-grid">
    @forelse($visits as $visit)
        @php
            $isScheduled = ($visit->status === 'scheduled' || empty($visit->checkin_time));
            $isCheckedIn = !empty($visit->checkin_time);
        @endphp
        <article class="visit-card">
            <div class="visit-top">
                <h4>{{ $visit->customer->name ?? 'Customer' }}</h4>
                <span class="badge {{ $visit->badge_class }}">
                    {{ $visit->badge_label }}
                </span>
            </div>

            <p>{{ $visit->customer->address ?? 'Tidak ada alamat' }}</p>

            <div class="meta-grid">
                <div>📅 {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-' }}</div>
                <div>🕘 {{ $visit->checkin_time ? \Carbon\Carbon::parse($visit->checkin_time)->format('H:i') : 'Belum Check-in' }}</div>
                <div>Tujuan: {{ ucfirst($visit->purpose ?? '-') }}</div>
                <div>Catatan: {{ $visit->notes ? Str::limit($visit->notes, 20) : '—' }}</div>
            </div>

            <div class="button-row" style="margin-top: 12px; gap: 8px;">
                @if(!$isCheckedIn)
                    {{-- Belum check-in -> Tombol Check-in aktif --}}
                    <a href="{{ route('sales.kunjungan.checkin', $visit->id) }}" class="button button-primary full-width" style="padding:9px;font-size:12px;text-align:center;">
                        📍 Check-in Sekarang
                    </a>
                @else
                    {{-- Sudah check-in --}}
                    <button disabled class="button button-soft full-width" style="padding:9px;font-size:12px;text-align:center;opacity:0.75;cursor:default;">
                        ✓ Sudah Check-in
                    </button>
                    @if($visit->purpose === 'order' || $visit->purpose === 'collection')
                        <a href="{{ route('sales.order.index', ['customer_id' => $visit->customer_id, 'visit_id' => $visit->id]) }}" 
                           class="button button-primary full-width" 
                           style="padding:9px;font-size:12px;text-align:center;display:flex;align-items:center;justify-content:center;text-decoration:none;">
                            🛒 Sales Order
                        </a>
                    @endif
                @endif
            </div>

            <a href="{{ route('sales.kunjungan.show', $visit->id) }}" class="button button-soft full-width" style="margin-top:8px;padding:8px;font-size:11px;text-align:center;">
                Lihat Detail
            </a>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">📍</div>
            <p style="font-size:16px;font-weight:500;">Belum ada kunjungan dijadwalkan</p>
            <a href="{{ route('sales.kunjungan.create') }}" class="button button-primary" style="margin-top:12px;">＋ Jadwalkan Kunjungan Pertama</a>
        </div>
    @endforelse
</div>

@include('partials.pagination', ['paginator' => $visits, 'itemLabel' => 'kunjungan'])
@endsection
