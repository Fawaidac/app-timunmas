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
        // Total piutang/tanggungan = Faktur Piutang (VW_PIUTANG) + Sisa Sales Order OS KREDIT
        // (order KREDIT yang ST_JADI='OS', sudah dikurangi pembayaran approved —
        //  sama persis dengan logika kolom Sisa di halaman Tagihan).
        $invoiceDebt = 0.0;
        if ($this->relationLoaded('invoices')) {
            $invoiceDebt = (float) $this->invoices->where('SISA_PIUTANG', '>', 0.005)->sum('SISA_PIUTANG');
        } else {
            $invoiceDebt = (float) (DB::connection('firebird')
                ->table('VW_PIUTANG')
                ->where('KD_CUST', $this->KD_CUST)
                ->where('SISA_PIUTANG', '>', 0.005)
                ->sum('SISA_PIUTANG') ?? 0);
        }

        return $invoiceDebt + $this->openOrderDebt();
    }

    /**
     * Sisa order KREDIT yang belum jadi faktur (ST_JADI='OS'),
     * sudah dikurangi pembayaran ber-STATUS 'approved' — konsisten dgn Tagihan.
     */
    private function openOrderDebt(): float
    {
        if ($this->relationLoaded('orders')) {
            $orders = $this->orders
                ->filter(fn ($o) => $o->ST_JADI === 'OS' && $o->JNS_BYR === 'KREDIT')
                ->values();
        } else {
            $orders = DB::connection('firebird')->table('MST_ORD_JUAL')
                ->where('KD_CUST', $this->KD_CUST)
                ->where('ST_JADI', 'OS')
                ->where('JNS_BYR', 'KREDIT')
                ->get(['NO_ENT', 'TOTAL']);
        }

        if ($orders->isEmpty()) {
            return 0.0;
        }

        $paid = DB::connection('firebird')->table('PAYMENT')
            ->whereIn('NO_ENT', $orders->pluck('NO_ENT')->all())
            ->where('STATUS', 'approved')
            ->groupBy('NO_ENT')
            ->select('NO_ENT', DB::raw('SUM(JUMLAH) AS PAID'))
            ->get()
            ->keyBy('NO_ENT');

        $debt = 0.0;
        foreach ($orders as $order) {
            $debt += max(0, (float) $order->TOTAL - (float) ($paid[$order->NO_ENT]->PAID ?? 0));
        }

        return $debt;
    }

    public function getRemainingLimitAttribute(): float
    {
        $limit = $this->credit_limit;
        if ($limit <= 0) {
            return 0;
        }
        return max(0, $limit - $this->current_debt);
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

    public static function nextKdCust(string $prefix = 'CUST'): string
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