<?php

namespace App\Helpers;

use App\Models\Pegawai;
use Illuminate\Support\Facades\Auth;

/**
 * Helper untuk mendapatkan identitas Sales yang sedang login.
 *
 * Mendukung dua mode login:
 *   A) Guard 'sales'  → PEGAWAI langsung, user() adalah Pegawai
 *   B) Guard 'web'    → MST_PENGGUNA dengan role=sales, KD_PEG dari
 *                        kolom KD_PEG atau session('active_kd_peg')
 */
class SalesHelper
{
    /**
     * Dapatkan KD_PEG sales yang sedang login.
     */
    public static function kdPeg(): ?string
    {
        // Skenario A: guard sales
        if (Auth::guard('sales')->check()) {
            return trim((string) Auth::guard('sales')->user()->KD_PEG);
        }

        // Skenario B: guard web dengan role sales
        if (Auth::guard('web')->check()) {
            $webUser = Auth::guard('web')->user();
            // Coba dari kolom KD_PEG di MST_PENGGUNA
            $kd = trim((string) ($webUser->KD_PEG ?? ''));
            if (!empty($kd)) return $kd;
            // Coba dari session (jika pilih pegawai)
            $kd = session('active_kd_peg', '');
            return !empty($kd) ? trim((string) $kd) : null;
        }

        return null;
    }

    /**
     * Dapatkan model Pegawai sales yang sedang login.
     */
    public static function pegawai(): ?Pegawai
    {
        // Skenario A: guard sales (user adalah Pegawai langsung)
        if (Auth::guard('sales')->check()) {
            $u = Auth::guard('sales')->user();
            return $u instanceof Pegawai ? $u : null;
        }

        // Skenario B: guard web — ambil Pegawai via KD_PEG
        $kdPeg = self::kdPeg();
        if ($kdPeg) {
            return Pegawai::where('KD_PEG', $kdPeg)->first();
        }

        return null;
    }

    /**
     * Nama sales yang sedang login.
     */
    public static function nama(): string
    {
        $peg = self::pegawai();
        if ($peg) return $peg->NM_PEG;

        // Fallback ke nama user web
        if (Auth::guard('web')->check()) {
            return Auth::guard('web')->user()->NM_USER ?? '';
        }

        return '';
    }
}
