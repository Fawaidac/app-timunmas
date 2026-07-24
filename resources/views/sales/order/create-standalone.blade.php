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

    <form action="{{ route('sales.order.store') }}" method="POST" id="orderForm">
        @csrf
        <input type="hidden" name="visit_id" value="{{ $selectedVisitId ?? '' }}">
        <input type="hidden" name="customer_id" value="{{ $selectedCustomerId ?? '' }}">
        <!-- Grid Form Atas -->
        <div class="form-grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
            <div class="field">
                <label style="font-weight: 600; font-size: 13px; margin-bottom: 6px; display: block;">Customer <span style="color:#ef4444;">*</span></label>
                <select name="customer_id" class="form-control custom-input" required>
                    <option value="">-- Pilih Customer --</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" 
                            {{ (old('customer_id', $selectedCustomerId) == $customer->id) ? 'selected' : '' }}>
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
                                        @php
                                            $totalStock = $product->warehouses->sum('pivot.stock_quantity');
                                        @endphp
                                        <option value="{{ $product->id }}" 
                                                data-price="{{ $product->price }}" 
                                                data-unit="{{ $product->unit }}" 
                                                data-stock="{{ $totalStock }}">
                                            {{ $product->name }} ({{ $product->sku }}) - Stok: {{ $totalStock }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
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
            <button type="submit" class="button button-primary" style="flex: 2; border-radius: 8px; padding: 12px; font-weight: 600;">Simpan Sales Order</button>
        </div>
    </form>
</article>

@push('scripts')

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
function initSelect2(element) {
    const target = element ? $(element) : $('.product-select');
    target.select2({
        placeholder: "-- Pilih Produk --",
        allowClear: true,
        width: '100%'
    });
}

// Toggle payment term field
$(document).ready(function() {
    // Inisialisasi Select2 di awal
    initSelect2();

    // Toggle Payment Term
    $('#payment_type').on('change', function() {
        $('#termField').toggle(this.value === 'credit');
    });

    if ($('#payment_type').val() === 'credit') {
        $('#termField').show();
    }

    calculate();
});

// Initial check
if (document.getElementById('payment_type').value === 'credit') {
    document.getElementById('termField').style.display = 'block';
}

function fillPrice(select) {
    const row = select.closest('tr');
    const option = select.options[select.selectedIndex];
    
    const price = option ? (option.dataset.price || 0) : 0;
    const stock = option ? (parseFloat(option.dataset.stock) || 0) : 0;
    
    // Set harga
    row.querySelector('.price-input').value = price;
    
    // Set max stok pada input qty
    const qtyInput = row.querySelector('.qty-input');
    if (option && option.value !== "") {
        qtyInput.setAttribute('max', stock);
    } else {
        qtyInput.removeAttribute('max');
    }
    
    // Jalankan validasi qty
    validateQty(qtyInput);
}

function validateQty(input) {
    const row = input.closest('tr');
    const select = row.querySelector('.product-select');
    const option = select.options[select.selectedIndex];
    
    if (!option || !option.value) return;

    const maxStock = parseFloat(option.dataset.stock) || 0;
    let currentQty = parseFloat(input.value) || 0;

    // Jika stok 0, paksa nilai qty jadi 0
    if (maxStock <= 0) {
        alert(`Stok produk "${option.text.split('-')[0].trim()}" sedang KOSONG (0)!`);
        input.value = 0;
        calculate();
        return;
    }

    // Jika qty diinput Melebihi Stok
    if (currentQty > maxStock) {
        alert(`Jumlah melebihi stok yang tersedia! Maksimal stok hanya ${maxStock}.`);
        input.value = maxStock;
    }

    // Jika qty kurang dari 1
    if (currentQty < 1 && maxStock > 0) {
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
