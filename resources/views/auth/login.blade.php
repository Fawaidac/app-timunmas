<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - SFA Timun Mas</title>
    @vite(['resources/css/app.css'])

    <!-- Select2 CSS & Font -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        * { box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #9a3412 0%, #c2410c 45%, #ea580c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
        }

        .login-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.25), 0 4px 16px rgba(0, 0, 0, 0.1);
            max-width: 440px;
            width: 100%;
            padding: 36px 32px;
            animation: slideUp .35s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand-header {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #c2410c, #ea580c);
            border-radius: 18px;
            margin-bottom: 14px;
            box-shadow: 0 8px 20px rgba(234, 88, 12, 0.35);
        }

        .brand-logo span {
            font-size: 32px;
            font-weight: 900;
            color: #ffffff;
        }

        .brand-title {
            margin: 0;
            font-size: 22px;
            font-weight: 800;
            color: #172033;
        }

        .brand-subtitle {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 13px;
        }

        /* Mode Tabs */
        .login-tabs {
            display: flex;
            background: #f1f5f9;
            padding: 4px;
            border-radius: 12px;
            margin-bottom: 22px;
            gap: 4px;
        }

        .login-tab-btn {
            flex: 1;
            padding: 10px 12px;
            border: none;
            background: transparent;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .login-tab-btn.active {
            background: #ffffff;
            color: #ea580c;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .error-alert {
            padding: 12px 14px;
            background: #fee2e2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            margin-bottom: 18px;
            color: #dc2626;
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            color: #4b5563;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .field input,
        .field select {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #e2e8f0;
            border-radius: 11px;
            outline: none;
            background: #f8fafc;
            font-size: 14px;
            color: #172033;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .field input:focus,
        .field select:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234, 88, 12, 0.15);
            background: #ffffff;
        }

        .pegawai-info-hint {
            margin-top: 8px;
            font-size: 11px;
            color: #64748b;
            line-height: 1.4;
        }

        .remember-row {
            display: flex;
            align-items: center;
            margin: 18px 0 22px;
        }

        .remember-row input[type="checkbox"] {
            width: 17px;
            height: 17px;
            cursor: pointer;
            accent-color: #ea580c;
        }

        .remember-row label {
            margin: 0 0 0 8px;
            font-size: 13px;
            color: #4b5563;
            cursor: pointer;
        }

        .footer-note {
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }

        /* === KUSTOMISASI SELECT2 MODERN (SAMA SEPERTI INSERT KUNJUNGAN) === */
        .select2-container--default .select2-selection--single {
            border: 1px solid #cbd5e1 !important;
            border-radius: 10px !important;
            height: 44px !important;
            padding: 6px 12px !important;
            background-color: #f8fafc !important;
            transition: all 0.2s ease-in-out !important;
            display: flex !important;
            align-items: center !important;
        }

        .select2-container--default.select2-container--open .select2-selection--single,
        .select2-container--default .select2-selection--single:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15) !important;
            background-color: #ffffff !important;
            outline: none !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #1e293b !important;
            font-size: 14px !important;
            font-weight: 500 !important;
            padding-left: 2px !important;
            line-height: normal !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 42px !important;
            right: 10px !important;
        }

        .select2-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 10px !important;
            box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.12), 0 4px 8px -2px rgba(0, 0, 0, 0.06) !important;
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
            border-radius: 7px !important;
            padding: 8px 12px !important;
            font-size: 13px !important;
            outline: none !important;
        }

        .select2-search--dropdown .select2-search__field:focus {
            border-color: #f97316 !important;
            box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.15) !important;
        }

        .select2-results__option {
            padding: 9px 14px !important;
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
</head>
<body>

<div class="login-card">
    <div class="brand-header">
        <div class="brand-logo">
            <span>A</span>
        </div>
        <h1 class="brand-title">ASRI SFA</h1>
        <p class="brand-subtitle">Sales Force Automation System</p>
    </div>

    <!-- Toggle Mode Tab: Sales vs Admin -->
    <div class="login-tabs">
        <button type="button" class="login-tab-btn active" id="tabSalesBtn" onclick="switchLoginMode('sales')">
            <span>👤</span> Sales (Pilih Pegawai)
        </button>
        <button type="button" class="login-tab-btn" id="tabAdminBtn" onclick="switchLoginMode('admin')">
            <span>🏢</span> Admin / Kantor
        </button>
    </div>

    @if ($errors->any())
        <div class="error-alert">
            <span>⚠️</span>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" id="loginForm">
        @csrf
        <input type="hidden" name="login_type" id="loginTypeInput" value="sales">

        <!-- ──────────────── SECTION SALES (SELECT 2 PILIH PEGAWAI) ──────────────── -->
        <div id="sectionSales">
            <div class="field" style="margin-bottom: 20px;">
                <label for="selectPegawai">Pilih Pegawai Sales</label>
                <!-- class pegawai-select untuk Select2 interaktif -->
                <select name="kd_peg" id="selectPegawai" class="pegawai-select" style="width: 100%;" required>
                    <option value="">-- Cari / Pilih Pegawai Sales --</option>
                    @if(isset($pegawaiList))
                        @foreach($pegawaiList as $p)
                            <option value="{{ trim($p->KD_PEG) }}" {{ old('kd_peg') == trim($p->KD_PEG) ? 'selected' : '' }}>
                                {{ trim($p->NM_PEG) }} ({{ trim($p->KD_PEG) }})
                            </option>
                        @endforeach
                    @endif
                </select>
                <div class="pegawai-info-hint">
                    💡 Cari atau pilih nama pegawai Anda untuk langsung masuk ke dashboard Sales.
                </div>
            </div>
        </div>

        <!-- ──────────────── SECTION ADMIN (USERNAME + PASSWORD) ──────────────── -->
        <div id="sectionAdmin" style="display: none;">
            <div class="field" style="margin-bottom: 16px;">
                <label for="inputUsername">Nama User Admin</label>
                <input type="text" name="username" id="inputUsername" value="{{ old('username') }}" placeholder="Contoh: HENIS, WEBTEST">
            </div>

            <div class="field" style="margin-bottom: 16px;">
                <label for="inputPassword">Kata Kunci</label>
                <input type="password" name="password" id="inputPassword" placeholder="Masukkan kata kunci admin">
            </div>
        </div>

        <div class="remember-row">
            <input type="checkbox" name="remember" id="remember" checked>
            <label for="remember">Ingat sesi saya di perangkat ini</label>
        </div>

        <button type="submit" class="button button-primary full-width" id="submitBtn" style="padding: 13px; font-size: 15px;">
            Masuk sebagai Sales
        </button>
    </form>

    <div class="footer-note">
        © 2026 ASRI - All Rights Reserved
    </div>
</div>

<!-- JS jQuery & Select2 -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Inisialisasi Select2 pada dropdown pegawai sales
    $('.pegawai-select').select2({
        placeholder: "-- Cari / Pilih Pegawai Sales --",
        allowClear: true,
        width: '100%'
    });

    @if(old('login_type') === 'admin' || (old('username') && !old('kd_peg')))
        switchLoginMode('admin');
    @else
        switchLoginMode('sales');
    @endif
});

function switchLoginMode(mode) {
    const sectionSales   = $('#sectionSales');
    const sectionAdmin   = $('#sectionAdmin');
    const selectPegawai  = $('#selectPegawai');
    const inputUsername  = $('#inputUsername');
    const inputPassword  = $('#inputPassword');
    const tabSalesBtn    = $('#tabSalesBtn');
    const tabAdminBtn    = $('#tabAdminBtn');
    const loginTypeInput = $('#loginTypeInput');
    const submitBtn      = $('#submitBtn');

    if (mode === 'sales') {
        sectionSales.show();
        sectionAdmin.hide();
        selectPegawai.prop('required', true);
        inputUsername.prop('required', false);
        inputPassword.prop('required', false);
        tabSalesBtn.addClass('active');
        tabAdminBtn.removeClass('active');
        loginTypeInput.val('sales');
        submitBtn.text('Masuk sebagai Sales');
        
        // Re-adjust select2 width if needed
        $('.pegawai-select').select2({
            placeholder: "-- Cari / Pilih Pegawai Sales --",
            allowClear: true,
            width: '100%'
        });
    } else {
        sectionSales.hide();
        sectionAdmin.show();
        selectPegawai.prop('required', false);
        inputUsername.prop('required', true);
        inputPassword.prop('required', true);
        tabSalesBtn.removeClass('active');
        tabAdminBtn.addClass('active');
        loginTypeInput.val('admin');
        submitBtn.text('Masuk sebagai Admin');
    }
}
</script>

</body>
</html>
