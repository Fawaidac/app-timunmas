@extends('layouts.admin')

@section('title', 'Master Data Customer - Admin')
@section('page_title', 'Master Data Customer')
@section('page_description', 'Kelola informasi customer dan outlet')

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

<div class="toolbar">
    <label class="search-box">
        <span>⌕</span>
        <input type="search" placeholder="Cari customer...">
    </label>
    <a href="{{ route('admin.customers.create') }}" class="button button-primary">＋ Tambah Customer</a>
</div>

<article class="card">
    <div class="table-responsive">
        <table>
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
</article>
@endsection
