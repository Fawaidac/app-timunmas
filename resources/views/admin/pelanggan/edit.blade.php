@extends('layouts.admin')

@section('title', 'Edit Customer - Admin')
@section('page_title', 'Edit Customer')
@section('page_description', 'Perbarui informasi customer')

@section('content')
<div class="section-head">
    <h2>Edit Customer</h2>
    <p>Perbarui informasi: {{ $customer->name }}</p>
</div>

<article class="card" style="max-width:640px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.customers.update', $customer->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label style="font-size:12px;color:var(--muted);font-weight:500;">Kode Customer (tidak bisa diubah)</label>
            <input type="text" class="form-control" value="{{ $customer->code }}" readonly style="background:#f9fafb;">
        </div>

        <div class="field">
            <label>Nama Customer <span style="color:#ef4444;">*</span></label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $customer->name) }}" required maxlength="150">
        </div>

        <div class="field">
            <label>Alamat Lengkap</label>
            <textarea name="address" class="form-control" rows="3">{{ old('address', $customer->address) }}</textarea>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Nomor Telepon</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone) }}" maxlength="20">
            </div>
            <div class="field">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email) }}" maxlength="100">
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.customers.show', $customer->id) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan</button>
        </div>
    </form>
</article>
@endsection
