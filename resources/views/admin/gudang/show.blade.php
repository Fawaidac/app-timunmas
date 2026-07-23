@extends('layouts.admin')

@section('title', 'Detail Gudang - Admin')
@section('page_title', 'Detail Gudang')
@section('page_description', 'Informasi lengkap gudang')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $warehouse->name }}</h2>
        <p>Kode: {{ $warehouse->code }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('admin.warehouses.edit', $warehouse->id) }}" class="button button-soft">✏ Edit</a>
        <form action="{{ route('admin.warehouses.destroy', $warehouse->id) }}" method="POST" onsubmit="return confirm('Yakin hapus gudang ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="button" style="background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;padding:8px 16px;cursor:pointer;">🗑 Hapus</button>
        </form>
        <a href="{{ route('admin.warehouses.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;align-items:start;">
    <article class="card">
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Kode Gudang</label>
                <input type="text" class="form-control" value="{{ $warehouse->code }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Nama Gudang</label>
                <input type="text" class="form-control" value="{{ $warehouse->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field" style="grid-column:1/-1;">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Alamat</label>
                <textarea class="form-control" rows="3" readonly style="background:#f9fafb;">{{ $warehouse->address ?? '-' }}</textarea>
            </div>
        </div>
    </article>

    <article class="card">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid #f1f5f9;">
            <div style="width:40px;height:40px;background:{{ $warehouse->is_active ? '#d1fae5' : '#f1f5f9' }};border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">🏭</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Status</div>
                <div style="font-size:16px;font-weight:700;color:{{ $warehouse->is_active ? '#065f46' : '#64748b' }};">
                    {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📅</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Ditambahkan</div>
                <div style="font-size:13px;font-weight:600;">{{ $warehouse->created_at->format('d M Y') }}</div>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:40px;height:40px;background:#fdf4ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">🔄</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Terakhir diupdate</div>
                <div style="font-size:13px;font-weight:600;">{{ $warehouse->updated_at->format('d M Y') }}</div>
            </div>
        </div>
    </article>
</div>
@endsection
