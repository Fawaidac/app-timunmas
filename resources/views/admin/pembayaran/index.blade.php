@extends('layouts.admin')

@section('title', 'Payments - Admin')
@section('page_title', 'Kelola Pembayaran')
@section('page_description', 'Approve atau reject pembayaran dari sales')

@section('content')
<div class="section-head">
    <h2>Manajemen Pembayaran</h2>
    <p>Review dan approve pembayaran yang dititipkan sales</p>
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

<!-- Stats Cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;margin-bottom:24px;">
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#FEF3C7;display:flex;align-items:center;justify-content:center;font-size:20px;">⏳</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Pending Approval</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">{{ $pendingApproval }}</h3>
                <p style="font-size:12px;color:var(--muted);margin:4px 0 0;">Rp {{ number_format($totalPending, 0, ',', '.') }}</p>
            </div>
        </div>
    </article>
    
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#D1FAE5;display:flex;align-items:center;justify-content:center;font-size:20px;">✅</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Approved</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">{{ $approved }}</h3>
                <p style="font-size:12px;color:var(--muted);margin:4px 0 0;">Rp {{ number_format($totalApproved, 0, ',', '.') }}</p>
            </div>
        </div>
    </article>
    
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#FEE2E2;display:flex;align-items:center;justify-content:center;font-size:20px;">❌</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Rejected</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">{{ $rejected }}</h3>
            </div>
        </div>
    </article>
    
    <article class="card">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px;">
            <div style="width:40px;height:40px;border-radius:10px;background:#DBEAFE;display:flex;align-items:center;justify-content:center;font-size:20px;">📊</div>
            <div>
                <p style="color:var(--muted);font-size:12px;margin:0;">Approved Bulan Ini</p>
                <h3 style="margin:0;font-size:20px;font-weight:700;">Rp {{ number_format($approvedBulanIni, 0, ',', '.') }}</h3>
            </div>
        </div>
    </article>
</div>

<!-- Tabs -->
<div style="border-bottom:2px solid #f3f4f6;margin-bottom:20px;">
    <div style="display:flex;gap:24px;">
        <button class="tab-btn active" data-tab="pending">Pending ({{ $pendingApproval }})</button>
        <button class="tab-btn" data-tab="approved">Approved ({{ $approved }})</button>
        <button class="tab-btn" data-tab="rejected">Rejected ({{ $rejected }})</button>
    </div>
</div>

<!-- Pending Approval Table -->
<article class="card tab-content" id="tab-pending">
    <h3 style="margin:0 0 16px;font-size:16px;">Pembayaran Menunggu Approval</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Pembayaran</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>No. Invoice</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentsByStatus['pending_approval'] as $payment)
                    <tr>
                        <td><b>{{ $payment->payment_number }}</b></td>
                        <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') }}</td>
                        <td>{{ $payment->customer->name }}</td>
                        <td>{{ $payment->sales->name }}</td>
                        <td>{{ $payment->invoice->invoice_number }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td><span class="badge badge-warning">Pending</span></td>
                        <td>
                            <a href="{{ route('admin.pembayaran.show', $payment->id) }}" class="button button-soft" style="padding:6px 12px;font-size:11px;">Review</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:40px 20px;text-align:center;color:var(--muted);">
                            <p style="font-size:14px;">Tidak ada pembayaran pending</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>

<!-- Approved Table -->
<article class="card tab-content" id="tab-approved" style="display:none;">
    <h3 style="margin:0 0 16px;font-size:16px;">Pembayaran yang Sudah Diapprove</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Pembayaran</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>No. Invoice</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Approved By</th>
                    <th>Approved At</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentsByStatus['approved'] as $payment)
                    <tr>
                        <td><b>{{ $payment->payment_number }}</b></td>
                        <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y') }}</td>
                        <td>{{ $payment->customer->name }}</td>
                        <td>{{ $payment->sales->name }}</td>
                        <td>{{ $payment->invoice->invoice_number }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td>{{ $payment->approver->name ?? '-' }}</td>
                        <td>{{ $payment->approved_at ? \Carbon\Carbon::parse($payment->approved_at)->format('d M Y H:i') : '-' }}</td>
                        <td>
                            <a href="{{ route('admin.pembayaran.show', $payment->id) }}" class="button button-soft" style="padding:6px 12px;font-size:11px;">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:40px 20px;text-align:center;color:var(--muted);">
                            <p style="font-size:14px;">Belum ada pembayaran yang diapprove</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>

<!-- Rejected Table -->
<article class="card tab-content" id="tab-rejected" style="display:none;">
    <h3 style="margin:0 0 16px;font-size:16px;">Pembayaran yang Ditolak</h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>No. Pembayaran</th>
                    <th>Tanggal</th>
                    <th>Customer</th>
                    <th>Sales</th>
                    <th>No. Invoice</th>
                    <th>Metode</th>
                    <th>Jumlah</th>
                    <th>Alasan Ditolak</th>
                    <th>Rejected By</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentsByStatus['rejected'] as $payment)
                    <tr>
                        <td><b>{{ $payment->payment_number }}</b></td>
                        <td>{{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y') }}</td>
                        <td>{{ $payment->customer->name }}</td>
                        <td>{{ $payment->sales->name }}</td>
                        <td>{{ $payment->invoice->invoice_number }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td style="max-width:200px;">{{ $payment->rejection_reason ?? '-' }}</td>
                        <td>{{ $payment->approver->name ?? '-' }}</td>
                        <td>
                            <a href="{{ route('admin.pembayaran.show', $payment->id) }}" class="button button-soft" style="padding:6px 12px;font-size:11px;">Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="padding:40px 20px;text-align:center;color:var(--muted);">
                            <p style="font-size:14px;">Tidak ada pembayaran yang ditolak</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</article>

@push('scripts')
<script>
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const tab = this.getAttribute('data-tab');
        
        // Update active button
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        
        // Show/hide content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.style.display = 'none';
        });
        document.getElementById('tab-' + tab).style.display = 'block';
    });
});
</script>

<style>
.tab-btn {
    background: none;
    border: none;
    padding: 12px 0;
    font-size: 14px;
    font-weight: 600;
    color: var(--muted);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
}

.tab-btn.active {
    color: var(--primary);
    border-bottom-color: var(--primary);
}

.tab-btn:hover {
    color: var(--text);
}
</style>
@endpush
@endsection
