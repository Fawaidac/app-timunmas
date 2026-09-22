@extends('layouts.app')

@section('title', 'Tambah Order Baru')
@section('page_title', 'Tambah Sales Order')
@section('page_description', 'Buat order baru tanpa kunjungan')
@push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* === KUSTOMISASI SELECT2 MODERN === */
        
        /* Box Utama Select2 */
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 8px !important;
            height: 40px !important;
            padding: 5px 10px !important;
            background-color: #ffffff !important;
            transition: all 0.2s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
        }

        /* Hover & Focus state */
        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            border-color: #f97316 !important; /* Warna oranye aksen */
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15) !important;
            outline: none !important;
        }

        /* Teks Pilihan / Placeholder */
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding-left: 2px !important;
            line-height: normal !important;
        }

        /* Panah Dropdown (Arrow) */
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #64748b transparent transparent transparent !important;
            border-width: 5px 4px 0 4px !important;
        }
        .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #f97316 transparent !important;
            border-width: 0 4px 5px 4px !important;
        }

        /* Container Menu Dropdown yang Terbuka */
        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
            background-color: #ffffff !important;
        }

        /* Kotak Search di Dalam Dropdown */
        .select2-search--dropdown {
            padding: 8px 10px !important;
            background-color: #f8fafc !important;
            border-bottom: 1px solid #f1f5f9 !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            padding: 6px 10px !important;
            font-size: 12px !important;
            outline: none !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.15) !important;
        }

        /* Item Opsi Pilihan */
        .select2-results__option {
            padding: 8px 12px !important;
            font-size: 13px !important;
            color: #334155 !important;
            transition: background 0.15s ease !important;
        }

        /* Hover Item Opsi */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #fff7ed !important; /* Latar oranye soft */
            color: #ea580c !important; /* Teks oranye */
            font-weight: 600 !important;
        }

        /* Item Terpilih */
        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #ffedd5 !important;
            color: #c2410c !important;
            font-weight: 600 !important;
        }
    </style>
@endpush
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

    <form action="{{ route('sales.order.store') }}" method="POST" id="orderForm" onsubmit="return submitOrderForm(this);">
        @csrf
        <input type="hidden" name="visit_id" value="{{ $selectedVisitId ?? '' }}">
        
        <!-- Grid Form Atas -->
        <div class="form-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
            <div class="field">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Customer <span style="color:#ef4444;">*</span></label>
                @if($selectedCustomer)
                    <input type="hidden" name="customer_id" value="{{ $selectedCustomer->id }}">
                    <div style="position:relative;">
                        <input type="text" class="form-control custom-input" value="{{ $selectedCustomer->code }} - {{ $selectedCustomer->name }}" readonly style="background:#f1f5f9; cursor:not-allowed; font-weight:600; color:#1e293b; padding-right:32px;">
                        <span style="position:absolute; right:10px; top:50%; transform:translateY(-50%); font-size:12px; color:#64748b;" title="Customer otomatis terkunci dari URL">🔒</span>
                    </div>
                @else
                    <select name="customer_id" id="customer_id" class="form-control custom-input" required onchange="updateCustomerInfo(this)">
                        <option value="">-- Pilih Customer --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" 
                                data-debt="{{ $customer->current_debt }}"
                                data-limit="{{ $customer->credit_limit }}"
                                data-top="{{ $customer->top_days }}"
                                {{ (old('customer_id') == $customer->id) ? 'selected' : '' }}>
                                {{ $customer->code }} - {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div class="field">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Tanggal Order <span style="color:#ef4444;">*</span></label>
                <input type="date" name="order_date" class="form-control custom-input" value="{{ old('order_date', date('Y-m-d')) }}" required>
            </div>
        </div>
        {{-- Payment default: KREDIT 7 hari --}}


        <!-- Box Ringkasan Tanggungan / Piutang & Limit Customer (dari VW_PIUTANG) -->
        <div id="customerInfoBox" style="{{ $selectedCustomer ? 'display:block;' : 'display:none;' }} background:#f8fafc; border:1px solid #e2e8f0; border-radius:10px; padding:14px 18px; margin-bottom:20px;">
            <div style="font-size:12px; font-weight:700; color:#475569; margin-bottom:8px; display:flex; align-items:center; gap:6px;">
                <span>💳</span> INFORMASI PIUTANG & LIMIT CUSTOMER (VW_PIUTANG)
            </div>
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:12px;">
                <div>
                    <div style="font-size:11px; color:var(--muted);">Tanggungan / Sisa Piutang:</div>
                    <div id="custDebtDisplay" style="font-size:14px; font-weight:700; color:#b91c1c;">
                        Rp {{ number_format($selectedCustomer?->current_debt ?? 0, 0, ',', '.') }}
                    </div>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--muted);">Plafon Kredit:</div>
                    <div id="custLimitDisplay" style="font-size:14px; font-weight:700; color:#0f766e;">
                        {{ ($selectedCustomer && $selectedCustomer->credit_limit > 0) ? 'Rp ' . number_format($selectedCustomer->credit_limit, 0, ',', '.') : 'Tidak dibatasi' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:11px; color:var(--muted);">Sisa Limit Kredit:</div>
                    <div id="custRemainingLimitDisplay" style="font-size:14px; font-weight:700; color:#2563eb;">
                        {{ ($selectedCustomer && $selectedCustomer->credit_limit > 0) ? 'Rp ' . number_format($selectedCustomer->remaining_limit, 0, ',', '.') : '—' }}
                    </div>
                </div>
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
                            <th style="width:36%;">Produk</th>
                            <th style="width:18%;">Satuan</th>
                            <th style="width:12%;">Qty</th>
                            <th style="width:16%;">Harga Satuan</th>
                            <th style="width:14%;">Subtotal</th>
                            <th style="width:4%; text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="item-row">
                            <td style="padding: 10px 6px;">
                                <select name="product_id[]" class="form-control custom-input product-select" required onchange="fillPrice(this)">
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
                            <td style="padding: 10px 6px;">
                                <select name="unit[]" class="form-control custom-input unit-select" required onchange="onUnitChange(this)">
                                    <option value="">-- Satuan --</option>
                                </select>
                                <input type="hidden" name="sat_ke[]" class="sat-ke-input" value="1">
                                <input type="hidden" name="kapasitas[]" class="kapasitas-input" value="1">
                            </td>
                            <td style="padding: 10px 6px;">
                                <input type="number" name="quantity[]" class="form-control custom-input qty-input" value="1" min="1" required style="text-align: center;" oninput="validateQty(this);">
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
            <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft" style="flex: 2; text-align: center; display: flex; align-items: center; justify-content: center; border-radius: 8px; text-decoration: none;">Batal</a>
            <button type="submit" class="button button-primary" id="submitBtn" style="flex: 2; border-radius: 8px; padding: 12px; font-weight: 600;">Simpan Sales Order</button>
        </div>
    </form>
</article>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Fungsi Re-inisialisasi Select2
function initSelect2(element) {
    const targets = element ? $(element) : $('.product-select');
    
    targets.each(function() {
        const $this = $(this);
        
        // Hancurkan Select2 lama jika sudah pernah di-init agar tidak konflik
        if ($this.hasClass("select2-hidden-accessible")) {
            $this.select2('destroy');
        }
        
        // Inisialisasi ulang
        $this.select2({
            placeholder: "-- Pilih Produk --",
            allowClear: true,
            width: '100%'
        }).on('change', function() {
            // Trigger manual fillPrice saat opsi diubah via Select2
            fillPrice(this);
        });
    });
}

function updateCustomerInfo(select) {
    const option = select.options[select.selectedIndex];
    const box = document.getElementById('customerInfoBox');
    
    if (!option || !option.value) {
        if (box) box.style.display = 'none';
        return;
    }
    
    const debt = parseFloat(option.dataset.debt) || 0;
    const limit = parseFloat(option.dataset.limit) || 0;
    const top = parseInt(option.dataset.top) || 0;
    const remaining = limit > 0 ? Math.max(0, limit - debt) : 0;
    
    document.getElementById('custDebtDisplay').textContent = 'Rp ' + formatNumber(debt);
    document.getElementById('custLimitDisplay').textContent = limit > 0 ? 'Rp ' + formatNumber(limit) : 'Tidak dibatasi';
    document.getElementById('custRemainingLimitDisplay').textContent = limit > 0 ? 'Rp ' + formatNumber(remaining) : '—';
    
    if (top > 0 && $('#payment_term_days').val() == 7) {
        $('#payment_term_days').val(top);
    }
    
    if (box) box.style.display = 'block';
}

$(document).ready(function() {
    // Inisialisasi Select2 awal saat dokumen siap
    initSelect2();

    // Trigger info customer awal jika sudah ada yang terpilih
    const custSelect = document.getElementById('customer_id');
    if (custSelect && custSelect.value) {
        updateCustomerInfo(custSelect);
    }


    calculate();
});


// Fungsi Tambah Baris Baru
function addRow() {
    const tbody = document.querySelector('#itemsTable tbody');
    const firstRow = tbody.querySelector('tr');

    // 1. Destroy Select2 pada baris acuan sebelum kloning
    $(firstRow).find('.product-select').select2('destroy');

    // 2. Kloning baris HTML murni
    const newRow = firstRow.cloneNode(true);

    // 3. Re-init Select2 pada baris pertama tadi
    initSelect2($(firstRow).find('.product-select'));

    // 4. Bersihkan nilai/input pada baris baru
    const newSelect = $(newRow).find('.product-select');
    newSelect.val('').trigger('change.select2'); // Reset value select
    
    // Hapus kontainer HTML sisa Select2 kloning jika terikut
    $(newRow).find('.select2-container').remove(); 
    
    const unitSel = newRow.querySelector('.unit-select');
    if (unitSel) {
        unitSel.innerHTML = '<option value="">-- Satuan --</option>';
    }
    newRow.querySelector('.sat-ke-input').value = 1;
    newRow.querySelector('.kapasitas-input').value = 1;
    newRow.querySelector('.qty-input').value = 1;
    newRow.querySelector('.price-input').value = 0;
    newRow.querySelector('.subtotal-display').value = "0";

    // 5. Masukkan baris baru ke tabel
    tbody.appendChild(newRow);

    // 6. Inisialisasi Select2 khusus pada baris baru
    initSelect2(newSelect);

    calculate();
}

function removeRow(btn) {
    const rows = document.querySelectorAll('#itemsTable tbody tr');
    if (rows.length > 1) {
        const row = $(btn).closest('tr');
        row.find('.product-select').select2('destroy');
        row.remove();
        calculate();
    } else {
        Swal.fire({ icon: 'warning', title: 'Minimal harus ada 1 item produk.' });
    }
}

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

function formatNumber(num) {
    return num.toFixed(0).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}
</script>
@endpush
@endsection
