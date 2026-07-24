@extends('layouts.app')

@section('title', 'Detail Kunjungan')
@section('page_title', 'Detail Kunjungan')
@section('page_description', 'Informasi lengkap kunjungan sales')

@section('content')
<div class="section-head" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
    <div>
        <h2>{{ $visit->customer->name }}</h2>
        <p>Kunjungan: {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('sales.kunjungan.edit', $visit->id) }}" class="button button-soft">✏️ Edit</a>
        <form action="{{ route('sales.kunjungan.destroy', $visit->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus kunjungan ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="button button-soft" style="background:#fee2e2;border-color:#fca5a5;color:#991b1b;">🗑️ Hapus</button>
        </form>
        <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft">← Kembali</a>
    </div>
</div>

@if(session('success'))
    <div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✔ {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:16px;">
        ✖ {{ session('error') }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1fr 320px;gap:20px;align-items:start;">
    <article class="card">
        <h4 style="margin:0 0 16px;font-size:15px;font-weight:600;padding-bottom:12px;border-bottom:1px solid #f1f5f9;">Informasi Kunjungan</h4>
        
        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Customer</label>
                <input type="text" class="form-control" value="{{ $visit->customer->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Kode Customer</label>
                <input type="text" class="form-control" value="{{ $visit->customer->code }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tanggal Kunjungan</label>
                <input type="text" class="form-control" value="{{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Tujuan</label>
                <input type="text" class="form-control" value="{{ ucfirst($visit->purpose) }}" readonly style="background:#f9fafb;">
            </div>
        </div>

        @if($visit->checkin_time)
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:16px;margin-bottom:20px;">
                <h5 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#065f46;">✓ Check-in Berhasil</h5>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:#166534;margin-bottom:2px;">Waktu Check-in</div>
                        <div style="font-size:14px;font-weight:600;color:#065f46;">{{ \Carbon\Carbon::parse($visit->checkin_time)->format('H:i:s') }}</div>
                    </div>
                    @if($visit->distance_meters)
                        <div>
                            <div style="font-size:11px;color:#166534;margin-bottom:2px;">Jarak dari Customer</div>
                            <div style="font-size:14px;font-weight:600;color:#065f46;">{{ round($visit->distance_meters) }} meter</div>
                        </div>
                    @endif
                    <div style="grid-column:1/-1;">
                        <div style="font-size:11px;color:#166534;margin-bottom:2px;">Koordinat GPS</div>
                        <div style="font-size:12px;color:#065f46;">{{ $visit->checkin_latitude }}, {{ $visit->checkin_longitude }}</div>
                    </div>
                </div>
            </div>
        @endif

        @if($visit->order)
            <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:16px;margin-bottom:20px;">
                <h5 style="margin:0 0 12px;font-size:14px;font-weight:600;color:#c2410c;">🛒 Sales Order Dibuat</h5>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                    <div>
                        <div style="font-size:11px;color:#9a3412;margin-bottom:2px;">Nomor Order</div>
                        <div style="font-size:14px;font-weight:700;color:#c2410c;">{{ $visit->order->order_number }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:#9a3412;margin-bottom:2px;">Total</div>
                        <div style="font-size:14px;font-weight:700;color:#c2410c;">Rp {{ number_format($visit->order->total_amount, 0, ',', '.') }}</div>
                    </div>
                    <div style="grid-column:1/-1;">
                        <div style="font-size:11px;color:#9a3412;margin-bottom:2px;">Status</div>
                        <span style="background:#fed7aa;color:#9a3412;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;">
                            {{ ucfirst($visit->order->status) }}
                        </span>
                    </div>
                </div>
                <a href="{{ route('sales.order.show', $visit->order->id) }}" class="button button-soft full-width" style="margin-top:12px;font-size:12px;">
                    Lihat Detail Order
                </a>
            </div>
        @endif

        @if($visit->notes)
            <div class="field">
                <label style="font-size:12px;color:var(--muted);font-weight:500;">Catatan</label>
                <textarea class="form-control" rows="3" readonly style="background:#f9fafb;">{{ $visit->notes }}</textarea>
            </div>
        @endif
    </article>

    <article class="card">
        <div style="text-align:center;padding:20px 0;border-bottom:1px solid #f1f5f9;margin-bottom:20px;">
            <div style="width:64px;height:64px;background:linear-gradient(135deg,#fff7ed,#fed7aa);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:32px;margin:0 auto 12px;">
                📍
            </div>
            @if($visit->status === 'scheduled')
                <span style="background:#fef3c7;color:#92400e;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Dijadwalkan</span>
            @elseif($visit->status === 'in_progress')
                <span style="background:#dbeafe;color:#1e40af;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Berlangsung</span>
            @elseif($visit->status === 'completed')
                <span style="background:#d1fae5;color:#065f46;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Selesai</span>
            @else
                <span style="background:#fee2e2;color:#991b1b;padding:5px 16px;border-radius:20px;font-size:12px;font-weight:600;">Batal</span>
            @endif
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#f0f9ff;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">👤</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Sales</div>
                <div style="font-size:13px;font-weight:600;">{{ $visit->sales->name }}</div>
            </div>
        </div>

        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <div style="width:40px;height:40px;background:#fef3c7;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;">📅</div>
            <div>
                <div style="font-size:11px;color:var(--muted);">Dibuat</div>
                <div style="font-size:13px;font-weight:600;">{{ $visit->created_at->format('d M Y H:i') }}</div>
            </div>
        </div>
    </article>
</div>
@endsection
