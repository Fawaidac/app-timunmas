<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * User Management:
 * - Admin  → MST_PENGGUNA (NM_USER + KATAKUNCI)
 * - Sales  → PEGAWAI      (KD_PEG, NM_PEG, ALM_PEG, HP, E_MAIL, KD_WIL + KATAKUNCI)
 *
 * Halaman index menampilkan keduanya sekaligus.
 */
class UserController extends Controller
{
    // ──────────────────────────────────────────────────────────
    // INDEX – gabungkan kedua tabel
    // ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->filled('search') ? strtoupper(trim($request->search)) : null;

        // Admin dari MST_PENGGUNA
        $admins = User::with('pegawai')
            ->when($search, fn ($q) => $q->whereRaw("UPPER(CAST(NM_USER AS VARCHAR(100))) LIKE ?", ["%{$search}%"]))
            ->orderBy('NM_USER')
            ->get()
            ->map(fn ($u) => [
                'source'       => 'admin',
                'id'           => $u->NO_USER,
                'username'     => $u->NM_USER,
                'nama'         => $u->NM_USER,
                'role'         => 'admin',
                'kd_peg'       => $u->KD_PEG,
                'nm_peg'       => $u->pegawai?->NM_PEG ?? '—',
                'alm_peg'      => $u->pegawai?->ALM_PEG ?? null,
                'hp'           => $u->pegawai?->HP ?? $u->pegawai?->TELP1 ?? null,
                'e_mail'       => $u->pegawai?->E_MAIL ?? null,
                'kd_wil'       => $u->pegawai?->KD_WIL ?? null,
                'st_aktif'     => 'AKTIF',
                'has_password' => !empty($u->KATAKUNCI),
                '_model'       => $u,
            ]);

        // Sales dari PEGAWAI
        $sales = Pegawai::salesAktif()
            ->when($search, fn ($q) => $q->where(function ($sub) use ($search) {
                $term = "%{$search}%";
                $sub->whereRaw("UPPER(CAST(KD_PEG AS VARCHAR(100))) LIKE ?", [$term])
                    ->orWhereRaw("UPPER(CAST(NM_PEG AS VARCHAR(100))) LIKE ?", [$term])
                    ->orWhereRaw("UPPER(CAST(COALESCE(ALM_PEG, '') AS VARCHAR(255))) LIKE ?", [$term])
                    ->orWhereRaw("UPPER(CAST(COALESCE(HP, '') AS VARCHAR(100))) LIKE ?", [$term])
                    ->orWhereRaw("UPPER(CAST(COALESCE(E_MAIL, '') AS VARCHAR(100))) LIKE ?", [$term]);
            }))
            ->orderBy('NM_PEG')
            ->get()
            ->map(fn ($p) => [
                'source'       => 'sales',
                'id'           => $p->KD_PEG,
                'username'     => $p->KD_PEG,
                'nama'         => $p->NM_PEG,
                'role'         => 'sales',
                'kd_peg'       => $p->KD_PEG,
                'nm_peg'       => $p->NM_PEG,
                'alm_peg'      => $p->ALM_PEG,
                'hp'           => $p->HP ?: $p->TELP1,
                'e_mail'       => $p->E_MAIL,
                'kd_wil'       => $p->KD_WIL,
                'st_aktif'     => $p->ST_AKTIF ?? 'AKTIF',
                'has_password' => !empty($p->KATAKUNCI),
                '_model'       => $p,
            ]);

        $users = $admins->merge($sales);

        // Manual paginate dari merged collection
        $perPage  = 15;
        $page     = (int) $request->input('page', 1);
        $total    = $users->count();
        $sliced   = $users->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $sliced, $total, $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.pengguna.index', [
            'users'  => $paginator,
            'search' => $request->search,
        ]);
    }

    // ──────────────────────────────────────────────────────────
    // CREATE ADMIN
    // ──────────────────────────────────────────────────────────

    public function createAdmin()
    {
        return view('admin.pengguna.create_admin');
    }

    public function storeAdmin(Request $request)
    {
        $request->validate([
            'nm_user'  => 'required|string|max:50|unique:MST_PENGGUNA,NM_USER',
            'password' => 'required|string|min:4|max:32|confirmed',
        ], [
            'nm_user.required'   => 'Username wajib diisi.',
            'nm_user.unique'     => 'Username sudah dipakai.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        User::create([
            'NO_USER'   => User::nextNoUser(),
            'NM_USER'   => strtoupper(trim($request->nm_user)),
            'KATAKUNCI' => $request->password,
            'NO_OTOR'   => 9, // MANAGER / admin
            'KD_PEG'    => null,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun admin berhasil dibuat.');
    }

    // ──────────────────────────────────────────────────────────
    // CREATE SALES (bisa buat pegawai baru / pilih pegawai existing)
    // ──────────────────────────────────────────────────────────

    public function createSales()
    {
        $nextKdPeg = Pegawai::nextKdPeg();
        $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();

        // Pegawai aktif yang belum punya akun kata kunci
        $pegawaiTanpaAkun = Pegawai::salesAktif()
            ->where(fn ($q) => $q->whereNull('KATAKUNCI')->orWhere('KATAKUNCI', ''))
            ->orderBy('NM_PEG')
            ->get();

        return view('admin.pengguna.create_sales', compact('nextKdPeg', 'wilayahList', 'pegawaiTanpaAkun'));
    }

    public function storeSales(Request $request)
    {
        $mode = $request->input('mode', 'new');

        if ($mode === 'existing') {
            $request->validate([
                'kd_peg'   => 'required|string|exists:PEGAWAI,KD_PEG',
                'password' => 'required|string|min:4|max:32|confirmed',
                'alm_peg'  => 'nullable|string|max:65',
                'hp'       => 'nullable|string|max:14',
                'telp1'    => 'nullable|string|max:14',
                'e_mail'   => 'nullable|email|max:50',
                'kd_wil'   => 'nullable|string|max:20',
            ], [
                'kd_peg.required'    => 'Pilih pegawai terlebih dahulu.',
                'kd_peg.exists'      => 'Kode pegawai tidak ditemukan.',
                'password.required'  => 'Password wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);

            $pegawai = Pegawai::where('KD_PEG', $request->kd_peg)->firstOrFail();
            $updateData = ['KATAKUNCI' => $request->password];

            if ($request->filled('alm_peg')) $updateData['ALM_PEG'] = $request->alm_peg;
            if ($request->filled('hp'))      $updateData['HP']      = $request->hp;
            if ($request->filled('telp1'))   $updateData['TELP1']   = $request->telp1;
            if ($request->filled('e_mail'))  $updateData['E_MAIL']  = $request->e_mail;
            if ($request->filled('kd_wil'))  $updateData['KD_WIL']  = $request->kd_wil;

            $pegawai->update($updateData);

            return redirect()->route('admin.users.index')
                ->with('success', "Akun sales untuk [{$pegawai->NM_PEG}] berhasil diaktifkan.");
        }

        // Mode New Pegawai
        $request->validate([
            'kd_peg'    => 'required|string|max:9|unique:PEGAWAI,KD_PEG',
            'nm_peg'    => 'required|string|max:50',
            'alm_peg'   => 'nullable|string|max:65',
            'hp'        => 'nullable|string|max:14',
            'telp1'     => 'nullable|string|max:14',
            'e_mail'    => 'nullable|email|max:50',
            'kd_wil'    => 'nullable|string|max:20',
            'bank'      => 'nullable|string|max:50',
            'no_rek'    => 'nullable|string|max:14',
            'password'  => 'required|string|min:4|max:32|confirmed',
        ], [
            'kd_peg.required'    => 'Kode pegawai wajib diisi.',
            'kd_peg.unique'      => 'Kode pegawai sudah terdaftar.',
            'nm_peg.required'    => 'Nama pegawai wajib diisi.',
            'password.required'  => 'Password wajib diisi.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        Pegawai::create([
            'KD_PEG'    => strtoupper(trim($request->kd_peg)),
            'NM_PEG'    => strtoupper(trim($request->nm_peg)),
            'ALM_PEG'   => $request->alm_peg,
            'HP'        => $request->hp,
            'TELP1'     => $request->telp1,
            'E_MAIL'    => $request->e_mail,
            'KD_WIL'    => $request->kd_wil,
            'BANK'      => $request->bank,
            'NO_REK'    => $request->no_rek,
            'STS_SALES' => 'YA',
            'ST_AKTIF'  => 'AKTIF',
            'KATAKUNCI' => $request->password,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Pegawai sales baru [{$request->nm_peg}] berhasil dibuat.");
    }

    // ──────────────────────────────────────────────────────────
    // EDIT
    // ──────────────────────────────────────────────────────────

    public function edit(Request $request, $id)
    {
        $source = $request->query('source', 'admin');

        if ($source === 'sales') {
            $pegawai = Pegawai::where('KD_PEG', $id)->firstOrFail();
            $wilayahList = DB::connection('firebird')->table('WILAYAH')->get();
            return view('admin.pengguna.edit_sales', compact('pegawai', 'wilayahList'));
        }

        $user    = User::where('NO_USER', $id)->firstOrFail();
        $pegawai = Pegawai::salesAktif()->orderBy('NM_PEG')->get();
        return view('admin.pengguna.edit', compact('user', 'pegawai'));
    }

    public function update(Request $request, $id)
    {
        $source = $request->input('source', 'admin');

        if ($source === 'sales') {
            $request->validate([
                'nm_peg'    => 'required|string|max:50',
                'alm_peg'   => 'nullable|string|max:65',
                'hp'        => 'nullable|string|max:14',
                'telp1'     => 'nullable|string|max:14',
                'e_mail'    => 'nullable|email|max:50',
                'kd_wil'    => 'nullable|string|max:20',
                'bank'      => 'nullable|string|max:50',
                'no_rek'    => 'nullable|string|max:14',
                'st_aktif'  => 'nullable|in:AKTIF,TIDAK',
                'sts_sales' => 'nullable|in:YA,TIDAK',
                'password'  => 'nullable|string|min:4|max:32|confirmed',
            ], [
                'nm_peg.required'    => 'Nama pegawai wajib diisi.',
                'password.confirmed' => 'Konfirmasi password tidak cocok.',
            ]);

            $pegawai = Pegawai::where('KD_PEG', $id)->firstOrFail();

            $updateData = [
                'NM_PEG'    => strtoupper(trim($request->nm_peg)),
                'ALM_PEG'   => $request->alm_peg,
                'HP'        => $request->hp,
                'TELP1'     => $request->telp1,
                'E_MAIL'    => $request->e_mail,
                'KD_WIL'    => $request->kd_wil,
                'BANK'      => $request->bank,
                'NO_REK'    => $request->no_rek,
                'ST_AKTIF'  => $request->st_aktif ?: 'AKTIF',
                'STS_SALES' => $request->sts_sales ?: 'YA',
            ];

            if ($request->filled('password')) {
                $updateData['KATAKUNCI'] = $request->password;
            }

            $pegawai->update($updateData);

            return redirect()->route('admin.users.index')
                ->with('success', "Data sales [{$pegawai->NM_PEG}] berhasil diperbarui.");
        }

        // Admin update
        $request->validate([
            'nm_user'  => 'required|string|max:50|unique:MST_PENGGUNA,NM_USER,' . $id . ',NO_USER',
            'password' => 'nullable|string|min:4|max:32|confirmed',
        ], [
            'nm_user.unique'     => 'Username sudah dipakai akun lain.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::where('NO_USER', $id)->firstOrFail();
        $data = ['NM_USER' => strtoupper(trim($request->nm_user))];
        if ($request->filled('password')) {
            $data['KATAKUNCI'] = $request->password;
        }
        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Akun admin berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────
    // DESTROY
    // ──────────────────────────────────────────────────────────

    public function destroy(Request $request, $id)
    {
        $source = $request->input('source', 'admin');

        if ($source === 'sales') {
            $pegawai = Pegawai::where('KD_PEG', $id)->firstOrFail();
            // Cabut akses sales = hapus KATAKUNCI
            $pegawai->update(['KATAKUNCI' => null]);
            return redirect()->route('admin.users.index')
                ->with('success', "Akses login sales [{$pegawai->NM_PEG}] berhasil dicabut.");
        }

        // Jangan hapus diri sendiri
        if (Auth::guard('web')->id() == $id) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        User::where('NO_USER', $id)->firstOrFail()->delete();
        return redirect()->route('admin.users.index')
            ->with('success', 'Akun admin berhasil dihapus.');
    }
}
