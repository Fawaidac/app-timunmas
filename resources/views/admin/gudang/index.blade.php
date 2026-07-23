@extends('layouts.admin')

@section('title', 'Warehouse Management - Admin')
@section('page_title', 'Warehouse Management')
@section('page_description', 'Kelola gudang dan distribusi stok')

@section('content')
<div class="section-head">
    <h2>Warehouse Management</h2>
    <p>Kelola gudang dan monitoring stok per lokasi.</p>
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

<div class="toolbar" style="display:flex;justify-content:flex-end;">
    <a href="{{ route('admin.warehouses.create') }}" class="button button-primary">＋ Tambah Gudang</a>
</div>

<div class="visit-grid">
    @forelse($warehouses as $warehouse)
        <article class="card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:12px;">
                <div>
                    <h3 style="margin:0 0 4px;font-size:17px;">{{ $warehouse->name }}</h3>
                    <p style="margin:0;color:var(--muted);font-size:13px;">{{ $warehouse->code }}</p>
                </div>
                <span style="background:{{ $warehouse->is_active ? '#d1fae5' : '#f1f5f9' }};color:{{ $warehouse->is_active ? '#065f46' : '#64748b' }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                    {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </div>
            @if($warehouse->address)
                <p style="margin:0 0 16px;color:var(--muted);font-size:13px;">📍 {{ $warehouse->address }}</p>
            @endif
            <div style="display:flex;gap:6px;margin-top:auto;">
                <a href="{{ route('admin.warehouses.show', $warehouse->id) }}" class="button button-soft" style="flex:1;padding:8px;font-size:11px;text-align:center;">Detail</a>
                <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="button button-soft" style="flex:1;padding:8px;font-size:11px;text-align:center;">Edit</a>
                <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" onsubmit="return confirm('Yakin hapus gudang ini?');" style="flex:1;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" style="width:100%;padding:8px;font-size:11px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;cursor:pointer;">Hapus</button>
                </form>
            </div>
        </article>
    @empty
        <div style="grid-column:1/-1;text-align:center;padding:60px 0;color:var(--muted);">
            <div style="font-size:48px;margin-bottom:12px;">🏭</div>
            <p style="font-size:16px;font-weight:500;">Belum ada data gudang</p>
            <a href="{{ route('admin.warehouses.create') }}" class="button button-primary" style="margin-top:12px;">＋ Tambah Gudang Pertama</a>
        </div>
    @endforelse
</div>
@endsection
