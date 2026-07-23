@extends('layouts.admin')

@section('title', 'Sales Visits - Admin')
@section('page_title', 'Sales Activity Monitoring')
@section('page_description', 'Pantau lokasi dan efektivitas kunjungan sales hari ini')

@section('content')
<div class="section-head">
    <h2>Monitoring Kunjungan Sales</h2>
    <p>Pantau rute dan aktivitas kunjungan lapangan seluruh tim sales</p>
</div>

<div class="visit-grid">
    @foreach([
        ['Andi Salesman','Toko Berkah Jaya','Selesai','success','09:00','Kebayoran Baru'],
        ['Budi Sales','UD Sinar Baru','Berlangsung','orange','10:30','Mampang'],
        ['Citra Seller','CV Maju Makmur','Terjadwal','warning','13:00','Pancoran'],
    ] as $visit)
        <article class="visit-card">
            <div class="visit-top">
                <h4>{{ $visit[0] }}</h4>
                <span class="badge badge-{{ $visit[3] }}">{{ $visit[2] }}</span>
            </div>
            <p style="margin: 4px 0 10px; font-weight: 700; color: var(--ink);">{{ $visit[1] }}</p>
            <div class="meta-grid">
                <div>🕘 {{ $visit[4] }}</div>
                <div>📍 {{ $visit[5] }}</div>
            </div>
            <button class="button button-soft full-width" style="padding: 8px; font-size: 11px;">Lihat GPS Log</button>
        </article>
    @endforeach
</div>
@endsection
