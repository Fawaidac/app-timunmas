<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Product web = tabel BARANG (legacy Firebird).
 * PK: KD_BRG (string, e.g. DBL01, BRG0001).
 * Stok riil = MUTASI_BARANG per gudang.
 */
class Product extends FirebirdModel
{
    protected $table = 'BARANG';
    protected $primaryKey = 'KD_BRG';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'KD_BRG',
        'NM_BRG',
        'JNS_BRG',
        'KD_JNS_BRG',
        'JNS_SEDIA',
        'HARGA_JL',
        'HARGA_BL',
        'HARGA_JL2',
        'HARGA_JL3',
        'HARGA_JL4',
        'HARGA_JL5',
        'QTY_MIN1',
        'QTY_MIN2',
        'QTY_MIN3',
        'SATUAN1',
        'SATUAN2',
        'SATUAN3',
        'SATUAN4',
        'KAPASITAS2',
        'KAPASITAS3',
        'KAPASITAS4',
        'STOK_MIN',
        'SAT_MIN',
        'KD_SUPPL',
        'NM_SUPPL',
        'RAK',
        'BERAT',
        'PJG_CM',
        'LBR_CM',
        'TEBAL',
        'KUBIKASI',
        'STS_AKTIF',
        'HPP',
        'DISKON',
        'POINT',
        'KOMISI',
    ];

    /* ------------------------------------------------------------------
     |  Accessors (kompatibilitas view & helper)
     | ------------------------------------------------------------------ */

    public function getSkuAttribute(): string
    {
        return (string) ($this->attributes['KD_BRG'] ?? ($this->attributes['kd_brg'] ?? ''));
    }

    public function getCodeAttribute(): string
    {
        return $this->getSkuAttribute();
    }

    public function getNameAttribute(): string
    {
        return (string) ($this->attributes['NM_BRG'] ?? ($this->attributes['nm_brg'] ?? ''));
    }

    public function getCategoryAttribute(): ?string
    {
        return $this->attributes['JNS_BRG'] ?? ($this->attributes['jns_brg'] ?? null);
    }

    public function getSupplierNameAttribute(): ?string
    {
        return $this->attributes['NM_SUPPL'] ?? ($this->attributes['nm_suppl'] ?? null);
    }

    public function getPriceAttribute(): float
    {
        return (float) ($this->attributes['HARGA_JL'] ?? ($this->attributes['harga_jl'] ?? 0));
    }

    public function getBuyPriceAttribute(): float
    {
        return (float) ($this->attributes['HARGA_BL'] ?? ($this->attributes['harga_bl'] ?? 0));
    }

    /** Satuan jual utama */
    public function getUnitAttribute(): string
    {
        return (string) ($this->attributes['SATUAN1'] ?: ($this->attributes['SATUAN2'] ?? 'PCS'));
    }

    /** Total stok lintas gudang dari MUTASI_BARANG (QTY_AKHIR) */
    public function getStockAttribute(): float
    {
        return (float) (DB::connection('firebird')
            ->table('MUTASI_BARANG')
            ->where('KD_BRG', $this->KD_BRG)
            ->sum('QTY_AKHIR'));
    }

    /** Koleksi stok per gudang dengan informasi detail gudang */
    public function getWarehousesAttribute()
    {
        return WarehouseStock::query()
            ->where('KD_BRG', $this->KD_BRG)
            ->get()
            ->map(function (WarehouseStock $ws) {
                $wh = $ws->warehouse;

                return (object) [
                    'id'             => $ws->GUDANG,
                    'name'           => $wh?->name ?? $ws->GUDANG,
                    'code'           => $wh?->code ?? $ws->GUDANG,
                    'address'        => $wh?->address ?? '',
                    'is_active'      => true,
                    'stock'          => (float) $ws->QTY_AKHIR,
                    'stock_quantity' => (float) $ws->QTY_AKHIR,
                    'pivot'          => (object) ['stock_quantity' => (float) $ws->QTY_AKHIR],
                ];
            });
    }

    public function getUpdatedAtAttribute()
    {
        return null;
    }

    public function getCreatedAtAttribute()
    {
        return null;
    }

    /* ------------------------------------------------------------------
     |  Relasi
     | ------------------------------------------------------------------ */

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class, 'KD_BRG', 'KD_BRG');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'KD_BRG', 'KD_BRG');
    }

    /* ------------------------------------------------------------------
     |  Helper KD_BRG
     | ------------------------------------------------------------------ */

    /** Generate saran SKU / KD_BRG berikutnya (misal BRG0001 atau WEB0001) */
    public static function nextKdBrg(string $prefix = 'BRG'): string
    {
        $prefix = strtoupper(trim($prefix));
        $prefixLen = strlen($prefix);

        $codes = DB::connection('firebird')
            ->table('BARANG')
            ->whereRaw("UPPER(CAST(KD_BRG AS VARCHAR(100))) LIKE ?", ["{$prefix}%"])
            ->pluck('KD_BRG');

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

        return $prefix . str_pad((string) ($maxNum + 1), 4, '0', STR_PAD_LEFT);
    }
}