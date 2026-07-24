@extends('layouts.admin')

@section('title', 'Master Data Customer - Admin')
@section('page_title', 'Master Data Customer')
@section('page_description', 'Kelola informasi customer dan outlet')

@push('styles')
<style>
/* Style Pagination Modern */
.pagination-wrapper {
    margin-top: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
}

.pagination-info {
    font-size: 13px;
    color: #64748b;
}

.pagination-container {
    display: flex;
    align-items: center;
    gap: 6px;
    list-style: none;
    margin: 0;
    padding: 0;
}

.pagination-container .page-item .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 10px;
    border-radius: 8px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #334155;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease-in-out;
}

.pagination-container .page-item .page-link:hover {
    border-color: #f97316;
    color: #ea580c;
    background-color: #fff7ed;
}

.pagination-container .page-item.active .page-link {
    background-color: #f97316;
    border-color: #f97316;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(249, 115, 22, 0.25);
}

.pagination-container .page-item.disabled .page-link {
    background-color: #f8fafc;
    border-color: #e2e8f0;
    color: #cbd5e1;
    cursor: not-allowed;
    pointer-events: none;
}
</style>
@endpush

@section('content')
<div class="section-head">
    <h2>Master Data Customer</h2>
    <p>Kelola informasi customer, alamat, dan riwayat transaksi.</p>
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

<!-- Toolbar Pencarian Server-Side -->
<div class="toolbar">
    <form action="{{ route('admin.customers.index') }}" method="GET" style="flex: 1; max-width: 400px;">
        <label class="search-box" style="width: 100%;">
            <span>⌕</span>
            <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari kode, nama, atau alamat customer..." onchange="this.form.submit()">
        </label>
    </form>
    <a href="{{ route('admin.customers.create') }}" class="button button-primary">＋ Tambah Customer</a>
</div>

<article class="card">
    <div class="table-responsive">
        <table id="customerTable">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Customer</th>
                    <th>Alamat</th>
                    <th>Telepon</th>
                    <th>Email</th>
                    <th>Piutang</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($customers as $customer)
                    <tr>
                        <td><b>{{ $customer->code }}</b></td>
                        <td>{{ $customer->name }}</td>
                        <td style="max-width:200px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $customer->address ?? '-' }}</td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                        <td>{{ $customer->email ?? '-' }}</td>
                        <td style="color:{{ $customer->current_debt > 0 ? '#c2410c' : 'inherit' }};font-weight:{{ $customer->current_debt > 0 ? '600' : '400' }};">
                            Rp {{ number_format($customer->current_debt, 0, ',', '.') }}
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;">
                                <a href="{{ route('admin.customers.show', $customer->id) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Detail</a>
                                <a href="{{ route('admin.customers.edit', $customer->id) }}" class="button button-soft" style="padding:6px 10px;font-size:11px;">Edit</a>
                                <form action="{{ route('admin.customers.destroy', $customer->id) }}" method="POST" onsubmit="return confirm('Yakin hapus customer ini?');">
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
    @if($customers->hasPages())
        <div class="pagination-wrapper">
            <div class="pagination-info">
                Menampilkan <b>{{ $customers->firstItem() }}</b> - <b>{{ $customers->lastItem() }}</b> dari total <b>{{ $customers->total() }}</b> customer
            </div>

            <ul class="pagination-container">
                {{-- Tombol Previous --}}
                @if ($customers->onFirstPage())
                    <li class="page-item disabled"><span class="page-link">‹</span></li>
                @else
                    <li class="page-item"><a class="page-link" href="{{ $customers->previousPageUrl() }}" rel="prev">‹</a></li>
                @endif

                {{-- Angka Halaman --}}
                @foreach ($customers->getUrlRange(1, $customers->lastPage()) as $page => $url)
                    @if ($page == $customers->currentPage())
                        <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                    @else
                        <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                    @endif
                @endforeach

                {{-- Tombol Next --}}
                @if ($customers->hasMorePages())
                    <li class="page-item"><a class="page-link" href="{{ $customers->nextPageUrl() }}" rel="next">›</a></li>
                @else
                    <li class="page-item disabled"><span class="page-link">›</span></li>
                @endif
            </ul>
        </div>
    @endif
</article>

@endsection