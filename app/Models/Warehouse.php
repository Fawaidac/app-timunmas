<?php

namespace App\Models;

/**
 * Warehouse web = tabel GUDANG (legacy).
 * PK: NM_GUDANG (string, bukan auto-increment) — contoh '01-GUDANG TM'.
 */
class Warehouse extends FirebirdModel
{
    protected $table = 'GUDANG';
    protected $primaryKey = 'NM_GUDANG';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'NM_GUDANG', 'KET', 'KD_PEG', 'ST',
        'NO_AKUN_KAS', 'NO_AKUN_SEDIA', 'NO_AKUN_JUAL', 'NO_AKUN_HPP', 'NO_AKUN_BELI',
    ];

    public function getCodeAttribute()
    {
        return $this->NM_GUDANG;
    }

    public function getNameAttribute()
    {
        return $this->NM_GUDANG;
    }

    public function getAddressAttribute()
    {
        return $this->KET;
    }

    public function getIsActiveAttribute()
    {
        return true;
    }

    public function getCreatedAtAttribute()
    {
        return null;
    }

    public function getUpdatedAtAttribute()
    {
        return null;
    }

    public function stocks()
    {
        return $this->hasMany(WarehouseStock::class, 'GUDANG', 'NM_GUDANG');
    }

    /** Pemegang gudang (dari PEGAWAI). */
    public function pegawai()
    {
        return $this->belongsTo(Pegawai::class, 'KD_PEG', 'KD_PEG');
    }

    /** Nama gudang berikutnya untuk create web fallback jika nama tidak diisi. */
    public static function nextName(): string
    {
        $existing = self::query()->pluck('NM_GUDANG');
        $max = 0;
        foreach ($existing as $name) {
            if (preg_match('/^(\d+)/', trim($name), $matches)) {
                $num = (int) $matches[1];
                if ($num > $max) {
                    $max = $num;
                }
            }
        }

        return str_pad((string) ($max + 1), 2, '0', STR_PAD_LEFT) . '-GUDANG BARU';
    }
}