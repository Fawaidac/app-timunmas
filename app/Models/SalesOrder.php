<?php

namespace App\Models;

/**
 * SalesOrder web = MST_ORD_JUAL (legacy).
 * PK NO_ENT string 'OJYYMM/seri/urut' — DIBANGKITKAN APLIKASI WEB
 * (mengikuti pola desktop: MAX(urut)+1 per bulan, TIDAK lewat trigger).
 */
class SalesOrder extends FirebirdModel
{
    protected $table = 'MST_ORD_JUAL';
    protected $primaryKey = 'NO_ENT';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'NO_ENT', 'TANGGAL', 'KD_CUST', 'TOTAL', 'KD_PEG', 'KD_USER',
        'ST_JADI', 'JNS_BYR', 'TOP', 'TOP_PROS', 'TOP_PROS_HARI',
        'TGL_HARGA', 'RNC_TGL_KIRIM', 'TGL_EXP', 'GUDANG', 'KD_STOK',
        'NM_KIRIM', 'ALM_KIRIM', 'TELP_KIRIM', 'NM_BRG_KIRIM',
        'NO_ENT_ORD', 'KD_EKSPEDISI', 'TGL_PO', 'NO_ENT_PO',
    ];

    /* Alias untuk view */
    public function getOrderNumberAttribute()
    {
        return $this->NO_ENT;
    }

    public function getOrderDateAttribute()
    {
        return $this->TANGGAL;
    }

    public function getCustomerIdAttribute()
    {
        return $this->KD_CUST;
    }

    public function getSalesIdAttribute()
    {
        return $this->KD_PEG;
    }

    public function getTotalAmountAttribute()
    {
        return (float) ($this->TOTAL ?? 0);
    }

    public function getStatusAttribute()
    {
        return $this->ST_JADI;
    }

    public function getPaymentTypeAttribute()
    {
        return $this->JNS_BYR === 'TUNAI' ? 'cash' : 'credit';
    }

    public function getPaymentTermDaysAttribute()
    {
        return (int) ($this->TOP ?? 0);
    }

    public function getVisitIdAttribute()
    {
        return null;
    }

    public function getCreatedAtAttribute()
    {
        return $this->TANGGAL;
    }

    public function getIdAttribute()
    {
        return $this->NO_ENT;
    }

    /* Badge status (nilai legacy: OS = order, INV = jadi faktur, BATAL) */
    public function getBadgeClassAttribute()
    {
        return [
            'OS'    => 'badge-warning',
            'INV'   => 'badge-success',
            'BATAL' => 'badge-danger',
        ][$this->ST_JADI] ?? 'badge-secondary';
    }

    public function getBadgeLabelAttribute()
    {
        return [
            'OS'    => 'Order (belum faktur)',
            'INV'   => 'Sudah jadi faktur',
            'BATAL' => 'Dibatalkan',
        ][$this->ST_JADI] ?? ucfirst((string) $this->ST_JADI);
    }

    /* Relasi */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KD_CUST', 'KD_CUST');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'NO_ENT', 'NO_ENT');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'NO_ENT', 'NO_ENT_ORD');
    }

    public function visit()
    {
        return $this->belongsTo(SalesVisit::class, 'NO_ENT_ORD', 'NO_ENT_ORD');
    }

    public function getSalesAttribute()
    {
        if (! $this->KD_PEG) {
            return null;
        }

        $p = Pegawai::find($this->KD_PEG);

        return (object) [
            'name' => $p->NM_PEG ?? $this->KD_PEG,
            'code' => $this->KD_PEG,
        ];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'NO_ENT', 'NO_ENT');
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class, 'NO_ENT', 'NO_ENT')
            ->orderBy('NOMOR', 'desc');
    }
}