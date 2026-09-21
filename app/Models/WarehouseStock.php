<?php

namespace App\Models;

/**
 * WarehouseStock web = MUTASI_BARANG (legacy) — stok per gudang per barang.
 * PK: KD_BRG_GUDANG (string, bukan auto-increment).
 */
class WarehouseStock extends FirebirdModel
{
    protected $table = 'MUTASI_BARANG';
    protected $primaryKey = 'KD_BRG_GUDANG';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'KD_BRG_GUDANG', 'GUDANG', 'KD_BRG', 'QTY_AWAL', 'RP_AWAL',
        'QTY_MASUK', 'RP_MASUK', 'QTY_KELUAR', 'RP_KELUAR', 'QTY_AKHIR',
        'RP_AKHIR', 'SAK_AWAL', 'SAK_MASUK', 'SAK_KELUAR', 'SAK_AKHIR',
        'QTY_AKHIR_BL_LALU', 'RP_AKHIR_BL_LALU', 'QTY_ORDER',
    ];

    public function getWarehouseIdAttribute()
    {
        return $this->GUDANG;
    }

    public function getProductIdAttribute()
    {
        return $this->KD_BRG;
    }

    public function getStockQuantityAttribute()
    {
        return (float) ($this->QTY_AKHIR ?? 0);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class, 'GUDANG', 'NM_GUDANG');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'KD_BRG', 'KD_BRG');
    }
}