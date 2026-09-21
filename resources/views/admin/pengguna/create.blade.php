@extends('layouts.admin')

@section('title', 'Tambah Pengguna - Admin')
@section('page_title', 'Tambah Pengguna Baru')
@section('page_description', 'Buat akun pengguna baru dari MST_PENGGUNA')

@section('content')
<div class="section-head">
    <h2>Tambah Pengguna Baru</h2>
    <p>Buat akun baru untuk <strong>Admin</strong> (dari MST_PENGGUNA) atau <strong>Sales</strong> (link ke PEGAWAI).</p>
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

    <form action="{{ route('admin.users.store') }}" method="POST" id="createUserForm">
        @csrf

        {{-- Info box --}}
        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 16px;margin-bottom:20px;font-size:13px;color:#92400e;">
            <b>📋 Sumber data:</b><br>
            • <b>Admin</b> → akun disimpan di <code>MST_PENGGUNA</code><br>
            • <b>Sales</b> → akun disimpan di <code>MST_PENGGUNA</code> + wajib link ke pegawai di tabel <code>PEGAWAI</code>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Username (NM_USER) <span style="color:#ef4444;">*</span></label>
                <input type="text" name="nm_user" class="form-control"
                    value="{{ old('nm_user') }}"
                    placeholder="Contoh: BUDI.S"
                    style="text-transform:uppercase;"
                    required>
                <small style="color:var(--muted);font-size:11px;">Dipakai untuk login. Akan disimpan uppercase.</small>
            </div>
            <div class="field">
                <label>Role <span style="color:#ef4444;">*</span></label>
                <select name="role" class="form-control" id="roleSelect" required onchange="togglePegawai(this.value)">
                    <option value="">— Pilih Role —</option>
                    <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="sales" {{ old('role') === 'sales' ? 'selected' : '' }}>Sales</option>
                </select>
            </div>
        </div>

        {{-- Dropdown pegawai (hanya muncul saat role = sales) --}}
        <div class="field" id="pegawaiField" style="{{ old('role') === 'sales' ? '' : 'display:none;' }}">
            <label>Pegawai / Sales <span style="color:#ef4444;">*</span></label>
            <select name="kd_peg" class="form-control" id="kd_peg_select">
                <option value="">— Pilih Pegawai dari tabel PEGAWAI —</option>
                @foreach($pegawai as $peg)
                    <option value="{{ $peg->KD_PEG }}" {{ old('kd_peg') == $peg->KD_PEG ? 'selected' : '' }}>
                        [{{ $peg->KD_PEG }}] {{ $peg->NM_PEG }}
                    </option>
                @endforeach
            </select>
            <small style="color:var(--muted);font-size:11px;">Hanya pegawai aktif (STS_SALES = 'YA') yang ditampilkan.</small>
        </div>

        <div class="form-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
            <div class="field">
                <label>Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password" class="form-control"
                    placeholder="Min. 4 karakter" required minlength="4">
                <small style="color:var(--muted);font-size:11px;">Disimpan as-is (tanpa enkripsi) sesuai sistem legacy.</small>
            </div>
            <div class="field">
                <label>Konfirmasi Password <span style="color:#ef4444;">*</span></label>
                <input type="password" name="password_confirmation" class="form-control"
                    placeholder="Ulangi password" required>
            </div>
        </div>

        <div class="button-row" style="margin-top:24px;display:flex;gap:12px;">
            <a href="{{ route('admin.users.index') }}" class="button button-soft" style="flex:1;text-align:center;">Batal</a>
            <button type="submit" class="button button-primary" style="flex:2;">Buat Pengguna</button>
        </div>
    </form>
</article>

<script>
function togglePegawai(role) {
    const field = document.getElementById('pegawaiField');
    const select = document.getElementById('kd_peg_select');
    if (role === 'sales') {
        field.style.display = '';
        select.required = true;
    } else {
        field.style.display = 'none';
        select.required = false;
        select.value = '';
    }
}
// Init on load
document.addEventListener('DOMContentLoaded', function () {
    togglePegawai(document.getElementById('roleSelect').value);
});
</script>
@endsection
