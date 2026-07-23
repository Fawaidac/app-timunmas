@extends('layouts.app')

@section('title', 'Tambah Order Baru')
@section('page_title', 'Tambah Sales Order')
@section('page_description', 'Buat order baru tanpa kunjungan')

@section('content')
<div class="section-head">
    <h2>Tambah Order Baru</h2>
    <p>Buat sales order langsung tanpa melalui kunjungan.</p>
</div>

<article class="card" style="width: 100%; max-width: 100%;">
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

        <!-- Grid Form Atas -->
        <div class="form-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="field">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Customer <span style="color:#ef4444;">*</span></label>
                <select name="customer_id" class="form-control custom-input" required>
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                            {{ $customer->code }} - {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Tanggal Order <span style="color:#ef4444;">*</span></label>
                <input type="date" name="order_date" class="form-control custom-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
            </div>
            <div class="field">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Jenis Pembayaran <span style="color:#ef4444;">*</span></label>
                <select name="payment_type" id="payment_type" class="form-control custom-input" required>
                    <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="credit" {{ old('payment_type') === 'credit' ? 'selected' : '' }}>Kredit</option>
                </select>
            </div>
            <div class="field" id="termField" style="display:none;">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Tempo Pembayaran (hari) <span style="color:#ef4444;">*</span></label>
                <input type="number" name="payment_term_days" class="form-control custom-input" value="{{ old('payment_term_days', 7) }}" min="1">
            </div>
        </div>

        <!-- Container Item Produk -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); margin-bottom: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <div>
                    <h5 style="margin: 0; font-size: 15px; font-weight: 700; color: #1e293b;">Item Produk</h5>
                    <p style="margin: 2px 0 0; font-size: 12px; color: #64748b;">Pilih barang dan atur kuantitas pesanan</p>
                </div>
                <button type="button" onclick="addRow()" class="button button-soft" style="padding: 8px 14px; font-size: 12px; font-weight: 600; border-radius: 8px;">＋ Tambah Item</button>
            </div>

            <!-- Tabel Responsive -->
            <div class="table-responsive">
                <table id="itemsTable" class="table" style="width:100%; border-collapse: separate; border-spacing: 0;">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Qty</th>
                            <th>Harga</th>
                            <th>Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td style="padding: 10px 6px;">
                                <select name="product_id[]" class="form-control custom-input product-select" required onchange="fillPrice(this)">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" data-price="{{ $product->price }}" data-unit="{{ $product->unit }}">
                                            {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $product->warehouses->sum('pivot.stock_quantity') }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="number" name="quantity[]" class="form-control custom-input qty-input" value="1" min="1" required style="text-align: center;" oninput="calculate()">
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="number" name="price[]" class="form-control custom-input price-input" value="0" min="0" step="0.01" required style="text-align: right;" oninput="calculate()">
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="text" class="form-control custom-input subtotal-display" value="0" readonly style="text-align: right; background: #f1f5f9; font-weight: 600; color: #334155;">
                            </td>
                            <td style="padding: 10px 6px; text-align: center;">
                                <button type="button" onclick="removeRow(this)" class="btn-delete-row" title="Hapus Item">
                                    ✕
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Section Total -->
            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px dashed #cbd5e1; display: flex; justify-content: flex-end; align-items: center; gap: 16px;">
                <span style="font-size: 14px; font-weight: 600; color: #475569;">Total Order:</span>
                <input type="text" id="totalDisplay" class="form-control" value="Rp 0" readonly style="width: 220px; text-align: right; font-size: 18px; font-weight: 700; color: #ea580c; background: #fff7ed; border: 2px solid #ffedd5; border-radius: 10px; padding: 8px 12px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.03);">
            </div>
        </div>

        <!-- Tombol Aksi -->
        <div class="button-row" style="margin-top: 24px; display: flex; gap: 12px;">
            <a href="{{ route('sales.order.index') }}" class="button button-soft" style="flex: 2; text-align: center; display: flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none;">Batal</a>
            <button type="submit" class="button button-primary" style="flex: 2; border-radius: 8px; padding: 12px; font-weight: 600;">Simpan Sales Order</button>
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
