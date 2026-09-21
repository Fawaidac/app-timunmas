<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

/**
 * SalesVisit web = tabel KUNJUNGAN (BARU, dibuat khusus web).
 * PK NOMOR via generator GEN_KUNJUNGAN_NOMOR (dibangkitkan di PHP).
 */
class SalesVisit extends FirebirdModel
{
    protected $table = 'KUNJUNGAN';
    protected $primaryKey = 'NOMOR';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'NOMOR', 'KD_PEG', 'KD_CUST', 'TANGGAL', 'TUJUAN', 'STATUS',
        'JAM_CHECKIN', 'LAT_CHECKIN', 'LON_CHECKIN', 'JARAK_M', 'CATATAN',
        'NO_ENT_ORD', 'DIBUAT',
    ];

    protected static function booted()
    {
        static::creating(function (self $visit) {
            if (empty($visit->NOMOR)) {
                $visit->NOMOR = (int) (DB::select('SELECT GEN_ID(GEN_KUNJUNGAN_NOMOR, 1) AS ID FROM RDB$DATABASE')[0]->ID ?? 0);
            }
            if (empty($visit->DIBUAT)) {
                $visit->DIBUAT = now()->format('Y-m-d H:i:s');
            }
            if (empty($visit->STATUS)) {
                $visit->STATUS = 'scheduled';
            }
        });
    }

    /* ------------------------------------------------------------------
     |  Accessors (kompatibilitas view)
     | ------------------------------------------------------------------ */

    public function getStatusAttribute(): string
    {
        return strtolower(trim((string) ($this->attributes['STATUS'] ?? ($this->attributes['status'] ?? 'scheduled'))));
    }

    public function getSalesIdAttribute()
    {
        return $this->KD_PEG;
    }

    public function getCustomerIdAttribute()
    {
        return $this->KD_CUST;
    }

    public function getVisitDateAttribute()
    {
        return $this->TANGGAL;
    }

    public function getPurposeAttribute()
    {
        return strtolower(trim((string) ($this->TUJUAN ?? '')));
    }

    public function getNotesAttribute()
    {
        return $this->CATATAN;
    }

    public function getCheckinTimeAttribute()
    {
        return $this->JAM_CHECKIN;
    }

    public function getCheckinLatitudeAttribute()
    {
        return $this->LAT_CHECKIN;
    }

    public function getCheckinLongitudeAttribute()
    {
        return $this->LON_CHECKIN;
    }

    public function getDistanceMetersAttribute()
    {
        return $this->JARAK_M;
    }

    public function getCreatedAtAttribute()
    {
        return $this->DIBUAT;
    }

    public function getIdAttribute()
    {
        return (int) ($this->NOMOR ?? 0);
    }

    /** Label & badge status. */
    public function getStatusLabelAttribute()
    {
        $st = $this->status;
        return [
            'scheduled'   => 'Dijadwalkan',
            'in_progress' => 'Sedang Berlangsung',
            'completed'   => 'Selesai',
            'cancelled'   => 'Dibatalkan',
        ][$st] ?? ucfirst((string) $st);
    }

    public function getBadgeClassAttribute()
    {
        $st = $this->status;
        return [
            'scheduled'   => 'badge-warning',
            'in_progress' => 'badge-primary',
            'completed'   => 'badge-success',
            'cancelled'   => 'badge-danger',
        ][$st] ?? 'badge-secondary';
    }

    public function getBadgeLabelAttribute()
    {
        return $this->status_label;
    }

    /* ------------------------------------------------------------------
     |  Relasi Eloquent
     | ------------------------------------------------------------------ */

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'KD_CUST', 'KD_CUST');
    }

    public function sales()
    {
        return $this->belongsTo(Pegawai::class, 'KD_PEG', 'KD_PEG');
    }

    public function order()
    {
        return $this->hasOne(SalesOrder::class, 'NO_ENT_ORD', 'NO_ENT_ORD')
            ->where('ST_JADI', '!=', 'BATAL');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'KD_PEG', 'KD_PEG');
    }
}