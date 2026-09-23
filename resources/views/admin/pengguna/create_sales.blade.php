@extends('layouts.admin')
@section('title', 'Tambah / Beri Akses Sales')
@section('content')
<div class="section-head">
    <h2>Tambah Pegawai Sales</h2>
    <p>Kelola data pegawai sales di tabel <code>PEGAWAI</code> — sales login cukup pilih nama pegawai, <b>tanpa kata kunci</b>.</p>
</div>

<article class="card" style="max-width:760px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Pilihan Mode: Buat Baru vs Pilih Existing --}}
    <div style="display:flex;gap:12px;margin-bottom:24px;border-bottom:1px solid #e2e8f0;padding-bottom:12px;">
        <button type="button" id="tabNewBtn" onclick="switchMode('new')"
            style="padding:8px 16px;border-radius:8px;font-weight:600;font-size:13px;border:none;cursor:pointer;background:#2563eb;color:#fff;">
            ＋ Buat Pegawai Baru
        </button>
        <button type="button" id="tabExistBtn" onclick="switchMode('existing')"
            style="padding:8px 16px;border-radius:8px;font-weight:600;font-size:13px;border:1px solid #cbd5e1;cursor:pointer;background:#f8fafc;color:#475569;">
            📋 Pilih dari Pegawai Existing ({{ $pegawaiTanpaAkun->count() }})
        </button>
    </div>

    {{-- FORM MODE NEW --}}
    <form action="{{ route('admin.users.store.sales') }}" method="POST" id="formNew">
        @csrf
        <input type="hidden" name="mode" value="new">

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="field">
                <label>Kode Pegawai (KD_PEG) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="kd_peg" value="{{ old('kd_peg', $nextKdPeg) }}" required maxlength="9" style="text-transform:uppercase;">
                <small style="color:var(--muted);font-size:11px;">Otomatis digenerate atau isi manual.</small>
            </div>
            <div class="field">
                <label>Nama Lengkap (NM_PEG) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="nm_peg" value="{{ old('nm_peg') }}" placeholder="Contoh: BUDI SANTOSO" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div class="field">
            <label>Alamat Lengkap (ALM_PEG)</label>
            <input type="text" name="alm_peg" value="{{ old('alm_peg') }}" placeholder="Contoh: Jl. Diponegoro No. 45, Bondowoso" maxlength="65">
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>No HP / WA (HP)</label>
                <input type="text" name="hp" value="{{ old('hp') }}" placeholder="081234567890" maxlength="14">
            </div>
            <div class="field">
                <label>Telepon (TELP1)</label>
                <input type="text" name="telp1" value="{{ old('telp1') }}" placeholder="0332-421234" maxlength="14">
            </div>
            <div class="field">
                <label>Email (E_MAIL)</label>
                <input type="email" name="e_mail" value="{{ old('e_mail') }}" placeholder="sales@contoh.com" maxlength="50">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Wilayah (KD_WIL)</label>
                <select name="kd_wil">
                    <option value="">— Pilih Wilayah —</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ trim($w->KD_WIL) }}" {{ old('kd_wil') == trim($w->KD_WIL) ? 'selected' : '' }}>
                            [{{ trim($w->KD_WIL) }}] {{ trim($w->WILAYAH) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Nama Bank (BANK)</label>
                <input type="text" name="bank" value="{{ old('bank') }}" placeholder="Contoh: BCA / BRI" maxlength="50">
            </div>
            <div class="field">
                <label>No Rekening (NO_REK)</label>
                <input type="text" name="no_rek" value="{{ old('no_rek') }}" placeholder="Contoh: 1234567890" maxlength="14">
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Pegawai Sales Baru</button>
        </div>
    </form>

    {{-- FORM MODE EXISTING --}}
    <form action="{{ route('admin.users.store.sales') }}" method="POST" id="formExisting" style="display:none;">
        @csrf
        <input type="hidden" name="mode" value="existing">

        @if($pegawaiTanpaAkun->isEmpty())
            <div style="background:#fefce8;border:1px solid #fde047;color:#854d0e;padding:14px 16px;border-radius:10px;margin-bottom:16px;">
                <b>Belum ada pegawai sales aktif di database.</b><br>
                <small>Tambahkan lewat mode "Buat Pegawai Baru" di atas.</small>
            </div>
        @else
            <div class="field">
                <label>Pilih Pegawai (KD_PEG) <span style="color:#ef4444;">*</span></label>
                <select name="kd_peg" id="selectExistingPegawai" onchange="fillExistingData(this)">
                    <option value="">— Pilih Pegawai dari Database —</option>
                    @foreach($pegawaiTanpaAkun as $peg)
                        <option value="{{ $peg->KD_PEG }}"
                            data-alm="{{ $peg->ALM_PEG }}"
                            data-hp="{{ $peg->HP }}"
                            data-telp="{{ $peg->TELP1 }}"
                            data-email="{{ $peg->E_MAIL }}"
                            data-wil="{{ trim((string)$peg->KD_WIL) }}"
                            {{ old('kd_peg') == $peg->KD_PEG ? 'selected' : '' }}>
                            [{{ $peg->KD_PEG }}] {{ $peg->NM_PEG }}
                        </option>
                    @endforeach
                </select>
                <small style="color:var(--muted);font-size:11px;">Daftar pegawai sales aktif (tabel PEGAWAI).</small>
            </div>

            <div class="field">
                <label>Alamat Lengkap (ALM_PEG)</label>
                <input type="text" name="alm_peg" id="exist_alm" value="{{ old('alm_peg') }}" placeholder="Alamat pegawai" maxlength="65">
            </div>

            <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="field">
                    <label>No HP / WA (HP)</label>
                    <input type="text" name="hp" id="exist_hp" value="{{ old('hp') }}" placeholder="08..." maxlength="14">
                </div>
                <div class="field">
                    <label>Telepon (TELP1)</label>
                    <input type="text" name="telp1" id="exist_telp" value="{{ old('telp1') }}" placeholder="0332-..." maxlength="14">
                </div>
                <div class="field">
                    <label>Email (E_MAIL)</label>
                    <input type="email" name="e_mail" id="exist_email" value="{{ old('e_mail') }}" placeholder="email@..." maxlength="50">
                </div>
            </div>

            <div class="field">
                <label>Wilayah (KD_WIL)</label>
                <select name="kd_wil" id="exist_wil">
                    <option value="">— Pilih Wilayah —</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ trim($w->KD_WIL) }}">
                            [{{ trim($w->KD_WIL) }}] {{ trim($w->WILAYAH) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
                <button type="submit" class="button button-primary" style="flex:2;">Simpan Data Sales</button>
            </div>
        @endif
    </form>
</article>

<script>
function switchMode(mode) {
    const formNew = document.getElementById('formNew');
    const formExist = document.getElementById('formExisting');
    const btnNew = document.getElementById('tabNewBtn');
    const btnExist = document.getElementById('tabExistBtn');

    if (mode === 'new') {
        formNew.style.display = 'block';
        formExist.style.display = 'none';
        btnNew.style.background = '#2563eb';
        btnNew.style.color = '#fff';
        btnNew.style.border = 'none';
        btnExist.style.background = '#f8fafc';
        btnExist.style.color = '#475569';
        btnExist.style.border = '1px solid #cbd5e1';
    } else {
        formNew.style.display = 'none';
        formExist.style.display = 'block';
        btnExist.style.background = '#2563eb';
        btnExist.style.color = '#fff';
        btnExist.style.border = 'none';
        btnNew.style.background = '#f8fafc';
        btnNew.style.color = '#475569';
        btnNew.style.border = '1px solid #cbd5e1';
    }
}

function fillExistingData(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('exist_alm').value = opt.getAttribute('data-alm') || '';
    document.getElementById('exist_hp').value = opt.getAttribute('data-hp') || '';
    document.getElementById('exist_telp').value = opt.getAttribute('data-telp') || '';
    document.getElementById('exist_email').value = opt.getAttribute('data-email') || '';
    
    const wil = opt.getAttribute('data-wil');
    if (wil) {
        document.getElementById('exist_wil').value = wil;
    }
}
</script>
@endsection
