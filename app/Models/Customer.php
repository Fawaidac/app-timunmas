<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Customer web = tabel CUSTOMER (legacy Firebird).
 * PK: KD_CUST (string, pola kode cabang+urut, e.g. CUST001, WEB000001).
 */
class Customer extends FirebirdModel
{
    protected $table = 'CUSTOMER';
    protected $primaryKey = 'KD_CUST';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'KD_CUST',
        'NM_CUST',
        'ALM_CUST',
        'TELP1',
        'TELP2',
        'HP',
        'FAX',
        'E_MAIL',
        'C_PERSON',
        'WEB_SITE',
        'KD_KAT',
        'KATEGORI',
        'KD_WIL',
        'WILAYAH',
        'KD_PEG',
        'NM_PEG',
        'KRD_LIMIT',
        'TOP_LIMIT',
        'SO_AWAL_PIUTANG',
        'JML_PIUTANG',
        'JML_BAYAR',
        'NPWP',
        'NM_PKP',
        'ALM_PKP',
        'BANK1',
        'NO_REK1',
        'BANK2',
        'NO_REK2',
        'LATITUDE',
        'LONGITUDE',
        'NM_PEKERJAAN',
        'STS_MEMBER',
        'LEVELS',
    ];

    /* ------------------------------------------------------------------
     |  Accessors (kompatibilitas view)
     | ------------------------------------------------------------------ */

    public function getIdAttribute(): string
    {
        return (string) ($this->attributes['KD_CUST'] ?? ($this->attributes['kd_cust'] ?? ''));
    }

    public function getCodeAttribute(): string
    {
        return (string) ($this->attributes['KD_CUST'] ?? ($this->attributes['kd_cust'] ?? ''));
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['NM_CUST'] ?? ($this->attributes['nm_cust'] ?? ''));
    }

    public function getAddressAttribute(): ?string
    {
        return $this->attributes['ALM_CUST'] ?? ($this->attributes['alm_cust'] ?? null);
    }

    public function getPhoneAttribute(): ?string
    {
        return $this->attributes['HP']
            ?? ($this->attributes['hp']
            ?? ($this->attributes['TELP1']
            ?? ($this->attributes['telp1'] ?? null)));
    }

    public function getEmailAttribute(): ?string
    {
        return $this->attributes['E_MAIL'] ?? ($this->attributes['e_mail'] ?? null);
    }

    public function getContactPersonAttribute(): ?string
    {
        return $this->attributes['C_PERSON'] ?? ($this->attributes['c_person'] ?? null);
    }

    public function getLatitudeAttribute(): ?float
    {
        $val = $this->attributes['LATITUDE'] ?? ($this->attributes['latitude'] ?? null);
        return $val !== null ? (float) $val : null;
    }

    public function getLongitudeAttribute(): ?float
    {
        $val = $this->attributes['LONGITUDE'] ?? ($this->attributes['longitude'] ?? null);
        return $val !== null ? (float) $val : null;
    }

    public function getCurrentDebtAttribute(): float
    {
        return (float) ($this->attributes['JML_PIUTANG'] ?? ($this->attributes['jml_piutang'] ?? 0));
    }

    public function getCreditLimitAttribute(): float
    {
        return (float) ($this->attributes['KRD_LIMIT'] ?? ($this->attributes['krd_limit'] ?? 0));
    }

    public function getTopDaysAttribute(): int
    {
        return (int) ($this->attributes['TOP_LIMIT'] ?? ($this->attributes['top_limit'] ?? 0));
    }

    /* ------------------------------------------------------------------
     |  Relasi
     | ------------------------------------------------------------------ */

    public function sales()
    {
        return $this->belongsTo(Pegawai::class, 'KD_PEG', 'KD_PEG');
    }

    public function visits()
    {
        return $this->hasMany(SalesVisit::class, 'KD_CUST', 'KD_CUST');
    }

    public function orders()
    {
        return $this->hasMany(SalesOrder::class, 'KD_CUST', 'KD_CUST');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'KD_CUST', 'KD_CUST');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'KD_CUST', 'KD_CUST');
    }

    /* ------------------------------------------------------------------
     |  Helper KD_CUST
     | ------------------------------------------------------------------ */

    public static function nextKdCust(string $prefix = 'WEB'): string
    {
        $prefix = strtoupper(trim($prefix));
        $prefixLen = strlen($prefix);

        $codes = DB::connection('firebird')
            ->table('CUSTOMER')
            ->whereRaw("UPPER(CAST(KD_CUST AS VARCHAR(100))) LIKE ?", ["{$prefix}%"])
            ->pluck('KD_CUST');

        $maxNum = 0;
        foreach ($codes as $code) {
            $val = trim((string) $code);
            $numPart = substr($val, $prefixLen);
            if (is_numeric($numPart)) {
                $num = (int) $numPart;
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
            }
        }

        $padLen = max(1, 9 - $prefixLen);
        return $prefix . str_pad((string) ($maxNum + 1), $padLen, '0', STR_PAD_LEFT);
    }
}