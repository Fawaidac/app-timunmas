<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * OrderItem web = DET_ORD_JUAL (legacy).
 * PK NOMOR via generator DET_ORD_JUAL_NOMOR_GEN (dibangkitkan di PHP).
 */
class OrderItem extends FirebirdModel
{
    protected $table = 'DET_ORD_JUAL';
    protected $primaryKey = 'NOMOR';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'NOMOR', 'NO_ENT', 'NMR', 'KD_BRG', 'KD_BRG_1', 'NM_BRG', 'SATUAN',
        'JUMLAH', 'HARGA', 'DISC1', 'DISC2', 'DISC_RP', 'TOTAL', 'SUB_TOTAL',
        'JML_KIRIM', 'JML_TUR', 'SAK', 'GUDANG', 'KD_STOK', 'KET', 'HPP',
        'NM_DEPOS', 'SAT_KE', 'NO_ENT_ORD',
    ];

    protected static function booted()
    {
        static::creating(function (self $item) {
            if (empty($item->NOMOR)) {
                $max = (int) (DB::connection('firebird')->table('DET_ORD_JUAL')->max('NOMOR') ?? 0);
                $item->NOMOR = $max + 1;
            }
        });
    }

    public function getOrderIdAttribute()
    {
        return $this->NO_ENT;
    }

    public function getProductIdAttribute()
    {
        return $this->KD_BRG;
    }

    public function getQuantityAttribute()
    {
        return (float) ($this->JUMLAH ?? 0);
    }

    public function getPricePerUnitAttribute()
    {
        return (float) ($this->HARGA ?? 0);
    }

    public function getSubtotalAttribute()
    {
        $sub = (float) ($this->SUB_TOTAL ?? 0);
        if ($sub > 0) {
            return $sub;
        }

        $tot = (float) ($this->TOTAL ?? 0);
        if ($tot > 0) {
            return $tot;
        }

        $qty = (float) ($this->JUMLAH ?? 0);
        $price = (float) ($this->HARGA ?? 0);
        return $qty * $price;
    }

    public function getIdAttribute()
    {
        return $this->NOMOR;
    }

    public function order()
    {
        return $this->belongsTo(SalesOrder::class, 'NO_ENT', 'NO_ENT');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'KD_BRG', 'KD_BRG');
    }
}