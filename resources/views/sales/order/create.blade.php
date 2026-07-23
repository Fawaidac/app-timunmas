@extends('layouts.app')

@section('title', 'Buat Sales Order')
@section('page_title', 'Buat Sales Order')
@section('page_description', 'Buat order untuk kunjungan ini')

@section('content')
<div class="section-head">
    <h2>Buat Sales Order</h2>
    <p>Kunjungan ke: <strong>{{ $visit->customer->name }}</strong> - {{ \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') }}</p>
</div>

<article class="card" style="max-width:900px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.order.store') }}" method="POST" id="orderForm">
        @csrf
        <input type="hidden" name="visit_id" value="{{ $visit->id }}">
        <input type="hidden" name="customer_id" value="{{ $visit->customer_id }}">

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
            <div class="field">
                <label>Customer</label>
                <input type="text" class="form-control" value="{{ $visit->customer->name }}" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label>Tanggal Order <span style="color:#ef4444;">*</span></label>
                <input type="date" name="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
            </div>
            <div class="field">
                <label>Jenis Pembayaran <span style="color:#ef4444;">*</span></label>
                <select name="payment_type" id="payment_type" class="form-control" required>
                    <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="credit" {{ old('payment_type') === 'credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="field" id="termField" style="display:none;">
                <label>Tempo Pembayaran (hari) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="payment_term_days" class="form-control" value="{{ old('payment_term_days', 7) }}" min="1">
            </div>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h5 style="margin:0;font-size:14px;font-weight:600;">Item Produk</h5>
                <button type="button" onclick="addRow()" class="button button-soft" style="padding:6px 12px;font-size:12px;">＋ Tambah Item</button>
            </div>

            <div style="overflow-x:auto;">
                <table id="itemsTable" style="width:100%;font-size:13px;">
                    <thead style="background:#fff;border-bottom:2px solid #e2e8f0;">
                        <tr>
                            <th style="padding:10px;text-align:left;width:40%;">Produk</th>
                            <th style="padding:10px;text-align:center;width:15%;">Qty</th>
                            <th style="padding:10px;text-align:right;width:20%;">Harga</th>
                            <th style="padding:10px;text-align:right;width:20%;">Subtotal</th>
                            <th style="padding:10px;text-align:center;width:5%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td style="padding:8px;">
                                <select name="product_id[]" class="form-control product-select" required onchange="fillPrice(this)">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-unit="{{ $product->unit }}">
                                            {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $product->warehouses->sum('pivot.stock_quantity') }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding:8px;">
                                <input type="number" name="quantity[]" class="form-control qty-input" value="1" min="1" required style="text-align:center;" oninput="calculate()">
                            </td>
                            <td style="padding:8px;">
                                <input type="number" name="price[]" class="form-control price-input" value="0" min="0" step="0.01" required style="text-align:right;" oninput="calculate()">
                            </td>
                            <td style="padding:8px;">
                                <input type="text" class="form-control subtotal-display" value="0" readonly style="text-align:right;background:#f9fafb;">
                            </td>
                            <td style="padding:8px;text-align:center;">
                                <button type="button" onclick="removeRow(this)" class="button" style="padding:4px 8px;font-size:11px;background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;">✕</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;padding-top:16px;border-top:2px solid #e2e8f0;display:flex;justify-content:flex-end;align-items:center;gap:16px;">
                <span style="font-size:15px;font-weight:600;color:var(--ink);">Total Order:</span>
                <input type="text" id="totalDisplay" class="form-control" value="Rp 0" readonly style="width:200px;text-align:right;font-size:16px;font-weight:700;color:var(--orange-600);background:#fff7ed;border:2px solid #fed7aa;">
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('sales.kunjungan.show', $visit->id) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Sales Order</button>
        </div>
    </form>
</article>

@push('scripts')
<script>
// Toggle payment term field
document.getElementById('payment_type').addEventListener('change', function() {
    const termField = document.getElementById('termField');
    termField.style.display = this.value === 'credit' ? 'block' : 'none';
});

// Initial check
if (document.getElementById('payment_type').value === 'credit') {
    document.getElementById('termField').style.display = 'block';
}

function fillPrice(select) {
    const row = select.closest('tr');
    const option = select.options[select.selectedIndex];
    const price = option.dataset.price || 0;
    row.querySelector('.price-input').value = price;
    calculate();
}

function calculate() {
    let grandTotal = 0;
    document.querySelectorAll('#itemsTable tbody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.price-input').value) || 0;
        const subtotal = qty * price;
        row.querySelector('.subtotal-display').value = formatNumber(subtotal);
        grandTotal += subtotal;
    });
    document.getElementById('totalDisplay').value = 'Rp ' + formatNumber(grandTotal);
}

function formatNumber(num) {
    return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

function addRow() {
    const tbody = document.querySelector('#itemsTable tbody');
    const firstRow = tbody.querySelector('tr');
    const newRow = firstRow.cloneNode(true);
    
    newRow.querySelector('.product-select').value = '';
    newRow.querySelector('.qty-input').value = 1;
    newRow.querySelector('.price-input').value = 0;
    newRow.querySelector('.subtotal-display').value = 0;
    
    tbody.appendChild(newRow);
    calculate();
}

function removeRow(btn) {
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculate();
    } else {
        alert('Minimal harus ada 1 item produk.');
    }
}

// Initial calculation
calculate();
</script>
@endpush
@endsection
