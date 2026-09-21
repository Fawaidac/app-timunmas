<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('web')->check()) {
            $user = Auth::guard('web')->user();
            return $this->redirectByRole($user);
        }
        if (Auth::guard('sales')->check()) {
            return $this->redirectSales();
        }

        // Ambil daftar pegawai sales aktif untuk dropdown di halaman login
        $pegawaiList = Pegawai::salesAktif()->orderBy('NM_PEG')->get();

        return view('auth.login', compact('pegawaiList'));
    }

    public function login(Request $request)
    {
        $loginType = $request->input('login_type', 'sales'); // 'sales' atau 'admin'

        // ──────────────────────────────────────────────────────────────
        // A) LOGIN SEBAGAI SALES (Cukup Pilih Pegawai, Tanpa Password)
        // ──────────────────────────────────────────────────────────────
        if ($loginType === 'sales' || $request->filled('kd_peg')) {
            $request->validate([
                'kd_peg' => 'required|string',
            ], [
                'kd_peg.required' => 'Silakan pilih nama pegawai sales Anda.',
            ]);

            $kdPeg = strtoupper(trim($request->kd_peg));

            $sales = Pegawai::whereRaw("UPPER(CAST(TRIM(KD_PEG) AS VARCHAR(100))) = ?", [$kdPeg])
                ->orWhereRaw("UPPER(CAST(TRIM(NM_PEG) AS VARCHAR(100))) = ?", [$kdPeg])
                ->first();

            if (!$sales) {
                throw ValidationException::withMessages([
                    'kd_peg' => 'Pegawai tidak ditemukan dalam sistem.',
                ]);
            }

            // Sales langsung login tanpa password
            Auth::guard('sales')->login($sales, $request->boolean('remember'));
            $request->session()->regenerate();
            session(['active_kd_peg' => trim((string) $sales->KD_PEG)]);

            return $this->redirectSales();
        }

        // ──────────────────────────────────────────────────────────────
        // B) LOGIN SEBAGAI ADMIN / KANTOR (Ketik Username + Kata Kunci)
        // ──────────────────────────────────────────────────────────────
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Nama user wajib diisi',
            'password.required' => 'Kata kunci wajib diisi',
        ]);

        $username = strtoupper(trim($request->username));
        $password = trim($request->password);

        // Cek MST_PENGGUNA
        $user = User::whereRaw("UPPER(CAST(TRIM(NM_USER) AS VARCHAR(100))) = ?", [$username])->first();

        if ($user && trim((string) $user->KATAKUNCI) === $password) {
            Auth::guard('web')->login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            if ($user->role === 'sales') {
                if (!empty(trim((string) $user->KD_PEG))) {
                    session(['active_kd_peg' => trim((string) $user->KD_PEG)]);
                    return $this->redirectSales();
                }
                return redirect()->route('auth.select-pegawai');
            }

            return $this->redirectAdmin();
        }

        throw ValidationException::withMessages([
            'username' => 'Nama user atau kata kunci salah.',
        ]);
    }

    public function showSelectPegawai()
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $user = Auth::guard('web')->user();

        if ($user->role !== 'sales') {
            return $this->redirectAdmin();
        }

        if (!empty(trim((string) $user->KD_PEG))) {
            return $this->redirectSales();
        }

        if (session()->has('active_kd_peg')) {
            return $this->redirectSales();
        }

        $pegawaiList = Pegawai::salesAktif()->orderBy('NM_PEG')->get();

        return view('auth.select_pegawai', compact('user', 'pegawaiList'));
    }

    public function selectPegawai(Request $request)
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('login');
        }

        $request->validate([
            'kd_peg' => 'required|string|exists:PEGAWAI,KD_PEG',
        ], [
            'kd_peg.required' => 'Silakan pilih identitas pegawai Anda.',
            'kd_peg.exists'   => 'Pegawai tidak ditemukan.',
        ]);

        $kdPeg = trim($request->kd_peg);
        session(['active_kd_peg' => $kdPeg]);

        return $this->redirectSales();
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();
        Auth::guard('sales')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    protected function redirectAdmin()
    {
        return redirect()->route('admin.dashboard');
    }

    protected function redirectSales()
    {
        return redirect()->route('sales.dashboard');
    }

    protected function redirectByRole(User $user)
    {
        if ($user->role === 'sales') {
            $kdPeg = $user->KD_PEG ?? session('active_kd_peg');
            if (empty(trim((string) $kdPeg))) {
                return redirect()->route('auth.select-pegawai');
            }
            return $this->redirectSales();
        }
        return $this->redirectAdmin();
    }
}
