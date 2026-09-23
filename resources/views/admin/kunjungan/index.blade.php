@extends('layouts.admin')

@section('title', 'Sales Visits - Admin')
@section('page_title', 'Sales Activity Monitoring')
@section('page_description', 'Pantau lokasi dan efektivitas kunjungan sales hari ini')

@section('content')
<div class="section-head">
    <h2>Monitoring Kunjungan Sales</h2>
    <p>Pantau rute dan aktivitas kunjungan lapangan seluruh tim sales</p>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✔</span> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
        <span>✖</span> {{ session('error') }}
    </div>
@endif

<div class="toolbar">
    <form action="{{ route('admin.visits') }}" method="GET" class="search-form" style="flex: 1; max-width: 400px;">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama sales atau customer...">
        </label>
    </form>
</div>

<div class="visit-grid">
    @forelse($visits as $visit)
        <article class="visit-card">
            <div class="visit-top">
                <h4>{{ $visit->sales->name }}</h4>
                <span class="badge {{ $visit->badge_class }}">{{ $visit->badge_label }}</span>
            </div>
            <p style="margin: 4px 0 10px; font-weight: 700; color: var(--ink);">{{ $visit->customer->name }}</p>
            <div class="meta-grid">
                <div>📅  {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</div>
                <div>🕘 {{ $visit->checkin_time ? \Carbon\Carbon::parse($visit->checkin_time)->format('H:i') : 'Belum Check-in' }}</div>
                {{-- <div>📍 {{ $visit->customer->address ?? 'Lokasi tidak tersedia' }}</div> --}}
                                <div>Tujuan: {{ $visit->purpose === 'merchandising' ? 'Merchandising' : ($visit->purpose === 'collection' ? 'Penagihan' : 'Order') }}</div>
                <div>{{ $visit->status === 'completed' ? 'Durasi' : ($visit->status === 'in_progress' ? 'Check-in' : 'Prioritas') }}: {{ $visit->duration ?? 'N/A' }}</div>
            </div>
            @if($visit->checkin_latitude && $visit->checkin_longitude)
                <a href="{{ route('admin.visits.show', $visit->id) }}" class="button button-soft full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;">
                    Lihat Detail
                </a>
            @else
                <button class="button button-soft full-width" style="padding: 8px; font-size: 11px;" disabled>
                    Belum check-in
                </button>
            @endif
        </article>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 60px 20px; color: var(--muted);">
            <div style="font-size: 48px; margin-bottom: 12px;">📭</div>
            <p style="font-size: 16px; font-weight: 500;">Tidak ada kunjungan hari ini</p>
            <p style="font-size: 13px;">Kunjungan sales akan muncul di sini</p>
        </div>
    @endforelse
</div>

@include('partials.pagination', ['paginator' => $visits, 'itemLabel' => 'kunjungan'])
@endsection
