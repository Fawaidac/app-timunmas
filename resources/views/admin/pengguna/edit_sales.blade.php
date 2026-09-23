@extends('layouts.admin')
@section('title', 'Edit Pegawai Sales - Admin')
@section('content')
<div class="section-head">
    <h2>Edit Pegawai Sales</h2>
    <p>Perbarui data profil untuk <b>{{ $pegawai->NM_PEG }}</b> (<code>PEGAWAI</code>) — tanpa kata kunci, sales login tanpa password</p>
</div>

<article class="card" style="max-width:760px;">
    @if($errors->any())
        <div style="background:#fee2e2;border:1px solid #fca5a5;color:#991b1b;padding:12px 16px;border-radius:10px;margin-bottom:20px;">
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.users.update', $pegawai->KD_PEG) }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="source" value="sales">

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 2fr;gap:16px;">
            <div class="field">
                <label>Kode Pegawai (KD_PEG)</label>
                <input type="text" value="{{ $pegawai->KD_PEG }}" readonly style="background:#f1f5f9;cursor:not-allowed;font-weight:700;">
                <small style="color:var(--muted);font-size:11px;">Primary key di database, tidak dapat diubah.</small>
            </div>
            <div class="field">
                <label>Nama Pegawai (NM_PEG) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="nm_peg" value="{{ old('nm_peg', $pegawai->NM_PEG) }}" required maxlength="50" style="text-transform:uppercase;">
            </div>
        </div>

        <div class="field">
            <label>Alamat Lengkap (ALM_PEG)</label>
            <input type="text" name="alm_peg" value="{{ old('alm_peg', $pegawai->ALM_PEG) }}" placeholder="Alamat lengkap sales" maxlength="65">
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>No HP / WA (HP)</label>
                <input type="text" name="hp" value="{{ old('hp', $pegawai->HP) }}" placeholder="08..." maxlength="14">
            </div>
            <div class="field">
                <label>Telepon (TELP1)</label>
                <input type="text" name="telp1" value="{{ old('telp1', $pegawai->TELP1) }}" placeholder="0332-..." maxlength="14">
            </div>
            <div class="field">
                <label>Email (E_MAIL)</label>
                <input type="email" name="e_mail" value="{{ old('e_mail', $pegawai->E_MAIL) }}" placeholder="email@..." maxlength="50">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
            <div class="field">
                <label>Wilayah (KD_WIL)</label>
                <select name="kd_wil">
                    <option value="">— Pilih Wilayah —</option>
                    @foreach($wilayahList as $w)
                        <option value="{{ trim($w->KD_WIL) }}" {{ old('kd_wil', trim((string)$pegawai->KD_WIL)) == trim($w->KD_WIL) ? 'selected' : '' }}>
                            [{{ trim($w->KD_WIL) }}] {{ trim($w->WILAYAH) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Nama Bank (BANK)</label>
                <input type="text" name="bank" value="{{ old('bank', $pegawai->BANK) }}" placeholder="BCA / BRI" maxlength="50">
            </div>
            <div class="field">
                <label>No Rekening (NO_REK)</label>
                <input type="text" name="no_rek" value="{{ old('no_rek', $pegawai->NO_REK) }}" placeholder="1234567890" maxlength="14">
            </div>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Status Aktif (ST_AKTIF)</label>
                <select name="st_aktif">
                    <option value="AKTIF" {{ old('st_aktif', $pegawai->ST_AKTIF) === 'AKTIF' ? 'selected' : '' }}>AKTIF</option>
                    <option value="TIDAK" {{ old('st_aktif', $pegawai->ST_AKTIF) === 'TIDAK' ? 'selected' : '' }}>NONAKTIF</option>
                </select>
            </div>
            <div class="field">
                <label>Status Sales (STS_SALES)</label>
                <select name="sts_sales">
                    <option value="YA" {{ old('sts_sales', $pegawai->STS_SALES) === 'YA' ? 'selected' : '' }}>YA (Sales Lapangan)</option>
                    <option value="TIDAK" {{ old('sts_sales', $pegawai->STS_SALES) === 'TIDAK' ? 'selected' : '' }}>TIDAK (Bukan Sales)</option>
                </select>
            </div>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Simpan Perubahan Sales</button>
        </div>
    </form>
</article>
@endsection
