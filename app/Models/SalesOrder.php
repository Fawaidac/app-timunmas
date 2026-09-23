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

    /** Label tampilan: "💵 Lunas — Cash/Transfer" atau "⏳ Kredit — Tempo 7 Hari". */
    public function getPaymentMethodLabelAttribute(): string
    {
        if ($this->JNS_BYR !== 'TUNAI') {
            return '⏳ Kredit — Tempo ' . ((int) ($this->TOP ?: 7)) . ' Hari';
        }

        $metode = $this->payments()->where('STATUS', 'approved')->value('METODE');

        return '💵 Lunas — ' . (strtoupper((string) $metode) === 'TRANSFER' ? 'Transfer' : 'Cash');
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

    /* Status Helpers */
    public function isDraft(): bool
    {
        return in_array($this->ST_JADI, ['QUO', 'DRAFT']);
    }

    public function isPending(): bool
    {
        return $this->ST_JADI === 'OS';
    }

    public function isApproved(): bool
    {
        return $this->ST_JADI === 'INV';
    }

    public function isRejected(): bool
    {
        return in_array($this->ST_JADI, ['REJECTED', 'BATAL']);
    }

    /* Badge status (nilai legacy: QUO = penawaran/draft, OS = order (langsung final, TANPA approval),
       INV = sales/faktur, REJECTED/BATAL). Approval HANYA untuk penitipan pembayaran, bukan order. */
    private function osBadge(): array
    {
        if ($this->JNS_BYR === 'TUNAI') {
            return ['class' => 'badge-success', 'label' => 'Order — Lunas'];
        }

        $paid = (float) ($this->relationLoaded('payments')
            ? $this->payments->where('STATUS', 'approved')->sum('JUMLAH')
            : $this->payments()->where('STATUS', 'approved')->sum('JUMLAH'));

        $sisa = max(0, (float) ($this->TOTAL ?? 0) - $paid);

        return $sisa <= 0.005
            ? ['class' => 'badge-success', 'label' => 'Order — Lunas']
            : ['class' => 'badge-warning', 'label' => 'Order — Kredit'];
    }

    public function getBadgeClassAttribute()
    {
        if ($this->ST_JADI === 'OS') {
            return $this->osBadge()['class'];
        }

        return [
            'QUO'      => 'badge-secondary',
            'DRAFT'    => 'badge-secondary',
            'INV'      => 'badge-success',
            'REJECTED' => 'badge-danger',
            'BATAL'    => 'badge-danger',
        ][$this->ST_JADI] ?? 'badge-info';
    }

    public function getBadgeLabelAttribute()
    {
        if ($this->ST_JADI === 'OS') {
            return $this->osBadge()['label'];
        }

        return [
            'QUO'      => 'Penawaran (Draft)',
            'DRAFT'    => 'Penawaran (Draft)',
            'INV'      => 'Penjualan (Faktur)',
            'REJECTED' => 'Ditolak Admin',
            'BATAL'    => 'Dibatalkan',
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