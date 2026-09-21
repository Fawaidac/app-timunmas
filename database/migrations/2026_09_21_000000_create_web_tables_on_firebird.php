<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sistem ini menggunakan database Firebird legacy (DB_ASRI1).
 *
 * Tabel LEGACY (sudah ada, tidak boleh diubah):
 *  - MST_PENGGUNA  : login user admin (NM_USER + KATAKUNCI)
 *  - PEGAWAI       : data sales (+ kolom KATAKUNCI untuk login sales)
 *  - CUSTOMER      : data customer
 *  - BARANG        : master barang
 *  - GUDANG        : master gudang
 *  - MUTASI_BARANG : stok per gudang
 *  - MST_ORD_JUAL  : header sales order
 *  - DET_ORD_JUAL  : detail item order
 *  - VW_PIUTANG    : VIEW piutang/tagihan
 *  - MST_OTORITAS  : role/otoritas user
 *
 * Tabel BARU untuk web (sudah dibuat di Firebird):
 *  - KUNJUNGAN : jadwal kunjungan sales
 *  - PAYMENT   : titip pembayaran sales
 *  - PEGAWAI.KATAKUNCI : kolom VARCHAR(50) ditambahkan untuk autentikasi sales langsung
 *  - CUSTOMER.LATITUDE / CUSTOMER.LONGITUDE : kolom DOUBLE PRECISION untuk koordinat GPS outlet
 */
return new class extends Migration
{
    public function up(): void
    {
        // Tabel sudah ada di Firebird. Migration ini hanya dokumentasi.
    }

    public function down(): void
    {
        // Jangan DROP tabel legacy.
    }
};
