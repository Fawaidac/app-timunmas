<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

/**
 * Sales = tabel PEGAWAI (legacy), sales aktif: STS_SALES = 'YA'.
 * PK: KD_PEG (string).
 */
class Pegawai extends FirebirdAuthenticatable
{
    protected $table = 'PEGAWAI';
    protected $primaryKey = 'KD_PEG';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'KD_PEG',
        'NO_URUT',
        'NM_PEG',
        'ALM_PEG',
        'TELP1',
        'TELP2',
        'HP',
        'E_MAIL',
        'TGL_MASUK',
        'TGL_LHR',
        'KD_DEPT',
        'ST_AKTIF',
        'KD_JADWAL',
        'BANK',
        'NO_REK',
        'KOMISI',
        'POINT_JL',
        'POINT_GUNA',
        'STS_SALES',
        'KD_WIL',
        'KD_LAMA',
    ];

    /* ------------------------------------------------------------------
     |  Auth methods (untuk login sebagai sales)
     | ------------------------------------------------------------------ */

    public function getAuthPassword(): string
    {
        return '';
    }

    /** Username untuk login = KD_PEG */
    public function getAuthIdentifierName(): string
    {
        return 'KD_PEG';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->KD_PEG;
    }

    public function getRememberTokenName(): string
    {
        return '';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
    }

    /* ------------------------------------------------------------------
     |  Alias attribute (kompatibilitas view)
     | ------------------------------------------------------------------ */

    public function getNameAttribute(): string
    {
        return (string) ($this->NM_PEG ?? '');
    }

    public function getCodeAttribute(): string
    {
        return (string) ($this->KD_PEG ?? '');
    }

    public function getPhoneAttribute(): string
    {
        return (string) ($this->HP ?? $this->TELP1 ?? '');
    }

    /** Role selalu 'sales' untuk pegawai */
    public function getRoleAttribute(): string
    {
        return 'sales';
    }

    /** Alias Area untuk sidebar view */
    public function getAreaAttribute(): string
    {
        return (string) ($this->KD_WIL ?? 'Sales Team');
    }

    /** Alias NM_USER untuk kompatibilitas order controller */
    public function getNmUserAttribute(): string
    {
        return (string) ($this->NM_PEG ?? $this->KD_PEG ?? '');
    }

    /** Alias NO_USER → null (beda tabel) */
    public function getNoUserAttribute(): ?int
    {
        return null;
    }

    /* ------------------------------------------------------------------
     |  Scopes & Helpers
     | ------------------------------------------------------------------ */

    /** Scope sales aktif (STS_SALES = 'YA') */
    public function scopeSalesAktif($query)
    {
        return $query->where('STS_SALES', 'YA');
    }

    /** Helper generate next KD_PEG (misal T2467) */
    public static function nextKdPeg(): string
    {
        $pegs = DB::connection('firebird')
            ->table('PEGAWAI')
            ->whereRaw("KD_PEG STARTING WITH 'T'")
            ->pluck('KD_PEG');

        $maxNum = 0;
        foreach ($pegs as $kd) {
            $val = trim((string) $kd);
            if (preg_match('/^T(\d+)$/i', $val, $m)) {
                $num = (int) $m[1];
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        return 'T' . ($maxNum + 1);
    }
}