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
    <div></div>
    <a href="{{ route('sales.kunjungan.create') }}" class="button button-primary">＋ Jadwalkan Kunjungan</a>
</div>

<div class="visit-grid">
    @forelse($visits as $visit)
        <article class="visit-card">
            <div class="visit-top">
                <h4>{{ $visit->customer->name }}</h4>
                <span class="badge badge-{{ $visit->status === 'scheduled' ? 'warning' : ($visit->status === 'in_progress' ? 'primary' : ($visit->status === 'completed' ? 'success' : 'danger')) }}">
                    {{ $visit->status === 'scheduled' ? 'Dijadwalkan' : ($visit->status === 'in_progress' ? 'Berlangsung' : ($visit->status === 'completed' ? 'Selesai' : 'Batal')) }}
                </span>
            </div>

            <p>{{ $visit->customer->address }}</p>

            <div class="meta-grid">
                <div>📅  {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</div>
                <div>🕘 {{ $visit->checkin_time ? \Carbon\Carbon::parse($visit->checkin_time)->format('H:i') : 'Belum Check-in' }}</div>
                <div>Tujuan: {{ $visit->purpose === 'merchandising' ? 'Merchandising' : ($visit->purpose === 'collection' ? 'Penagihan' : 'Order') }}</div>
                <div>{{ $visit->status === 'completed' ? 'Durasi' : ($visit->status === 'in_progress' ? 'Check-in' : 'Prioritas') }}: {{ $visit->duration ?? 'N/A' }}</div>
            </div>

            <div class="button-row">
                @if($visit->status === 'scheduled')
                    <a href="{{ route('sales.kunjungan.checkin', $visit->id) }}" class="button button-soft full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;">
                        Check-in Sekarang
                    </a>
                @else
                    <button disabled class="button button-soft full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;opacity:0.5;cursor:not-allowed;">
                        Sudah Check-in
                    </button>
                @endif

                @if($visit->purpose === 'order' || $visit->purpose === 'collection')
                    @if($visit->checkin_time)
                        <a href="{{ route('sales.order.index') }}" 
                        class="button button-primary full-width" 
                        style="margin-top:12px; padding:9px; font-size:11px; display:flex; align-items:center; justify-content:center; text-decoration:none;">
                            Sales Order
                        </a>
                    @else
                        <button disabled class="button button-primary full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;">
                            Check-in dulu
                        </button>
                    @endif
                @else
                    <button disabled class="button button-primary full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;">
                        Tidak untuk order
                    </button>
                @endif
            </div>
            <a href="{{ route('sales.kunjungan.show', $visit->id) }}" class="button button-soft full-width" style="margin-top:12px;padding:9px;font-size:11px;text-align:center;">
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
@endsection
