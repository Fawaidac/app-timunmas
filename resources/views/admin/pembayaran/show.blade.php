@extends('layouts.admin')

@section('title', 'Detail Pembayaran - Admin')
@section('page_title', 'Detail Pembayaran')
@section('page_description', 'Review dan proses pembayaran')

@section('content')
<div class="section-head">
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <div>
            <h2>Detail Pembayaran</h2>
            <p>{{ $payment->payment_number }}</p>
        </div>
        <a href="{{ route('admin.payments') }}" class="button button-soft">← Kembali</a>
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

<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;">
    <!-- Main Content -->
    <div>
        <!-- Payment Info -->
        <article class="card" style="margin-bottom:20px;">
            <h3 style="margin:0 0 16px;font-size:16px;border-bottom:1px solid #f3f4f6;padding-bottom:12px;">Informasi Pembayaran</h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">No. Pembayaran</p>
                    <p style="font-weight:600;margin:0;">{{ $payment->payment_number }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Tanggal Pembayaran</p>
                    <p style="font-weight:600;margin:0;">{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Metode Pembayaran</p>
                    <p style="font-weight:600;margin:0;">
                        @if($payment->payment_method === 'cash')
                            💵 Cash
                        @elseif($payment->payment_method === 'transfer')
                            🏦 Transfer
                        @else
                            📝 Check/Giro
                        @endif
                    </p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Jumlah Dibayar</p>
                    <p style="font-weight:700;font-size:20px;color:var(--primary);margin:0;">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</p>
                </div>
                @if($payment->reference_number)
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Nomor Referensi</p>
                    <p style="font-weight:600;margin:0;">{{ $payment->reference_number }}</p>
                </div>
                @endif
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Status</p>
                    <p style="margin:0;">
                        @if($payment->status === 'pending_approval')
                            <span class="badge badge-warning">Pending Approval</span>
                        @elseif($payment->status === 'approved')
                            <span class="badge badge-success">Approved</span>
                        @else
                            <span class="badge badge-danger">Rejected</span>
                        @endif
                    </p>
                </div>
            </div>

            @if($payment->proof_image_url)
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid #f3f4f6;">
                <p style="color:var(--muted);font-size:12px;margin:0 0 8px;">Bukti Pembayaran</p>
                <img src="{{ asset('storage/' . $payment->proof_image_url) }}" alt="Bukti Pembayaran" style="max-width:100%;max-height:400px;border-radius:8px;border:1px solid #e5e7eb;">
            </div>
            @endif
        </article>

        <!-- Customer & Sales Info -->
        <article class="card" style="margin-bottom:20px;">
            <h3 style="margin:0 0 16px;font-size:16px;border-bottom:1px solid #f3f4f6;padding-bottom:12px;">Informasi Pelanggan & Sales</h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Pelanggan</p>
                    <p style="font-weight:600;margin:0 0 4px;">{{ $payment->customer->name }}</p>
                    <p style="font-size:12px;color:var(--muted);margin:0;">{{ $payment->customer->address }}</p>
                    <p style="font-size:12px;color:var(--muted);margin:4px 0 0;">{{ $payment->customer->phone }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Sales</p>
                    <p style="font-weight:600;margin:0;">{{ $payment->sales->name }}</p>
                    <p style="font-size:12px;color:var(--muted);margin:4px 0 0;">{{ $payment->sales->email }}</p>
                </div>
            </div>
        </article>

        <!-- Invoice Info -->
        <article class="card" style="margin-bottom:20px;">
            <h3 style="margin:0 0 16px;font-size:16px;border-bottom:1px solid #f3f4f6;padding-bottom:12px;">Informasi Invoice</h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">No. Invoice</p>
                    <p style="font-weight:600;margin:0;">{{ $payment->invoice->invoice_number }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Tanggal Invoice</p>
                    <p style="font-weight:600;margin:0;">{{ \Carbon\Carbon::parse($payment->invoice->invoice_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Jatuh Tempo</p>
                    <p style="font-weight:600;margin:0;">{{ \Carbon\Carbon::parse($payment->invoice->due_date)->format('d M Y') }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Status Invoice</p>
                    <p style="margin:0;">
                        @if($payment->invoice->status === 'paid')
                            <span class="badge badge-success">Lunas</span>
                        @elseif($payment->invoice->status === 'partially_paid')
                            <span class="badge badge-info">Dibayar Sebagian</span>
                        @else
                            <span class="badge badge-warning">Belum Dibayar</span>
                        @endif
                    </p>
                </div>
            </div>

            <div style="background:#f9fafb;padding:16px;border-radius:8px;display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Total Invoice</p>
                    <p style="font-weight:700;font-size:18px;margin:0;">Rp {{ number_format($payment->invoice->total_amount, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Sisa Tagihan</p>
                    <p style="font-weight:700;font-size:18px;margin:0;">Rp {{ number_format($payment->invoice->remaining_balance, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p style="color:var(--muted);font-size:12px;margin:0 0 4px;">Pembayaran Ini</p>
                    <p style="font-weight:700;font-size:18px;color:var(--primary);margin:0;">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</p>
                </div>
            </div>
        </article>

        <!-- Order Items -->
        <article class="card">
            <h3 style="margin:0 0 16px;font-size:16px;border-bottom:1px solid #f3f4f6;padding-bottom:12px;">Item Order</h3>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga Satuan</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payment->invoice->order->items as $item)
                            <tr>
                                <td>{{ $item->product->name }}</td>
                                <td>{{ $item->quantity }} {{ $item->product->unit ?? 'pcs' }}</td>
                                <td>Rp {{ number_format($item->price_per_unit, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight:700;background:#f9fafb;">
                            <td colspan="3" style="text-align:right;">Total</td>
                            <td>Rp {{ number_format($payment->invoice->order->total_amount, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </article>
    </div>

    <!-- Sidebar Actions -->
    <div>
        @if($payment->status === 'pending_approval')
            <!-- Approve/Reject Actions -->
            <article class="card" style="margin-bottom:20px;">
                <h3 style="margin:0 0 16px;font-size:16px;color:var(--primary);">⚡ Aksi Diperlukan</h3>
                
                <form action="{{ route('admin.pembayaran.approve', $payment->id) }}" method="POST" onsubmit="return confirm('Yakin approve pembayaran ini?')">
                    @csrf
                    <button type="submit" class="button" style="width:100%;background:#10b981;border-color:#10b981;margin-bottom:12px;">
                        ✓ Approve Pembayaran
                    </button>
                </form>

                <button type="button" class="button button-soft" style="width:100%;background:#fee2e2;border-color:#fca5a5;color:#991b1b;" onclick="showRejectModal()">
                    ✕ Reject Pembayaran
                </button>
            </article>
        @elseif($payment->status === 'approved')
            <article class="card" style="background:#d1fae5;border:2px solid #6ee7b7 ;margin-bottom:20px;">
                <div style="text-align:center;">
                    <div style="font-size:48px;margin-bottom:8px;">✅</div>
                    <h3 style="margin:0 0 8px;color:#065f46;">Pembayaran Approved</h3>
                    <p style="font-size:13px;color:#047857;margin:0;">
                        Oleh: {{ $payment->approver->name ?? '-' }}<br>
                        {{ $payment->approved_at ? \Carbon\Carbon::parse($payment->approved_at)->format('d M Y H:i') : '' }}
                    </p>
                </div>
            </article>
        @else
            <article class="card" style="background:#fee2e2;border:2px solid #fca5a5;">
                <div style="text-align:center;">
                    <div style="font-size:48px;margin-bottom:8px;">❌</div>
                    <h3 style="margin:0 0 8px;color:#991b1b;">Pembayaran Ditolak</h3>
                    <p style="font-size:13px;color:#b91c1c;margin:0 0 12px;">
                        Oleh: {{ $payment->approver->name ?? '-' }}<br>
                        {{ $payment->approved_at ? \Carbon\Carbon::parse($payment->approved_at)->format('d M Y H:i') : '' }}
                    </p>
                    @if($payment->rejection_reason)
                        <div style="background:white;padding:12px;border-radius:6px;text-align:left;">
                            <p style="font-size:11px;color:var(--muted);margin:0 0 4px;font-weight:600;">Alasan Penolakan:</p>
                            <p style="font-size:13px;color:var(--text);margin:0;">{{ $payment->rejection_reason }}</p>
                        </div>
                    @endif
                </div>
            </article>
        @endif

        <!-- Info Box -->
        <article class="card" style="background:#fef3c7;border:1px solid #fde047;">
            <p style="font-size:12px;color:#92400e;margin:0;line-height:1.6;">
                <b>ℹ️ Catatan:</b><br>
                • Pastikan bukti pembayaran jelas dan valid<br>
                • Cek nominal pembayaran sesuai dengan invoice<br>
                • Setelah approve, sisa tagihan invoice akan otomatis berkurang
            </p>
        </article>
    </div>
</div>

<!-- Reject Modal -->
<div id="rejectModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:white;border-radius:12px;padding:24px;max-width:500px;width:90%;">
        <h3 style="margin:0 0 16px;font-size:18px;">Reject Pembayaran</h3>
        <form action="{{ route('admin.pembayaran.reject', $payment->id) }}" method="POST">
            @csrf
            <div style="margin-bottom:16px;">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Alasan Penolakan *</label>
                <textarea name="rejection_reason" rows="4" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px;font-family:inherit;font-size:14px;" placeholder="Jelaskan alasan penolakan pembayaran ini..."></textarea>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <button type="button" class="button button-soft" onclick="hideRejectModal()">Batal</button>
                <button type="submit" class="button" style="background:#dc2626;border-color:#dc2626;">Reject</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal() {
    document.getElementById('rejectModal').style.display = 'flex';
}

function hideRejectModal() {
    document.getElementById('rejectModal').style.display = 'none';
}

// Close modal when clicking outside
document.getElementById('rejectModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        hideRejectModal();
    }
});
</script>
@endpush
@endsection
