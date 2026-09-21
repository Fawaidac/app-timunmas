<?php

namespace App\Models;

/**
 * Invoice web = VIEW VW_PIUTANG (legacy, READ-ONLY).
 * Sumber resmi piutang sesuai keputusan pemilik sistem (poin #1).
 * View sudah menyediakan: SISA_PIUTANG, TGL_JATUH_TEMPO, TOT_JML_BAYAR.
 */
class Invoice extends FirebirdModel
{
    protected $table = 'VW_PIUTANG';
    protected $primaryKey = 'NO_ENT';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    /* View -> tidak boleh insert/update via model ini */
    protected $guarded = ['*'];

    public function getInvoiceNumberAttribute()
    {
        return $this->NO_ENT;
    }

    public function getInvoiceDateAttribute()
    {
        return $this->TANGGAL;
    }

    public function getCustomerIdAttribute()
    {
        return $this->KD_CUST;
    }

    public function getTotalAmountAttribute()
    {
        return (float) ($this->NETTO ?? 0);
    }

    public function getRemainingBalanceAttribute()
    {
        return (float) ($this->SISA_PIUTANG ?? 0);
    }

    public function getDueDateAttribute()
    {
        return $this->TGL_JATUH_TEMPO;
    }

    public function getOrderIdAttribute()
    {
        return $this->NO_ENT;
    }

    public function getCreatedAtAttribute()
    {
        return $this->TANGGAL;
    }

    /** Status turunan dari sisa piutang + jatuh tempo (view tidak punya kolom status). */
    public function getStatusAttribute()
    {
        $sisa = (float) ($this->SISA_PIUTANG ?? 0);

        if ($sisa <= 0.005) {
            return 'paid';
        }

        $due = $this->TGL_JATUH_TEMPO;

        return ($due && \Carbon\Carbon::parse($due)->lt(\Carbon\Carbon::today())) ? 'overdue' : 'unpaid';
    }

    /* Relasi */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KD_CUST', 'KD_CUST');
    }

    public function getOrderAttribute()
    {
        // VW_PIUTANG tidak membawa NO_ENT_ORD; order asal dianggap tidak tersedia.
        return null;
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