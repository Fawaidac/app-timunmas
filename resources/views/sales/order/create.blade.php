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

    <form action="{{ route('sales.order.store') }}" method="POST" id="orderForm" onsubmit="return submitOrderForm(this);">
        @csrf
        <input type="hidden" name="visit_id" value="{{ $visit->id }}">
        <input type="hidden" name="customer_id" value="{{ $visit->customer_id }}">

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;">
            <div class="field">
                <label>Customer</label>
                <input type="text" class="form-control" value="{{ $visit->customer->name }} ({{ $visit->customer->code }})" readonly style="background:#f9fafb;">
            </div>
            <div class="field">
                <label>Tanggal Transaksi <span style="color:#ef4444;">*</span></label>
                <input type="date" name="order_date" class="form-control" value="{{ old('order_date', date('Y-m-d')) }}" required>
            </div>
            <div class="field">
                <label>Tipe Pembayaran <span style="color:#ef4444;">*</span></label>
                <select name="payment_type" id="payment_type" class="form-control" required onchange="togglePaymentMethod()">
                    <option value="TUNAI" {{ old('payment_type') == 'TUNAI' ? 'selected' : '' }}>💵 Lunas — Pilih Cash / Transfer</option>
                    <option value="KREDIT" {{ old('payment_type', 'KREDIT') == 'KREDIT' ? 'selected' : '' }}>⏳ Kredit / Tempo (Jatuh Tempo 7 Hari)</option>
                </select>
            </div>
            <div class="field" id="paymentMethodField" style="display:none;">
                <label>Metode Bayar (Lunas) <span style="color:#ef4444;">*</span></label>
                <select name="payment_method" id="payment_method" class="form-control">
                    <option value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'selected' : '' }}>💵 Cash (Uang Tunai)</option>
                    <option value="transfer" {{ old('payment_method') == 'transfer' ? 'selected' : '' }}>🏦 Transfer Bank</option>
                </select>
            </div>
        </div>

        <!-- Box Ringkasan Tanggungan / Piutang & Limit Customer (dari VW_PIUTANG) -->
        <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; margin-bottom:20px;">
            <div style="font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                <span>💳</span> INFORMASI PIUTANG & LIMIT CUSTOMER (VW_PIUTANG)
            </div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                <div>
                    <div style="font-size:11px; color:var(--muted);">Tanggungan / Sisa Piutang:</div>
                    <div style="font-size:14px; font-weight:700; color:#b91c1c;">Rp {{ number_format($visit->customer->current_debt, 0, ',', '.') }}</div>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--muted);">Plafon Kredit:</div>
                    <div style="font-size:14px; font-weight:700; color:#0f766e;">{{ $visit->customer->credit_limit > 0 ? 'Rp ' . number_format($visit->customer->credit_limit, 0, ',', '.') : 'Tidak dibatasi' }}</div>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--muted);">Sisa Limit Kredit:</div>
                    <div style="font-size:14px; font-weight:700; color:#2563eb;">{{ $visit->customer->credit_limit > 0 ? 'Rp ' . number_format($visit->customer->remaining_limit, 0, ',', '.') : '—' }}</div>
                </div>
            </div>
        </div>

        <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:16px;margin-bottom:20px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                <h5 style="margin:0;font-size:14px;font-weight:600;">Item Produk</h5>
                <button type="button" onclick="addRow()" class="button button-soft" style="padding:6px 12px;font-size:12px;">＋ Tambah Item</button>
            </div>

            <div style="overflow-x:auto;">
                <table id="itemsTable" style="width:100%;font-size:13px;border-collapse:separate;border-spacing:0;">
                    <thead style="background:#fff;border-bottom:2px solid #e2e8f0;">
                        <tr>
                            <th style="padding:10px;text-align:left;width:36%;">Produk</th>
                            <th style="padding:10px;text-align:left;width:18%;">Satuan</th>
                            <th style="padding:10px;text-align:center;width:12%;">Qty</th>
                            <th style="padding:10px;text-align:right;width:16%;">Harga Satuan</th>
                            <th style="padding:10px;text-align:right;width:14%;">Subtotal</th>
                            <th style="padding:10px;text-align:center;width:4%;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td style="padding:8px;">
                                <select name="product_id[]" class="form-control product-select" required onchange="fillPrice(this)">
                                    <option value="">-- Pilih Produk --</option>
                                    @foreach($products as $product)
                                        @php
                                            $totalStock = $product->warehouses->sum('pivot.stock_quantity');
                                        @endphp
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->price }}" 
                                                data-unit="{{ $product->unit }}" 
                                                data-units='@json($product->units_options)'
                                                data-stock="{{ $totalStock }}">
                                            {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $totalStock }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td style="padding:8px;">
                                <select name="unit[]" class="form-control unit-select" required onchange="onUnitChange(this)">
                                    <option value="">-- Satuan --</option>
                                </select>
                                <input type="hidden" name="sat_ke[]" class="sat-ke-input" value="1">
                                <input type="hidden" name="kapasitas[]" class="kapasitas-input" value="1">
                            </td>
                            <td style="padding:8px;">
                                <input type="number" name="quantity[]" class="form-control qty-input" value="1" min="1" required style="text-align:center;" oninput="validateQty(this)">
                            </td>
                            <td style="padding:8px;">
                                <input type="number" name="price[]" class="form-control price-input" value="0" min="0" step="0.01" required style="text-align:right;" oninput="calculate()">
                            </td>
                            <td style="padding:8px;">
                                <input type="text" class="form-control subtotal-display" value="0" readonly style="text-align:right;background:#f9fafb;font-weight:600;">
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
                <input type="text" id="totalDisplay" class="form-control" value="Rp 0" readonly style="width:220px;text-align:right;font-size:16px;font-weight:700;color:var(--orange-600);background:#fff7ed;border:2px solid #fed7aa;">
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('sales.kunjungan.show', $visit->id) }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" id="submitBtn" style="flex:2;">🧾 Simpan & Terbitkan Nota Penjualan</button>
        </div>
    </form>
</article>

@push('scripts')
<script>

function togglePaymentMethod() {
    const tipe = document.getElementById('payment_type');
    const field = document.getElementById('paymentMethodField');
    const select = document.getElementById('payment_method');
    if (!tipe || !field || !select) return;
    if (tipe.value === 'TUNAI') {
        field.style.display = '';
        select.required = true;
    } else {
        field.style.display = 'none';
        select.required = false;
        select.value = '';
    }
}
togglePaymentMethod();

function fillPrice(select) {
    const row = select.closest('tr');
    const option = select.options[select.selectedIndex];
    const unitSelect = row.querySelector('.unit-select');
    
    if (!option || !option.value) {
        unitSelect.innerHTML = '<option value="">-- Satuan --</option>';
        row.querySelector('.price-input').value = 0;
        row.querySelector('.sat-ke-input').value = 1;
        row.querySelector('.kapasitas-input').value = 1;
        calculate();
        return;
    }
    
    let units = [];
    try {
        units = JSON.parse(option.dataset.units || '[]');
    } catch (e) {
        units = [{ sat_ke: 1, satuan: option.dataset.unit || 'PCS', kapasitas: 1, harga: parseFloat(option.dataset.price) || 0 }];
    }
    
    unitSelect.innerHTML = '';
    units.forEach((u, index) => {
        const opt = document.createElement('option');
        opt.value = u.satuan;
        opt.textContent = `${u.satuan}${u.kapasitas > 1 ? ' (' + u.kapasitas + ' PCS)' : ''}`;
        opt.dataset.price = u.harga;
        opt.dataset.capacity = u.kapasitas;
        opt.dataset.satke = u.sat_ke;
        unitSelect.appendChild(opt);
    });
    
    onUnitChange(unitSelect);
}

function onUnitChange(unitSelect) {
    const row = unitSelect.closest('tr');
    const opt = unitSelect.options[unitSelect.selectedIndex];
    if (!opt) return;
    
    const price = parseFloat(opt.dataset.price) || 0;
    const capacity = parseFloat(opt.dataset.capacity) || 1;
    const satKe = parseInt(opt.dataset.satke) || 1;
    
    row.querySelector('.price-input').value = price;
    row.querySelector('.sat-ke-input').value = satKe;
    row.querySelector('.kapasitas-input').value = capacity;
    
    const prodSelect = row.querySelector('.product-select');
    const prodOpt = prodSelect.options[prodSelect.selectedIndex];
    const totalStockPcs = prodOpt ? (parseFloat(prodOpt.dataset.stock) || 0) : 0;
    
    const qtyInput = row.querySelector('.qty-input');
    if (capacity > 0) {
        const maxInThisUnit = Math.floor(totalStockPcs / capacity);
        qtyInput.setAttribute('max', maxInThisUnit > 0 ? maxInThisUnit : 0);
    }
    
    calculate();
}

function validateQty(input) {
    const row = input.closest('tr');
    const select = row.querySelector('.product-select');
    const option = select.options[select.selectedIndex];
    const unitSelect = row.querySelector('.unit-select');
    const unitOpt = unitSelect.options[unitSelect.selectedIndex];
    
    if (!option || !option.value) return;

    const totalStockPcs = parseFloat(option.dataset.stock) || 0;
    const capacity = unitOpt ? (parseFloat(unitOpt.dataset.capacity) || 1) : 1;
    const maxQtyInUnit = Math.floor(totalStockPcs / capacity);
    let currentQty = parseFloat(input.value) || 0;

    if (totalStockPcs <= 0) {
        Swal.fire({ icon: 'warning', title: 'Stok Kosong', text: `Stok produk "${option.text.split('-')[0].trim()}" sedang KOSONG (0)!` });
        input.value = 0;
        calculate();
        return;
    }

    if (currentQty > maxQtyInUnit) {
        Swal.fire({ icon: 'warning', title: 'Stok Tidak Cukup', text: `Jumlah melebihi stok yang tersedia! Maksimal hanya ${maxQtyInUnit} ${unitOpt ? unitOpt.value : ''} (${totalStockPcs} PCS).` });
        input.value = maxQtyInUnit > 0 ? maxQtyInUnit : 1;
    }

    if (currentQty < 1 && maxQtyInUnit > 0) {
        input.value = 1;
    }

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
    const unitSel = newRow.querySelector('.unit-select');
    if (unitSel) {
        unitSel.innerHTML = '<option value="">-- Satuan --</option>';
    }
    newRow.querySelector('.sat-ke-input').value = 1;
    newRow.querySelector('.kapasitas-input').value = 1;
    newRow.querySelector('.qty-input').value = 1;
    newRow.querySelector('.price-input').value = 0;
    newRow.querySelector('.subtotal-display').value = '0';
    
    tbody.appendChild(newRow);
    calculate();
}

function removeRow(btn) {
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    if (rows.length > 1) {
        btn.closest('tr').remove();
        calculate();
    } else {
        Swal.fire({ icon: 'warning', title: 'Minimal harus ada 1 item produk.' });
    }
}

// Proteksi double-submit: disable tombol & tandai form saat dikirim
function submitOrderForm(form) {
    if (form.dataset.submitted === '1') {
        return false;
    }
    form.dataset.submitted = '1';
    const btn = document.getElementById('submitBtn');
    if (btn) {
        btn.disabled = true;
        btn.textContent = '⏳ Menyimpan...';
        btn.style.opacity = '0.7';
    }
    return true;
}

// Initial calculation
calculate();
</script>
@endpush
@endsection
