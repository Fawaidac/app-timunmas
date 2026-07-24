@extends('layouts.app')

@section('title', 'Jadwalkan Kunjungan')
@section('page_title', 'Jadwalkan Kunjungan Baru')
@section('page_description', 'Buat jadwal kunjungan ke customer')

@push('styles')
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* === KUSTOMISASI SELECT2 MODERN === */
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

        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15) !important;
            outline: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            padding-left: 2px !important;
            line-height: normal !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 38px !important;
            right: 8px !important;
        }

        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
            overflow: hidden !important;
            z-index: 9999 !important;
            background-color: #ffffff !important;
        }

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

        .select2-results__option {
            padding: 8px 12px !important;
            font-size: 13px !important;
            color: #334155 !important;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #fff7ed !important;
            color: #ea580c !important;
            font-weight: 600 !important;
        }

        .select2-container--default .select2-results__option[aria-selected="true"] {
            background-color: #ffedd5 !important;
            color: #c2410c !important;
            font-weight: 600 !important;
        }
    </style>
@endpush

@section('content')
<div class="section-head">
    <h2>Jadwalkan Kunjungan Baru</h2>
    <p>Pilih customer dan tujuan kunjungan.</p>
</div>

<article class="card" style="width: 70%; max-width: 100%;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('sales.kunjungan.store') }}" method="POST">
        @csrf

        <div class="field" style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Customer <span style="color:#ef4444;">*</span></label>
            <!-- class customer-select ditambahkan untuk inisialisasi Select2 -->
            <select name="customer_id" class="form-control customer-select" required>
                <option value="">-- Pilih Customer --</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->code }} - {{ $customer->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="field" style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Tanggal Kunjungan <span style="color:#ef4444;">*</span></label>
            <input type="date" name="visit_date" class="form-control" value="{{ old('visit_date', date('Y-m-d')) }}" required>
        </div>

        <div class="field" style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Tujuan Kunjungan <span style="color:#ef4444;">*</span></label>
            <select name="purpose" class="form-control" required>
                <option value="">-- Pilih Tujuan --</option>
                <option value="merchandising" {{ old('purpose') === 'merchandising' ? 'selected' : '' }}>Merchandising</option>
                <option value="collection" {{ old('purpose') === 'collection' ? 'selected' : '' }}>Penagihan</option>
                <option value="order" {{ old('purpose') === 'order' ? 'selected' : '' }}>Order</option>
            </select>
        </div>

        <div class="field" style="margin-bottom: 16px;">
            <label style="display: block; margin-bottom: 6px; font-weight: 600; font-size: 13px;">Catatan (Opsional)</label>
            <textarea name="notes" class="form-control" rows="3" placeholder="Catatan tambahan...">{{ old('notes') }}</textarea>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('sales.kunjungan.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Jadwal</button>
        </div>
    </form>
</article>
@endsection

@push('scripts')
    <!-- Import JS jQuery & Select2 -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2 pada dropdown customer
            $('.customer-select').select2({
                placeholder: "-- Pilih Customer --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
@endpush