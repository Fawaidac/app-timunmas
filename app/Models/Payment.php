<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * Payment web = tabel PAYMENT (BARU, dibuat khusus web).
 * Workflow: sales titip pembayaran (pending) -> admin approve/reject.
 * Saat approve, piutang legacy di-update (MST_JUAL.JML_BAYAR).
 * PK NOMOR via generator GEN_PAYMENT_NOMOR (dibangkitkan di PHP).
 */
class Payment extends FirebirdModel
{
    protected $table = 'PAYMENT';
    protected $primaryKey = 'NOMOR';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'NOMOR', 'NO_BUKTI', 'NO_ENT', 'KD_CUST', 'KD_PEG', 'TANGGAL',
        'METODE', 'JUMLAH', 'NO_REF', 'FOTO', 'STATUS', 'ALASAN',
        'NO_USER_APPROVE', 'TGL_APPROVE', 'CATATAN', 'DIBUAT',
    ];

    protected static function booted()
    {
        static::creating(function (self $payment) {
            if (empty($payment->NOMOR)) {
                $payment->NOMOR = (int) (DB::select('SELECT GEN_ID(GEN_PAYMENT_NOMOR, 1) AS ID FROM RDB$DATABASE')[0]->ID ?? 0);
            }
            if (empty($payment->DIBUAT)) {
                $payment->DIBUAT = now()->format('Y-m-d H:i:s');
            }
        });
    }

    /* Alias untuk view */
    public function getPaymentNumberAttribute()
    {
        return $this->NO_BUKTI;
    }

    public function getInvoiceIdAttribute()
    {
        return $this->NO_ENT;
    }

    public function getSalesIdAttribute()
    {
        return $this->KD_PEG;
    }

    public function getCustomerIdAttribute()
    {
        return $this->KD_CUST;
    }

    public function getPaymentMethodAttribute()
    {
        return $this->METODE;
    }

    public function getAmountPaidAttribute()
    {
        return (float) ($this->JUMLAH ?? 0);
    }

    public function getReferenceNumberAttribute()
    {
        return $this->NO_REF;
    }

    public function getProofImageUrlAttribute()
    {
        return $this->FOTO;
    }

    public function getRejectionReasonAttribute()
    {
        return $this->ALASAN;
    }

    public function getApprovedByAttribute()
    {
        return $this->NO_USER_APPROVE;
    }

    public function getApprovedAtAttribute()
    {
        return $this->TGL_APPROVE;
    }

    public function getCreatedAtAttribute()
    {
        return $this->DIBUAT;
    }

    public function getIdAttribute()
    {
        return $this->NOMOR;
    }

    /* Relasi */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'NO_ENT', 'NO_ENT');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KD_CUST', 'KD_CUST');
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

    public function approver()
    {
        return $this->belongsTo(User::class, 'NO_USER_APPROVE', 'NO_USER');
    }
}