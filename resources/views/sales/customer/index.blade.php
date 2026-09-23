@extends('layouts.app')

@section('title', 'Customer Saya - Sales')
@section('page_title', 'Customer Saya')
@section('page_description', 'Daftar customer / outlet yang Anda kelola')

@section('content')
<div class="section-head">
    <h2>Customer Saya</h2>
    <p>Daftar customer / outlet yang terdaftar di bawah pengelolaan Anda (Sales: <strong>{{ \App\Helpers\SalesHelper::nama() }}</strong>)</p>
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

<!-- Toolbar Pencarian & Aksi -->
<div class="toolbar">
    <form action="{{ route('sales.customer.index') }}" method="GET" class="search-form" style="flex: 1; max-width: 480px;">
        <label class="search-box">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama, kode, alamat, telepon...">
        </label>
    </form>
    <a href="{{ route('sales.customer.create') }}" class="button button-primary">＋ Tambah Customer</a>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Customer / Outlet</th>
                    <th>Kontak & PIC</th>
                    <th>Alamat & Wilayah</th>
                    <th>Kategori</th>
                    <th style="text-align:right;">Jumlah Piutang</th>
                    <th>GPS</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:linear-gradient(135deg,var(--orange-100),var(--orange-50));color:var(--orange-700);flex-shrink:0;">
                                    {{ strtoupper(substr($customer->NM_CUST ?? 'C', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:14px;color:var(--ink);">
                                        {{ $customer->NM_CUST }}
                                    </div>
                                    <div style="font-size:11px;color:var(--muted);margin-top:2px;">
                                        <code>{{ $customer->KD_CUST }}</code>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;line-height:1.4;">
                                @if($customer->C_PERSON)
                                    <div><b>PIC:</b> {{ $customer->C_PERSON }}</div>
                                @endif
                                @if($customer->HP ?: $customer->TELP1)
                                    <div>📱 {{ $customer->HP ?: $customer->TELP1 }}</div>
                                @endif
                                @if($customer->E_MAIL)
                                    <div style="color:var(--muted);font-size:11px;">✉ {{ $customer->E_MAIL }}</div>
                                @endif
                                @if(!$customer->C_PERSON && !$customer->HP && !$customer->TELP1 && !$customer->E_MAIL)
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;max-width:240px;line-height:1.3;">
                                @if($customer->ALM_CUST)
                                    <div>{{ $customer->ALM_CUST }}</div>
                                @endif
                                @if($customer->WILAYAH)
                                    <span style="display:inline-block;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:10px;padding:1px 6px;border-radius:4px;margin-top:3px;">
                                        📍 {{ $customer->WILAYAH }}
                                    </span>
                                @endif
                                @if(!$customer->ALM_CUST && !$customer->WILAYAH)
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($customer->KATEGORI)
                                <span class="badge badge-warning" style="font-size:11px;">{{ $customer->KATEGORI }}</span>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td style="text-align:right;white-space:nowrap;">
                            @if($customer->current_debt > 0.005)
                                <span style="color:#b91c1c;font-weight:700;font-size:13px;">Rp {{ number_format($customer->current_debt, 0, ',', '.') }}</span>
                                @if($customer->credit_limit > 0)
                                    <div style="font-size:10px;color:var(--muted);font-weight:400;margin-top:2px;">Limit: Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</div>
                                @endif
                            @else
                                <span style="color:var(--muted);font-size:12px;font-weight:600;">Rp 0</span>
                            @endif
                        </td>
                        <td>
                            @if($customer->LATITUDE && $customer->LONGITUDE)
                                <span title="GPS: {{ $customer->LATITUDE }}, {{ $customer->LONGITUDE }}" style="color:#0284c7;font-size:12px;cursor:help;display:inline-flex;align-items:center;gap:4px;">
                                    📍 <span style="font-size:11px;color:#0369a1;">Ada</span>
                                </span>
                            @else
                                <span style="color:var(--muted);font-size:12px;">Belum set</span>
                            @endif
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <a href="{{ route('sales.customer.show', $customer->KD_CUST) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Detail</a>
                                <a href="{{ route('sales.customer.edit', $customer->KD_CUST) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:48px 20px;color:var(--muted);">
                            <div style="font-size:36px;margin-bottom:8px;">🏪</div>
                            <div style="font-weight:600;font-size:15px;color:var(--ink);">Belum ada customer</div>
                            <p style="font-size:13px;margin:4px 0 12px;">Tambahkan customer pertama Anda untuk mulai mengelola transaksi.</p>
                            <a href="{{ route('sales.customer.create') }}" class="button button-primary">＋ Tambah Customer Sekarang</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- SECTION PAGINATION -->
    @include('partials.pagination', ['paginator' => $customers, 'itemLabel' => 'customer'])
</article>

@endsection
