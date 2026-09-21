<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pilih Identitas Pegawai - SFA</title>
    @vite(['resources/css/app.css'])
    <style>
        * { box-sizing: border-box; }

        body {
            background: linear-gradient(135deg, #c2410c 0%, #ea580c 50%, #f97316 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', 'Segoe UI', sans-serif;
            padding: 24px;
            margin: 0;
        }

        .select-card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 24px 80px rgba(0,0,0,.25), 0 4px 20px rgba(0,0,0,.12);
            padding: 40px;
            width: 100%;
            max-width: 480px;
            animation: slideUp .35s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 28px;
        }

        .brand-icon {
            width: 48px; height: 48px;
            background: linear-gradient(135deg, #ea580c, #f97316);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
            color: #fff;
            font-weight: 800;
            box-shadow: 0 4px 14px rgba(234,88,12,.4);
        }

        .brand-text h1 {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            margin: 0;
        }

        .brand-text p {
            font-size: 12px;
            color: #94a3b8;
            margin: 0;
        }

        .info-box {
            background: #fff7ed;
            border: 1.5px solid #fed7aa;
            border-radius: 12px;
            padding: 13px 16px;
            margin-bottom: 24px;
            font-size: 13px;
            color: #9a3412;
            display: flex;
            gap: 10px;
            align-items: flex-start;
            line-height: 1.5;
        }

        .info-box .icon { font-size: 20px; flex-shrink: 0; margin-top: 1px; }

        h2 {
            font-size: 21px;
            font-weight: 800;
            color: #1e293b;
            margin: 0 0 6px;
        }

        p.subtitle {
            font-size: 13px;
            color: #64748b;
            margin: 0 0 24px;
            line-height: 1.5;
        }

        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            color: #64748b;
            margin-bottom: 7px;
        }

        .field select {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            color: #1e293b;
            outline: none;
            transition: border .2s, box-shadow .2s;
            background: #f8fafc;
            cursor: pointer;
        }

        .field select:focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(234,88,12,.15);
            background: #fff;
        }

        .pegawai-preview {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 13px;
            color: #166534;
            margin-top: 10px;
            display: none;
            line-height: 1.6;
        }

        .pegawai-preview strong { font-weight: 700; }
        .pegawai-preview code {
            background: #dcfce7;
            padding: 1px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #ea580c, #c2410c);
            color: #fff;
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all .2s;
            box-shadow: 0 4px 14px rgba(234,88,12,.4);
            margin-top: 4px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(234,88,12,.45);
        }

        .btn-submit:active { transform: translateY(0); }

        .error-msg {
            background: #fee2e2;
            border: 1.5px solid #fca5a5;
            color: #991b1b;
            padding: 11px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px;
        }

        .logout-section {
            text-align: center;
            margin-top: 20px;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            font-size: 12px;
            color: #94a3b8;
        }

        .logout-section form { display: inline; }

        .btn-logout {
            background: none;
            border: none;
            color: #ea580c;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            padding: 0;
            text-decoration: underline;
        }

        .btn-logout:hover { color: #c2410c; }
    </style>
</head>
<body>
    <div class="select-card">
        {{-- Brand --}}
        <div class="brand">
            <div class="brand-icon">S</div>
            <div class="brand-text">
                <h1>ASRI SFA</h1>
                <p>Sales Force Automation</p>
            </div>
        </div>

        {{-- Info akun yang sedang login --}}
        <div class="info-box">
            <div class="icon">👋</div>
            <div>
                Halo, <strong>{{ $user->NM_USER }}</strong>!
                Akun Anda terdaftar sebagai Sales. Silakan pilih identitas pegawai Anda untuk melanjutkan ke dashboard.
            </div>
        </div>

        <h2>Pilih Identitas Pegawai</h2>
        <p class="subtitle">Pilih nama Anda dari daftar pegawai sales yang aktif.</p>

        @if($errors->any())
            <div class="error-msg">
                @foreach($errors->all() as $err)
                    ⚠️ {{ $err }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('auth.select-pegawai.post') }}">
            @csrf

            <div class="field">
                <label>Nama Pegawai Anda</label>
                <select name="kd_peg" id="kd_peg" required onchange="updatePreview(this)">
                    <option value="">— Pilih nama Anda —</option>
                    @foreach($pegawaiList as $peg)
                        <option
                            value="{{ trim($peg->KD_PEG) }}"
                            data-nama="{{ trim($peg->NM_PEG) }}"
                            data-kd="{{ trim($peg->KD_PEG) }}"
                            data-wil="{{ trim($peg->KD_WIL ?? '-') }}"
                            {{ old('kd_peg') === trim($peg->KD_PEG) ? 'selected' : '' }}>
                            {{ trim($peg->NM_PEG) }} &nbsp;({{ trim($peg->KD_PEG) }})
                        </option>
                    @endforeach
                </select>

                <div class="pegawai-preview" id="peg-preview">
                    ✅ Anda akan login sebagai:<br>
                    <strong id="peg-nama"></strong>
                    &nbsp;·&nbsp; Kode: <code id="peg-kd"></code>
                    &nbsp;·&nbsp; Wilayah: <span id="peg-wil"></span>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                ✅ &nbsp;Lanjutkan ke Dashboard Sales
            </button>
        </form>

        <div class="logout-section">
            Bukan akun Anda? &nbsp;
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">Logout dan masuk ulang</button>
            </form>
        </div>
    </div>

    <script>
        function updatePreview(sel) {
            const opt = sel.options[sel.selectedIndex];
            const preview = document.getElementById('peg-preview');
            if (!opt.value) {
                preview.style.display = 'none';
                return;
            }
            document.getElementById('peg-nama').textContent = opt.dataset.nama;
            document.getElementById('peg-kd').textContent   = opt.dataset.kd;
            document.getElementById('peg-wil').textContent  = opt.dataset.wil;
            preview.style.display = 'block';
        }

        // Auto-preview jika ada pilihan awal (setelah validation error)
        document.addEventListener('DOMContentLoaded', function () {
            const sel = document.getElementById('kd_peg');
            if (sel && sel.value) updatePreview(sel);
        });
    </script>
</body>
</html>
