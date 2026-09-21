<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * User web = MST_PENGGUNA (legacy ClickSoft ASRI).
 * Login: NM_USER + KATAKUNCI — DIBANDINGKAN LANGSUNG (TANPA bcrypt),
 * sesuai keputusan pemilik sistem.
 */
class User extends FirebirdAuthenticatable
{
    use Notifiable;

    protected $table = 'MST_PENGGUNA';
    protected $primaryKey = 'NO_USER';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'NO_USER', 'NM_USER', 'KATAKUNCI', 'NO_OTOR', 'KD_PEG', 'REMEMBER_TOKEN',
    ];

    protected $hidden = [
        'KATAKUNCI', 'REMEMBER_TOKEN',
    ];

    /* ------------------------------------------------------------------
     |  Relasi ke PEGAWAI (sales punya KD_PEG)
     | ------------------------------------------------------------------ */

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class, 'KD_PEG', 'KD_PEG');
    }

    /* ------------------------------------------------------------------
     |  Alias atribut untuk view (nama friendly -> kolom legacy)
     | ------------------------------------------------------------------ */

    public function getNameAttribute()
    {
        return $this->NM_USER;
    }

    /** View memakai "email" sebagai identitas akun -> tampilkan NM_USER. */
    public function getEmailAttribute()
    {
        return $this->NM_USER;
    }

    public function getPhoneAttribute()
    {
        return '';
    }

    public function getAreaAttribute()
    {
        return '';
    }

    /** Role admin/sales dari nama otoritas legacy (MST_OTORITAS). */
    public function getRoleAttribute()
    {
        $nmOtor = (string) DB::table('MST_OTORITAS')
            ->where('NO_OTOR', $this->NO_OTOR)
            ->value('NM_OTOR');

        if (str_contains(strtoupper($nmOtor), 'SALES')) {
            return 'sales';
        }

        return 'admin';
    }

    public function getRememberTokenName()
    {
        return 'REMEMBER_TOKEN';
    }

    /** Password diverifikasi langsung (plain), TANPA bcrypt/hash. */
    public function getAuthPassword()
    {
        return $this->KATAKUNCI;
    }

    /* ------------------------------------------------------------------
     |  Helper buat/manage user (NO_USER bukan generator terkelola
     |  trigger -> pakai MAX(NO_USER)+1 di dalam transaksi)
     | ------------------------------------------------------------------ */

    public static function nextNoUser(): int
    {
        return (int) (self::query()->max('NO_USER') ?? 0) + 1;
    }

    /** Nama otoritas legacy untuk tampilan. */
    public function getNmOtorAttribute()
    {
        return DB::table('MST_OTORITAS')
            ->where('NO_OTOR', $this->NO_OTOR)
            ->value('NM_OTOR');
    }
}
