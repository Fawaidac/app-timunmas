@extends('layouts.app')

@section('title', 'Jadwalkan Kunjungan')
@section('page_title', 'Jadwalkan Kunjungan Baru')
@section('page_description', 'Buat jadwal kunjungan ke customer')

@section('content')
<div class="section-head">
    <h2>Jadwalkan Kunjungan Baru</h2>
    <p>Pilih customer dan tujuan kunjungan.</p>
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

    <form action="{{ route('sales.kunjungan.store') }}" method="POST">
        @csrf

        <div class="field">
            <label>Customer <span style="color:#ef4444;">*</span></label>
            <select name="customer_id" class="form-control" required>
                <option value="">-- Pilih Customer --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->code }} - {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field">
            <label>Tanggal Kunjungan <span style="color:#ef4444;">*</span></label>
            <input type="date" name="visit_date" class="form-control" value="{{ old('visit_date', date('Y-m-d')) }}" required>
        </div>

        <div class="field">
            <label>Tujuan Kunjungan <span style="color:#ef4444;">*</span></label>
            <select name="purpose" class="form-control" required>
                <option value="">-- Pilih Tujuan --</option>
                <option value="merchandising" {{ old('purpose') === 'merchandising' ? 'selected' : '' }}>Merchandising</option>
                <option value="collection" {{ old('purpose') === 'collection' ? 'selected' : '' }}>Penagihan</option>
                <option value="order" {{ old('purpose') === 'order' ? 'selected' : '' }}>Order</option>
            </select>
        </div>

        <div class="field">
            <label>Catatan (Opsional)</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Jadwal</button>
        </div>
    </form>
</article>
@endsection
