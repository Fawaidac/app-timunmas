<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware cek role.
 *
 * Mendukung dua skenario login sales:
 *   A) Guard 'sales'  → login langsung dari PEGAWAI (KATAKUNCI di PEGAWAI)
 *   B) Guard 'web'    → login dari MST_PENGGUNA, role = 'sales', KD_PEG dari
 *                        kolom MST_PENGGUNA.KD_PEG atau session('active_kd_peg')
 */
class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = null;

        if ($role === 'sales') {
            // Skenario A: guard 'sales' (PEGAWAI langsung)
            if (Auth::guard('sales')->check()) {
                Auth::shouldUse('sales');
                $user = Auth::guard('sales')->user();
            }
            // Skenario B: guard 'web' dengan role sales (via MST_PENGGUNA)
            elseif (Auth::guard('web')->check() && Auth::guard('web')->user()->role === 'sales') {
                Auth::shouldUse('web');
                $user = Auth::guard('web')->user();

                // Pastikan identitas pegawai sudah dipilih
                $kdPeg = $user->KD_PEG ?? session('active_kd_peg');
                if (empty(trim((string) $kdPeg))) {
                    // Belum pilih pegawai → paksa ke halaman pilih pegawai
                    return redirect()->route('auth.select-pegawai');
                }

                // Inject active_kd_peg ke session agar controller bisa baca
                if (!session()->has('active_kd_peg') && !empty(trim((string) $user->KD_PEG))) {
                    session(['active_kd_peg' => trim((string) $user->KD_PEG)]);
                }
            }
        } elseif ($role === 'admin') {
            if (Auth::guard('web')->check()) {
                Auth::shouldUse('web');
                $user = Auth::guard('web')->user();
            }
        }

        // Fallback: belum login
        if (!$user) {
            return redirect()->route('login');
        }

        // Superadmin bisa akses semua
        if ($user->role === 'superadmin') {
            return $next($request);
        }

        // Cek role sesuai parameter
        if ($user->role !== $role) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
