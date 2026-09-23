@extends('layouts.admin')

@section('title', 'Master Data Customer - Admin')
@section('page_title', 'Master Data Customer')
@section('page_description', 'Kelola informasi customer, outlet, lokasi GPS, dan limit kredit')

@section('content')
<div class="section-head">
    <h2>Master Data Customer</h2>
    <p>Kelola data pelanggan/outlet, wilayah, penanggung jawab sales, koordinat GPS, dan piutang.</p>
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

<!-- Toolbar Pencarian & Filter Server-Side -->
<div class="toolbar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;">
    <form action="{{ route('admin.customers.index') }}" method="GET" class="search-form" style="display:flex;flex-wrap:wrap;gap:8px;flex:1;max-width:850px;">
        <label class="search-box" style="flex:2;min-width:240px;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, kontak, alamat, sales...">
        </label>

        <select name="wilayah" style="flex:1;min-width:140px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:#fff;">
            <option value="">— Semua Wilayah —</option>
            @foreach($wilayahList as $w)
                <option value="{{ trim($w->KD_WIL) }}" {{ request('wilayah') == trim($w->KD_WIL) ? 'selected' : '' }}>
                    {{ trim($w->WILAYAH) }}
                </option>
            @endforeach
        </select>

        <select name="sales" style="flex:1;min-width:150px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:#fff;">
            <option value="">— Semua Sales —</option>
            @foreach($salesList as $s)
                <option value="{{ $s->KD_PEG }}" {{ request('sales') == $s->KD_PEG ? 'selected' : '' }}>
                    {{ $s->NM_PEG }}
                </option>
            @endforeach
        </select>

        <select name="kategori" style="flex:1;min-width:130px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:8px;font-size:13px;background:#fff;">
            <option value="">— Kategori —</option>
            @foreach($kategoriList as $k)
                <option value="{{ trim($k->KD_KAT) }}" {{ request('kategori') == trim($k->KD_KAT) ? 'selected' : '' }}>
                    {{ trim($k->KATEGORI) }}
                </option>
            @endforeach
        </select>
    </form>

    <a href="{{ route('admin.customers.create') }}" class="button button-primary">＋ Tambah Customer</a>
</div>

<article class="card">
    <div class="table-responsive">
        <table id="customerTable">
            <thead>
                <tr>
                    <th>Customer / Outlet</th>
                    <th>Kontak & PIC</th>
                    <th>Alamat & Wilayah</th>
                    <th>Sales PIC</th>
                    <th>Plafon Kredit</th>
                    <th>Total Piutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#1d4ed8;">
                                    {{ strtoupper(substr($customer->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight:700;font-size:14px;color:#1e293b;display:flex;align-items:center;gap:6px;">
                                        {{ $customer->name }}
                                        @if($customer->latitude && $customer->longitude)
                                            <span title="GPS Tersimpan ({{ $customer->latitude }}, {{ $customer->longitude }})" style="color:#0284c7;font-size:12px;cursor:help;">📍</span>
                                        @endif
                                    </div>
                                    <div style="font-size:11px;color:var(--muted);display:flex;gap:6px;align-items:center;margin-top:2px;">
                                        <code>{{ $customer->code }}</code>
                                        @if($customer->KATEGORI)
                                            <span style="background:#f1f5f9;color:#475569;padding:1px 6px;border-radius:4px;font-size:10px;">{{ $customer->KATEGORI }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;line-height:1.4;">
                                @if($customer->C_PERSON)
                                    <div><b>PIC:</b> {{ $customer->C_PERSON }}</div>
                                @endif
                                @if($customer->phone)
                                    <div>📱 {{ $customer->phone }}</div>
                                @endif
                                @if($customer->email)
                                    <div style="color:var(--muted);font-size:11px;">✉ {{ $customer->email }}</div>
                                @endif
                                @if(!$customer->C_PERSON && !$customer->phone && !$customer->email)
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;max-width:220px;line-height:1.3;">
                                @if($customer->address)
                                    <div>{{ $customer->address }}</div>
                                @endif
                                @if($customer->WILAYAH)
                                    <span style="display:inline-block;background:#f8fafc;border:1px solid #e2e8f0;color:#475569;font-size:10px;padding:1px 6px;border-radius:4px;margin-top:3px;">
                                        📍 {{ $customer->WILAYAH }}
                                    </span>
                                @endif
                                @if(!$customer->address && !$customer->WILAYAH)
                                    <span style="color:var(--muted);">—</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            @if($customer->sales || $customer->NM_PEG)
                                <div style="font-size:12px;">
                                    <b>{{ $customer->sales?->NM_PEG ?? $customer->NM_PEG }}</b>
                                    <div style="font-size:10px;color:var(--muted);"><code>{{ $customer->KD_PEG }}</code></div>
                                </div>
                            @else
                                <span style="color:var(--muted);font-size:12px;">—</span>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12px;">
                                @if($customer->credit_limit > 0)
                                    <div style="font-weight:600;color:#0f766e;">Rp {{ number_format($customer->credit_limit, 0, ',', '.') }}</div>
                                    <div style="font-size:10px;color:var(--muted);">Tempo: {{ $customer->top_days }} hari</div>
                                @else
                                    <span style="color:var(--muted);">Tunai (0)</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size:12px;font-weight:{{ $customer->current_debt > 0 ? '700' : '400' }};color:{{ $customer->current_debt > 0 ? '#b91c1c' : 'var(--muted)' }};">
                                Rp {{ number_format($customer->current_debt, 0, ',', '.') }}
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;align-items:center;">
                                <a href="{{ route('admin.customers.show', $customer->code) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Detail</a>
                                <a href="{{ route('admin.customers.edit', $customer->code) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Edit</a>
                                <form action="{{ route('admin.customers.destroy', $customer->code) }}" method="POST"
                                      onsubmit="return confirm('Hapus customer [{{ $customer->name }}]? Tindakan ini tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="padding:6px 10px;font-size:11px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;border-radius:8px;cursor:pointer;">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;padding:40px;color:var(--muted);">
                            Belum ada data customer. <a href="{{ route('admin.customers.create') }}" style="color:var(--orange-600);">Tambah sekarang</a>
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
