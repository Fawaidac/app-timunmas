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

<!-- Tabs & Search Toolbar -->
<div class="toolbar" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div style="display:flex;gap:12px;flex-wrap:wrap;">
        <a href="{{ route('admin.payments', ['status' => 'pending']) }}" class="button {{ $status === 'pending' ? 'button-primary' : 'button-soft' }}" style="font-size:13px;padding:8px 16px;">
            Pending ({{ $pendingApproval }})
        </a>
        <a href="{{ route('admin.payments', ['status' => 'approved']) }}" class="button {{ $status === 'approved' ? 'button-primary' : 'button-soft' }}" style="font-size:13px;padding:8px 16px;">
            Approved ({{ $approved }})
        </a>
        <a href="{{ route('admin.payments', ['status' => 'rejected']) }}" class="button {{ $status === 'rejected' ? 'button-primary' : 'button-soft' }}" style="font-size:13px;padding:8px 16px;">
            Rejected ({{ $rejected }})
        </a>
        <a href="{{ route('admin.payments', ['status' => 'all']) }}" class="button {{ $status === 'all' ? 'button-primary' : 'button-soft' }}" style="font-size:13px;padding:8px 16px;">
            Semua
        </a>
    </div>

    <form action="{{ route('admin.payments') }}" method="GET" class="search-form" style="flex: 1; max-width: 360px;">
        <input type="hidden" name="status" value="{{ $status }}">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari no. bukti, invoice, customer...">
        </label>
    </form>
</div>

<!-- Table -->
<article class="card">
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
                @forelse($payments as $payment)
                    <tr>
                        <td><b>{{ $payment->payment_number }}</b></td>
                        <td>{{ $payment->created_at ? \Carbon\Carbon::parse($payment->created_at)->format('d M Y H:i') : '-' }}</td>
                        <td>{{ $payment->customer->name ?? '-' }}</td>
                        <td>{{ $payment->sales->name ?? '-' }}</td>
                        <td>{{ $payment->invoice->invoice_number ?? $payment->NO_ENT ?? '-' }}</td>
                        <td>{{ ucfirst($payment->payment_method) }}</td>
                        <td>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</td>
                        <td>
                            @if($payment->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($payment->status === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @else
                                <span class="badge badge-warning">Pending</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.pembayaran.show', $payment->id) }}" class="button button-soft" style="padding:6px 12px;font-size:11px;">
                                {{ $payment->status === 'pending_approval' ? 'Review' : 'Detail' }}
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:40px 20px;text-align:center;color:var(--muted);">
                            <div style="font-size:40px;margin-bottom:8px;">💳</div>
                            <p style="font-size:14px;font-weight:500;">Tidak ada pembayaran pada status ini</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $payments, 'itemLabel' => 'pembayaran'])
</article>

@endsection
