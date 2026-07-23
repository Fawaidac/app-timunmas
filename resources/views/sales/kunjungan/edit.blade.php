@extends('layouts.app')

@section('title', 'Edit Kunjungan')
@section('page_title', 'Edit Kunjungan')
@section('page_description', 'Update tujuan, status, dan catatan kunjungan')

@section('content')
<div class="section-head">
    <h2>Edit Kunjungan</h2>
    <p>Customer: <strong>{{ $visit->customer->name }}</strong> • Tanggal: <strong>{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</strong></p>
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

    <form action="{{ route('sales.kunjungan.update', $visit->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="field">
            <label>Customer</label>
            <input type="text" class="form-control" value="{{ $visit->customer->code }} - {{ $visit->customer->name }}" readonly style="background:#f9fafb;cursor:not-allowed;">
            <small style="color:var(--muted);font-size:12px;">Customer tidak dapat diubah</small>
        </div>

        <div class="field">
            <label>Tanggal Kunjungan</label>
            <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}" readonly style="background:#f9fafb;cursor:not-allowed;">
            <small style="color:var(--muted);font-size:12px;">Tanggal tidak dapat diubah</small>
        </div>

        @if($visit->checkin_time)
            <div class="field">
                <label>Check-in Time</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($visit->checkin_time)->format('d M Y H:i') }}" readonly style="background:#f9fafb;cursor:not-allowed;">
                <small style="color:var(--muted);font-size:12px;">Data check-in tidak dapat diubah</small>
            </div>
        @endif

        <div class="field">
            <label>Tujuan Kunjungan <span style="color:#ef4444;">*</span></label>
            <select name="purpose" class="form-control" required>
                <option value="merchandising" {{ old('purpose', $visit->purpose) === 'merchandising' ? 'selected' : '' }}>Merchandising</option>
                <option value="collection" {{ old('purpose', $visit->purpose) === 'collection' ? 'selected' : '' }}>Penagihan</option>
                <option value="order" {{ old('purpose', $visit->purpose) === 'order' ? 'selected' : '' }}>Order</option>
            </select>
        </div>

        <div class="field">
            <label>Status <span style="color:#ef4444;">*</span></label>
            <select name="status" class="form-control" required>
                <option value="scheduled" {{ old('status', $visit->status) === 'scheduled' ? 'selected' : '' }}>Dijadwalkan</option>
                <option value="in_progress" {{ old('status', $visit->status) === 'in_progress' ? 'selected' : '' }}>Berlangsung</option>
                <option value="completed" {{ old('status', $visit->status) === 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ old('status', $visit->status) === 'cancelled' ? 'selected' : '' }}>Batal</option>
            </select>
        </div>

        <div class="field">
            <label>Catatan</label>
            <textarea name="notes" class="form-control" rows="4" placeholder="Catatan tambahan untuk kunjungan ini...">{{ old('notes', $visit->notes) }}</textarea>
        </div>

        <div class="button-row" style="display:flex;gap:12px;margin-top:24px;">
            <a href="{{ route('sales.kunjungan.show', $visit->id) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan</button>
        </div>
    </form>
</article>
@endsection
