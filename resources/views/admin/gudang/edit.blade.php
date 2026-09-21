@extends('layouts.admin')

@section('title', 'Edit Gudang - Admin')
@section('page_title', 'Edit Gudang')
@section('page_description', 'Perbarui informasi gudang')

@section('content')
<div class="section-head">
    <h2>Edit Gudang</h2>
    <p>Perbarui informasi: {{ $warehouse->name }}</p>
</div>

<article class="card" style="width: 70%; max-width: 100%;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.warehouses.update', $warehouse->NM_GUDANG) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label>Nama / Kode Gudang <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $warehouse->NM_GUDANG) }}" required maxlength="50">
            <small style="color:var(--muted);font-size:12px;display:block;margin-top:4px;">Nama unik gudang di database.</small>
        </div>

        <div class="field">
            <label>Alamat / Keterangan Gudang</label>
            <textarea name="address" class="form-control" rows="3" placeholder="Alamat atau keterangan lokasi gudang">{{ old('address', $warehouse->KET) }}</textarea>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.warehouses.show', $warehouse->NM_GUDANG) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan</button>
        </div>
    </form>
</article>
@endsection
