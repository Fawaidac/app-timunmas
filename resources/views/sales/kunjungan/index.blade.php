@extends('layouts.app')

@section('title', 'Kunjungan Sales - SFA Orange')
@section('page_title', 'Kunjungan Sales')
@section('page_description', 'Kelola jadwal, tujuan, hasil kunjungan, dan tindak lanjut pelanggan')

@section('content')
<div class="section-head">
    <h2>Kunjungan Sales</h2>
    <p>Kelola jadwal, tujuan, hasil kunjungan, dan tindak lanjut pelanggan.</p>
</div>

<div class="toolbar">
    <label class="search-box">
        <span>⌕</span>
        <input type="search" placeholder="Cari pelanggan atau wilayah...">
    </label>
    <button class="button button-primary">＋ Tambah Kunjungan</button>
</div>

<div class="visit-grid">
    @foreach([
        ['Toko Berkah Jaya','Jl. Wijaya II No. 18, Kebayoran Baru','Selesai','success','09:00','1,2 km','Order','42 mnt','Lihat Hasil'],
        ['UD Sinar Baru','Jl. Bangka Raya No. 47, Mampang','Berlangsung','orange','10:30','350 m','Tagihan','10:34','Selesaikan'],
        ['CV Maju Makmur','Jl. Raya Pasar Minggu No. 22, Pancoran','Terjadwal','warning','13:00','4,8 km','Presentasi','Tinggi','Check-in'],
    ] as $visit)
        <article class="visit-card">
            <div class="visit-top">
                <h4>{{ $visit[0] }}</h4>
                <span class="badge badge-{{ $visit[3] }}">{{ $visit[2] }}</span>
            </div>
            <p>{{ $visit[1] }}</p>
            <div class="meta-grid">
                <div>🕘 {{ $visit[4] }}</div>
                <div>📍 {{ $visit[5] }}</div>
                <div>Tujuan: {{ $visit[6] }}</div>
                <div>{{ $visit[2] === 'Selesai' ? 'Durasi' : ($visit[2] === 'Berlangsung' ? 'Check-in' : 'Prioritas') }}: {{ $visit[7] }}</div>
            </div>
            <div class="button-row">
                <a href="{{ route('checkin') }}" class="button button-soft">Check In</a>
                <a href="{{ route('order') }}" class="button button-primary">Sales Order</a>
            </div>
        </article>
    @endforeach
</div>
@endsection
