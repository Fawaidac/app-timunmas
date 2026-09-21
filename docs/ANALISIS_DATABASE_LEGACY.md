# Analisis Kesesuaian Database Legacy (Firebird) dengan Sistem Website Timunmas

> **STATUS KEPUTUSAN (final):** Pemetaan resmi ditetapkan pemilik sistem — lihat **Bagian 0** di bawah.

---

## 0. KEPUTUSAN PEMETAAN RESMI (dari pemilik sistem) — 19 Sep 2026

Tujuh poin keputusan + konsekuensi teknisnya (hasil verifikasi DB):

| # | Keputusan pemilik | Implementasi di web |
|---|---|---|
| 1 | **Piutang diambil dari view `VW_PIUTANG`** | Model `Piutang/Tagihan` read-only → `VW_PIUTANG` (`SISA_PIUTANG`, `TGL_JATUH_TEMPO` sudah dihitung view). Status data: `MST_JUAL_PIUTANG` 96.859 faktur. |
| 2 | **Login dari `MST_PENGGUNA`** | Autentikasi web membaca `MST_PENGGUNA` (5 user: HENIS, SONI, HUDA, ROY, PRATATO) + role via `MST_OTORITAS`. ⚠️ `KATAKUNCI` adalah enkripsi proprietary ClickSoft — **bukan** bcrypt dan **bukan** `crypt()` DES standar (sudah diuji 28 kata × 29 salt = gagal semua). Maka: verifikasi password lewat rutin kompatibel legacy, atau sinkron password pertama kali ke hash bcrypt kolom web sendiri. |
| 3 | **Sales dari `PEGAWAI`** | `STS_SALES = 'YA'` (15 orang aktif). Pakai view `VW_SALES` atau query langsung. |
| 4 | **Customer dari `CUSTOMER`; kalau ada yang kurang, buat field baru** | CRUD web menulis langsung ke `CUSTOMER` (insert/update). Kolom tambahan kebutuhan web ditambahkan **alter table `CUSTOMER`** (mis. untuk data khusus web). ⚠️ `KD_CUST` bukan auto-increment: polanya kode cabang + urut (contoh `BWS633`, `TM207`, `AR1147`) — generator `CUSTOMER` baru perlu menyusun kode, atau pakai konter per prefix. |
| 5 | **Menu kunjungan: tabel baru sendiri (mis. `KUNJUNGAN`)** | ✅ Sesuai temuan: legacy tidak punya tabel kunjungan; GPS di `SO_MOB` 100% kosong. Buat tabel baru + generator + trigger (pola FB 2.5). Kolom: `KD_PEG`, `KD_CUST`, tanggal/jam masuk-keluar, `LATITUDE`, `LONGITUDE`, foto, status, keterangan, `NO_ENT_ORD` (link ke order). |
| 6 | **Barang dari `BARANG`** | Read (`VW_BARANG`) + CRUD admin ke `BARANG`. ⚠️ Stok riil jangan diambil dari `BARANG.STOK` (semua 0) — pakai `MUTASI_BARANG`/`VW_STOK_BARANG` per gudang. |
| 7 | **Order dari `MST_ORD_JUAL` + `DET_ORD_JUAL`** | Baca: `VW_ORD_JUAL` / `VW_ORD_JUAL_DETAIL` (2.978 order / 9.228 item, nilai terverifikasi). **Tulis:** nomor `NO_ENT` (`OJYYMM/seri/urut`) dibangkitkan **aplikasi desktop, bukan trigger** — insert langsung dari web berisiko tabrakan nomor. Aman: tulis via `SO_MOB` (jalur upload mobile yang sudah terbukti → desktop), atau susun nomor sendiri mengikuti pola + `MAX(urut)+1` dalam transaksi. |

**Tabel baru yang tetap harus dibuat (diluar legacy):** `users`/login web bila perlu hash bcrypt sendiri *(opsional — keputusan #2 memilih `MST_PENGGUNA`)*, **`KUNJUNGAN`** (wajib, poin #5), dan tabel workflow pembayaran web + approval bila diperlukan (piutang itu sendiri dibaca dari `VW_PIUTANG`, poin #1).

**Bukti penomoran `NO_ENT` (bukan trigger):**
- `MST_ORD_JUAL_BI` tidak mengisi `NO_ENT`; `NO_ENT` terlanjur ada saat AFTER INSERT (dan dipakai oleh `MST_JUAL.NO_ENT_ORD`).
- Pola terbukti: `OJ2608/001/00001..00027` (Agu), `OJ2607/001/00001..02951` (Jul); faktur `J2607/1/001/002950`.
- Seri `001` = cabang; urut reset per bulan → nomor = `MAX(urut bulan itu) + 1`.

> Dokumen ini dibuat dari **hasil inspeksi langsung ke database** (bukan dari model/migration).
> Semua angka di bawah adalah hasil query nyata ke DB yang dikonfigurasi di `.env`.

---

## 1. Konfigurasi Koneksi Aktif

| Item | Nilai |
|---|---|
| Driver | `firebird` (custom, lihat `app/Database/Firebird/`) |
| Host:Port | `127.0.0.1:3050` |
| Database | `/firebird/data/DB_ASRI1_07_2026.GDB` |
| Username | `SYSDBA` |
| Charset | `UTF8` |
| Versi engine | **Firebird 2.5.9** |

Asal DB teridentifikasi dari kolom `TRANSF_PIUTANG.PATH_DB`:
`D:\CLICKSOFT\ASRI\DB_ASRI1_12_2022.GDB`

→ Ini adalah **database aplikasi ERP legacy "ClickSoft ASRI"** milik perusahaan (distributor), berjalan di Windows.
**Bukan** database yang dibuat untuk aplikasi Laravel ini.

---

## 2. Skala Database

| Objek | Jumlah |
|---|---|
| Tabel | **340** |
| View | **210** |
| Total kolom | 10.820 |
| Generator (sequence) | 75 |
| Trigger | 392 |
| Foreign key | **hanya 4** |
| Index tambahan | 36 |

Rentang data transaksi: **1 Juli 2026 – 6 Agustus 2026** (DB ini "berjalan", bukan arsip lama).

---

## 3. TEMUAN UTAMA (paling penting)

> ### ❌ 18 dari 18 tabel yang dipakai aplikasi website sekarang **TIDAK ADA** di database ini.

Tabel yang dicek dan hasilnya:

```
users                    TIDAK ADA        sales_orders         TIDAK ADA
password_reset_tokens    TIDAK ADA        order_items          TIDAK ADA
sessions                 TIDAK ADA        invoices             TIDAK ADA
cache / cache_locks      TIDAK ADA        payments             TIDAK ADA
jobs / job_batches       TIDAK ADA        customers            TIDAK ADA
failed_jobs              TIDAK ADA        products             TIDAK ADA
migrations               TIDAK ADA        warehouses           TIDAK ADA
                                          warehouse_stocks     TIDAK ADA
                                          sales_visits         TIDAK ADA
```

**Konsekuensi:** sistem website **harus menyesuaikan diri** ke struktur DB legacy.
Tidak bisa sebaliknya (menjalankan `php artisan migrate` di DB ini), karena:

1. DB ini dipakai aplikasi desktop lain → risiko konflik.
2. **392 trigger** + **75 generator** membangkitkan nomor dokumen (`NO_ENT`). Insert dari luar tanpa memahami trigger = nomor bentrok / data korup.
3. Hanya ada 4 foreign key → integritas data dijaga di level aplikasi legacy, bukan di DB.

> ### 🔴 TEMUAN PENTING KEDUA: hanya ±3% faktur yang punya rincian item
>
> | Cek | Hasil |
> |---|---|
> | `MST_JUAL` (semua faktur) | **99.809** |
> | Punya rincian di `DET_JUAL` | **2.950** (faktur baru Jul–Agu 2026, `STS_SO_AWAL = 0`) |
> | **Tanpa rincian** | **96.859** (faktur migrasi, `STS_SO_AWAL = 1`, termasuk di `MST_JUAL_PIUTANG`) |
>
> Jadi laporan **per barang** hanya valid untuk periode Juli–Agustus 2026. Laporan **per pelanggan/nilai piutang** valid untuk semua periode.
> Detail lengkap: lihat §4.G dan **§4.K (pola closing bulanan)**.
>
> ### 🔴 TEMUAN PENTING KETIGA: nilai tagihan yang benar adalah `NETTO`, bukan `TOTAL`
>
> `MST_JUAL.TOTAL == NETTO` hanya pada 92.657 / 99.809 faktur. Pada faktur berdiskon
> (contoh `J2607/1/001/002948`: `TOTAL = 6.840.000` → `NETTO = 6.703.200`),
> **`NETTO` = Σ `DET_JUAL.SUB_TOTAL`**. Gunakan `NETTO` untuk tagihan/piutang.

---

## 4. Pemetaan Tabel Legacy → Modul Website

### 4.A Login, Role & Hak Akses — `AuthController`, middleware `role:admin|sales`

| Kebutuhan Web | Padanan Legacy | Kolom Kunci |
|---|---|---|
| Akun user | **`MST_PENGGUNA`** (5 baris) | `NO_USER`, `NM_USER`, `KATAKUNCI`, `NO_OTOR` |
| Role | **`MST_OTORITAS`** (10 baris) | `NO_OTOR`, `NM_OTOR` |
| Hak akses granular | **`PENGGUNA`** (436 baris) + **`OTORITAS`** (1.101 baris) | `NO_USER`/`NO_OTOR`, `KODE`, `ST_ISI`, `ST_EDIT`, `ST_DEL`, `ST_CARI`, `ST_LAPORAN`, `ST_ACSES` |
| Daftar form/menu | **`OBYEK_FORM`** (145 form, 10 menu) | `KODE`, `NM_FORM`, `MENU`, `ST_AKTIF` |

**Isi `MST_OTORITAS` (siap dipetakan ke role web):**

| NO_OTOR | NM_OTOR | Saran role web |
|---|---|---|
| 10 | OWNER | admin (super) |
| 9 | MANAGER | admin |
| 4 | SUPERVISOR | admin (read-only) |
| 5 | SALES KOORDINATOR | admin |
| 8 | SALES_SPV | admin |
| 1 / 3 | BAG_PENJUALAN / BAG_PENJUALAN2 | admin |
| 2 | BAG_GUDANG | admin (gudang) |
| 6 | KASIR | admin (kasir) |
| 7 | SUPERVISOR2 | admin |

**Akun legacy yang ada (5):** `HENIS` (NO_USER 1), `SONI` (2), `HUDA` (3), `ROY` (4), `PRATATO` (5).

**Jumlah hak per user:** user 1 = 111 hak, user 2 = 111, user 3 = 3, user 4 = 107, user 22 = 104.

> ⚠️ **Password legacy tidak kompatibel dengan Laravel `Hash`.**
> `MST_PENGGUNA.KATAKUNCI` berisi 11 karakter acak, contoh:
> `aTmKXgBBfTU`, `m5YGOxSMq1k`, `oGg22GQNhbs`, `yIzW9K/l36E`, `75kZ.Z5PxU2`
> Charset-nya mirip base64 + karakter `.` dan `/` → **enkripsi/obfuscation proprietary ClickSoft**, bukan bcrypt/md5/sha.
> **Rekomendasi:** tetap gunakan tabel `users` sendiri (Laravel) untuk login web, lalu map `username` → `MST_PENGGUNA.NM_USER` + `NO_OTOR` untuk role/hak akses.

---

### 4.B Master Pelanggan — `CustomerController`, `customers`

| Field Web | Tabel/Kolom Legacy | Catatan |
|---|---|---|
| Kode | `CUSTOMER.KD_CUST` (PK, VARCHAR(9)) | contoh `BWS633`, `TM70`, `AR107` |
| Nama | `CUSTOMER.NM_CUST` | |
| Alamat | `CUSTOMER.ALM_CUST` | |
| Telepon | `CUSTOMER.TELP1`, `CUSTOMER.HP` | |
| Email | `CUSTOMER.E_MAIL` | |
| Kategori | `CUSTOMER.KD_KAT` → `KAT_CUSTOMER.KATEGORI` | `01`=OUTLET, `02`=GROSIR |
| Wilayah | `CUSTOMER.KD_WIL` → `WILAYAH.WILAYAH` | `001`=BONDOWOSO, `002`=SITUBONDO |
| **Sales penanggung jawab** | `CUSTOMER.KD_PEG` → `PEGAWAI.NM_PEG` | sangat berguna untuk filter data per sales |
| Saldo piutang | `CUSTOMER.JML_PIUTANG`, `JML_BAYAR`, `SO_AWAL_PIUTANG` | |
| Limit kredit | `CUSTOMER.KRD_LIMIT`, `TOP_LIMIT`, `TGL_LIMIT` | |
| Pajak | `CUSTOMER.NPWP`, `NM_PKP`, `ALM_PKP` | |
| Poin/loyalty | `CUSTOMER.POINT_JL`, `POINT_GUNA`, `STS_MEMBER` | |

**Data:** `CUSTOMER` = **2.277 baris**. Sebaran per sales: T2455=658, T2453=649, T2454=399, T2456=212, T2445=140, T2461=66, dst. Hanya **5 pelanggan tanpa sales** → relasi bersih.

**View siap pakai:** `VW_CUST` (28 kolom, sudah JOIN `KAT_CUSTOMER`), `VW_CUSTOMER`, `VW_LAP_CUSTOMER`, `VW_PIUTANG_CUST`, `VW_CUST_SUPPL_PEG`.


---

### 4.C Master Produk — `ProductController`, `products`

| Field Web | Tabel/Kolom Legacy |
|---|---|
| Kode | `BARANG.KD_BRG` (PK, VARCHAR(20)) |
| Nama | `BARANG.NM_BRG` (+ `NM_BRG2`) |
| Jenis | `BARANG.JNS_BRG`, `JNS_SEDIA`, `KD_JNS_BRG` → `JNS_BARANG` / `MST_JNS_BARANG.NM_GRUP_BRG` |
| Satuan | `BARANG.SATUAN1..4`, konversi `SAT_TUR2..4`, `KAPASITAS2..4` |
| Dimensi | `PJG_CM`, `LBR_CM`, `PJG_INC`, `LBR_INC`, `TEBAL`, `BERAT` |
| Harga jual | `BARANG.HARGA_JL` (+ multi-level di `HRG_JUAL_BARANG`, `HRG_PRODUK`) |
| Harga beli / HPP | `BARANG.HARGA_BL`, `BARANG.HPP` |
| Stok | `BARANG.STOK`, `STOK_MIN`, `SAT_MIN` — ⚠️ **semua bernilai 0** |
| Supplier | `BARANG.KD_SUPPL` |

**Data:** `BARANG` = **24 SKU** (contoh: `DBL01` DESAKU BALADO 12.5 GR, `DKU02` DESAKU KUNYIT BUBUK 7.5GR — semua grup `LADAKU`).
`HRG_JUAL_BARANG` = 26 baris, `HRG_PRODUK` = 24 baris (kolom `HARGA_SBLM1..5`, `JUMLAH1..5` — semua 0).

> ⚠️ **Stok riil TIDAK ada di `BARANG`.** Stok per gudang ada di **`MUTASI_BARANG`**
> (`QTY_AWAL`, `QTY_MASUK`, `QTY_KELUAR`, `QTY_AKHIR`, `RP_AKHIR`, `QTY_ORDER`).

**View siap pakai:** `VW_BARANG` (58 kolom), `VW_STOK_BARANG` (92 kolom), `VW_HRG_JUAL` (4 kolom), `VW_BARANG_LOOKUP`, `VW_LAP_BARANG`, `VW_SA_BARANG`.

---

### 4.D Gudang & Stok — `WarehouseController`, `warehouses`, `warehouse_stocks`

| Field Web | Tabel/Kolom Legacy |
|---|---|
| Nama gudang | `GUDANG.NM_GUDANG` (PK) |
| Keterangan | `GUDANG.KET` |
| Penanggung jawab | `GUDANG.KD_PEG` → `PEGAWAI.NM_PEG` |
| Akun terkait | `NO_AKUN_KAS`, `NO_AKUN_SEDIA`, `NO_AKUN_JUAL`, `NO_AKUN_HPP`, `NO_AKUN_BELI` |
| Stok per gudang | `MUTASI_BARANG` (`GUDANG` + `KD_BRG`) |

**Data:** `GUDANG` = **2 baris**: `01-GUDANG TM` dan `02-GUDANG BS`.
`MUTASI_BARANG` = **48 baris** = 2 gudang × 24 barang.

> ⚠️ Tidak ada tabel `warehouse_stocks`. Gunakan **`MUTASI_BARANG.QTY_AKHIR`** sebagai stok berjalan,
> atau view **`VW_STOK_BARANG`** (92 kolom, sudah join `BARANG` + `JNS_BARANG` + `MUTASI_BARANG`).
> Catatan: `VW_STOK_BARANG_BRG.GUDANG` bertipe `CHAR(0)` → perlu handling di aplikasi.

**Tabel stok lain:** `KET_STOK` (0), `DET_STOK_GUDANG` (0), `STOK_OPNAME` (0), `STOK_BARANG_EXP` (0), `DET_IO_STOK` (0) — belum terpakai.

---

### 4.E Order Penjualan — `sales_orders`, `order_items`, `OrderController`

| Field Web | Tabel/Kolom Legacy |
|---|---|
| No. order | `MST_ORD_JUAL.NO_ENT` (PK, format `OJ2608/001/00023`) |
| Tanggal | `MST_ORD_JUAL.TANGGAL`, `TGL_AWAL`, `TGL_AKHIR`, `RLS_TGL_KIRIM` |
| Pelanggan | `MST_ORD_JUAL.KD_CUST` |
| Total | `MST_ORD_JUAL.TOTAL` = **Σ `DET_ORD_JUAL.SUB_TOTAL`** ✅ (terverifikasi) |
| Sales | `MST_ORD_JUAL.KD_PEG` → `PEGAWAI` |
| User input | `MST_ORD_JUAL.KD_USER` |
| Status | `MST_ORD_JUAL.ST_JADI` → `OS` = order saja, `INV` = sudah jadi faktur |
| Proses | `MST_ORD_JUAL.PROSES` |
| Rencana kirim | `RNC_TGL_KIRIM`, `TGL_EXP`, `TGL_HARGA` |
| Termin | `TOP`, `TOP_PROS`, `TOP_PROS_HARI`, `JNS_BYR` |
| Alamat kirim | `NM_KIRIM`, `ALM_KIRIM`, `TELP_KIRIM`, `NM_BRG_KIRIM` |
| Relasi dokumen | `NO_ENT_ORD` = **ID batch upload mobile** (bukan nomor order, hanya 148 unik untuk 2.978 baris!), `NO_ENT_PO`, `TGL_PO`, `NO_ENT_JUAL` |
| Ekspedisi | `KD_EKSPEDISI` |
| Gudang | `GUDANG`, `KD_STOK` |
| **Item order** | `DET_ORD_JUAL` (`NOMOR`, `NO_ENT`, `NMR`, `KD_BRG`, `NM_BRG`, `SATUAN`, `SAT_KE`, `JUMLAH`, `HARGA`, `DISC1`, `DISC2`, `DISC_RP`, `TOTAL`, `SUB_TOTAL`, `JML_TUR`, `JML_KIRIM`, `SAK`, `HPP`, `GUDANG`, `KD_STOK`, `GAMBAR`, `NO_ENT_ORD`) |

**Data & validasi relasi:**

| Cek | Hasil |
|---|---|
| `MST_ORD_JUAL` | **2.978 baris** (ST_JADI: `INV` 2.950, `OS` 28) |
| `DET_ORD_JUAL` | **9.228 baris** |
| Order tanpa item | **0** ✅ |
| Item → `BARANG` | 9.228 / 9.228 cocok ✅ |
| Order → `CUSTOMER` | 2.973 / 2.978 cocok ✅ |
| Order → `PEGAWAI` | 2.978 / 2.978 cocok ✅ |
| Order sudah jadi faktur (`MST_JUAL.NO_ENT_ORD`) | 2.950 / 2.978 ✅ |
| Order per sales | T2462=859, T2465=597, T2466=572, T2461=517, T2455=218, T2463=215 |
| Rentang tanggal | 2026-07-01 s/d 2026-08-06 |

> ✅ **VERIFIKASI NILAI (sudah diuji, bukan asumsi):**
>
> - `MST_ORD_JUAL.TOTAL` **= Σ `DET_ORD_JUAL.SUB_TOTAL`** → **27 / 27 order cocok** (diuji pada seluruh order Agustus). **Tidak ada anomali.**
> - `DET_ORD_JUAL.TOTAL` **selalu `0`** (kolom legacy tak terpakai) → **jangan dipakai**, gunakan `SUB_TOTAL`.
> - `DET_ORD_JUAL.SAK` **selalu `0`** untuk seluruh 9.228 baris → kolom tak terpakai.
> - **`JML_TUR` = jumlah dalam satuan terkecil (PCS).** Rumus terverifikasi:
>   `JML_TUR = JUMLAH × KAPASITAS(SAT_KE)` → **25 / 25 sampel cocok**.
>   Contoh: `DCB05` 1 PAK × 24 PCS = `24`; 3 PAK = `72`; 5 PAK = `120`; `LDK33` 1 PAK = `72` (sesuai `BARANG.KAPASITAS2`).
> - `SAT_KE` = level satuan transaksi (`2` = PAK → mengacu ke `BARANG.SATUAN2` + `KAPASITAS2`).
> - `MST_JUAL.NETTO` **= Σ `DET_JUAL.SUB_TOTAL`** pada faktur ✅ (diuji, semua COCOK).
>
> **Master satuan (dari `BARANG`):** setiap SKU punya `SATUAN1`=PCS, `SATUAN2`=PAK, `SATUAN3`=KRT.
> Konversi: `KAPASITAS2` = PCS per PAK (10/12/24/72 tergantung SKU), `KAPASITAS3` = PAK per KRT (8/12/18/36).

**View siap pakai:** `VW_ORD_JUAL` (33 kolom), `VW_ORD_JUAL_DETAIL` (48), `VW_MST_ORD_JUAL` (20), `VW_DET_ORD_JUAL`, `VW_ORD_JUAL_1`, `VW_DET_ORDER_JUAL`.

---

### 4.F Order dari Mobile Sales — **BONUS: `SO_MOB`**

Ini temuan penting: sudah ada tabel order yang diinput dari mobile.

`SO_MOB` = **9.236 baris** (data per item). Kolom:

```
NOMOR (PK)      NO_ENT_ORD     NO_ENT        TANGGAL       TGL_HARGA
RNC_TGL_KIRIM   TGL_EXP        KD_CUST       KD_CUST_REFF  ST_TUNAI
KD_PEG          KD_PEG_REFF    JNS_TRANS     ST_UPLOAD_M   TOP
NO_ENT_PO       TGL_PO         LATITUDE      LONGITUDE     NMR
KD_BRG          KD_BRG_REFF    JNS_TRANS_DET JUMLAH        SATUAN
HARGA           DISC1          DISC2         DISC_RP       SUB_TOTAL
KET
```

| Cek | Hasil |
|---|---|
| Total baris | 9.236 |
| `NO_ENT` unik | **152** → ini **ID batch sinkronisasi mobile** |
| `NO_ENT_ORD` unik | **152** |
| Unik (`NO_ENT_ORD`, `KD_CUST`, `TANGGAL`) | **2.975** |
| **Kunjungan** = unik (`KD_PEG`,`KD_CUST`,`TANGGAL`) | **2.974** |
| `JNS_TRANS` | hanya `JUAL` |
| `ST_UPLOAD_M` | semua `1` (sudah ter-upload) |
| Rentang tanggal | 2026-06-30 s/d 2026-08-06 |
| Σ `SO_MOB.SUB_TOTAL` = `MST_ORD_JUAL.TOTAL` | **COCOK** ✅ (diuji pada seluruh order yang punya pasangan) |
| **`LATITUDE` / `LONGITUDE` terisi** | **0 / 9.236 (semuanya NULL)** ❌ |

#### ⚠️ KOREKSI PENTING: arti `NO_ENT_ORD` & `NO_ENT` di `SO_MOB`

Sempat terlihat `NO_ENT_ORD` = nomor order. **Ternyata tidak.** Hasil uji:

| Fakta | Nilai |
|---|---|
| `SO_MOB.NO_ENT` | 152 unik untuk 9.236 baris → **ID batch upload** (format `MIS2608000038`) |
| `SO_MOB.NO_ENT_ORD` | 152 unik → hanya 49 yang berisi 1 pelanggan; sisanya berisi **2 s/d belasan pelanggan & beberapa tanggal** |
| Contoh nyata | `OJ2607/001/02854` = 158 baris, **33 pelanggan, 27 tanggal**, semuanya `NO_ENT` sama. **Batch sebulan penuh.** |
| Batch `OJ2607/001/02854` di `MST_ORD_JUAL` | **TIDAK ADA** (0 baris) — batch lama tidak melewati `MST_ORD_JUAL` |

**Kunci join yang benar:**

| Dari | Ke | Arti |
|---|---|---|
| `MST_ORD_JUAL.NO_ENT` | `SO_MOB.NO_ENT_ORD` | nomor order (hanya untuk data baru) |
| `MST_ORD_JUAL.NO_ENT_ORD` | `SO_MOB.NO_ENT` | **ID batch upload mobile** — inilah kolom yang benar-benar menghubungkan |
| `MST_ORD_JUAL.NO_ENT_ORD` unik | **148** untuk 2.978 baris | ⚠️ **bukan** kunci unik |

> ✅ Terbukti pada 1 order: `MST_ORD_JUAL.NO_ENT = 'OJ2608/001/00012'`, `NO_ENT_ORD = 'MIS2608000038'`
> ←→ `SO_MOB.NO_ENT = 'MIS2608000038'`, `NO_ENT_ORD = 'OJ2608/001/00012'`. **Persis silang.**

**Order per sales** (dari `MST_ORD_JUAL`): T2462 (SYAIFUL IMAM BWS)=859, T2465 (RAFIQ STB)=597, T2466 (SENDYA RAHMAN BWS)=572, T2461 (SEPTIAN)=517, T2455 (MOHAMMAD ZAINURI STB)=218, T2463 (IMAM JUNAIDI BWS)=215. Semua nama diambil dari `VW_SALES`/`PEGAWAI`.

> 💡 **Alur data yang benar:** `SO_MOB` (mobile, `ST_UPLOAD_M=1`) → `MST_ORD_JUAL`+`DET_ORD_JUAL` (desktop) → `MST_JUAL`+`DET_JUAL` (faktur).
> Untuk data Juli ke belakang, mobile langsung menghasilkan faktur (`MST_JUAL`) tanpa melewati `MST_ORD_JUAL`.

---

### 4.G Faktur / Invoice — `invoices`, `TagihanController`

| Field Web | Tabel/Kolom Legacy |
|---|---|
| No. faktur | `MST_JUAL.NO_ENT` (PK, format `J2607/1/001/000006`) |
| No. order asal | `MST_JUAL.NO_ENT_ORD` → `MST_ORD_JUAL.NO_ENT` |
| Tanggal | `MST_JUAL.TANGGAL`, `TGL_ORD` |
| Pelanggan | `MST_JUAL.KD_CUST`, `NM_CUST`, `ALM_CUST`, `TELP1_C`, `TELP2_C` |
| Nilai | `TOTAL`, `NETTO`, `DISC_PR`, `TOT_DISC_RP`, `POT1`, `POT2`, `PPN`, `PPN_RP`, `U_MUKA` |
| Sudah dibayar | `JML_BAYAR`, `JML_RETUR`, `JML_KUPON` |
| **Sisa piutang (view)** | `VW_PIUTANG.SISA_PIUTANG` = `NETTO − (JML_BAYAR + U_MUKA + JML_RETUR + JML_KUPON)` |
| Jatuh tempo (view) | `VW_PIUTANG.TGL_JATUH_TEMPO` = `TANGGAL + TOP` |
| Total bayar (view) | `VW_PIUTANG.TOT_JML_BAYAR` |
| Jenis bayar | `MST_JUAL.JNS_BYR` (`KREDIT` / `TUNAI`) |
| Termin | `MST_JUAL.TOP` |
| Sales | `MST_JUAL.KD_PEG`, `NM_PEG` |
| Salesman group | `KD_PGRM`, `NM_PGRM` |
| Gudang | `MST_JUAL.GUDANG` (`01-GUDANG TM`) |
| Ekspedisi | `KD_EKSPEDISI`, `NM_KIRIM`, `ALM_KIRIM`, `NO_EKS_JUAL` |
| Status | `STS_PENDING`, `STS_SIMPAN`, `STS_BATAL`, `STS_TRANSFER`, `STS_SO_AWAL` |
| Cetak | `KD_USER_CETAK`, `TGL_CETAK`, `JAM_CETAK`, `CETAKAN_KE` |
| **Item faktur** | `DET_JUAL` (`NO_ENT`, `KD_BRG`, `NM_BRG`, `SATUAN`, `JUMLAH`, `HARGA`, `DISC1`, `DISC2`, `SUB_TOTAL`, `HPP`, `JML_KIRIM`, `SAK`) |
| Pajak | `MST_FAK_PAJAK` / `DET_FAK_PAJAK`, `MST_FP_JUAL` / `DET_FP_JUAL`, `JNS_PAJAK` |
| Retur | `MST_RET_JUAL` / `DET_RET_JUAL` (`NO_ENT`, format `RJ2607/0/001/00024`) |

**Data & validasi:**

| Cek | Hasil |
|---|---|
| `MST_JUAL` | **99.809 baris** |
| `DET_JUAL` | **9.116 baris** (semua punya faktur induk ✅) |
| `MST_RET_JUAL` / `DET_RET_JUAL` | 122 / 230 (semua terhubung ✅) |
| `MST_JUAL_PIUTANG` | 96.859 baris (hanya faktur kredit) |
| `JUAL_BARANG` | 8.694 baris |
| `JUAL_BERSIH` | 30 baris |

> ### 🔴 TEMUAN KRITIS: 96.859 dari 99.809 faktur **TIDAK punya rincian item**
>
> Diuji langsung ke DB:
>
> | Cek | Hasil |
> |---|---|
> | `MST_JUAL` total | **99.809** |
> | `MST_JUAL` yang punya baris di `DET_JUAL` | **2.950** ✅ (semua faktur baru Jul–Agu 2026) |
> | `MST_JUAL` **tanpa** `DET_JUAL` | **96.859** |
> | `MST_JUAL` dengan `STS_SO_AWAL = 1` | **96.859** (angka persis sama) |
> | `MST_JUAL_PIUTANG` | **96.859** (angka persis sama) |
>
> **Kesimpulan:** faktur lama hasil migrasi/saldo awal (`STS_SO_AWAL = 1`) disimpan **hanya sebagai header** di `MST_JUAL` + saldo piutang di `MST_JUAL_PIUTANG`.
> **Rincian item-nya tidak ada.**
>
> **Dampak ke website:**
> - Halaman detail invoice → **kosong** untuk 96.859 faktur lama. Perlu desain UI "rincian tidak tersedia (data migrasi)".
> - Laporan penjualan per barang (`DET_JUAL`, `JUAL_BARANG`) → hanya mencakup ±3% data (Juli–Agustus 2026).
> - Filter wajib: `MST_JUAL.STS_SO_AWAL = 0` untuk faktur yang punya detail.

> 💡 **Ini modul yang paling "murah" untuk diintegrasikan**: view `VW_PIUTANG` (24 kolom) sudah menghitung sisa piutang & jatuh tempo.
> **View siap pakai:** `VW_PIUTANG` (24 kolom), `VW_JUAL_DETAIL` (58), `VW_MST_JUAL` (48), `VW_PENJUALAN` (68), `VW_LAP_JUAL`, `VW_REKAP_JUAL`, `VW_RET_PENJUALAN` (54).

---

### 4.H Pembayaran / Piutang — `payments`, `PembayaranController`

| Field Web | Tabel/Kolom Legacy |
|---|---|
| Bukti bayar | `MST_BYR_PIUTANG.NO_ENT` |
| Kartu piutang (mutasi) | **`DET_KRT_PIUTANG`** (`NOMOR`, `TANGGAL`, `NO_BUKTI`, `KD_CUST`, `KET`, `DEBET`, `KREDIT`, `SALDO`, `BYR_TUNAI`, `BYR_CEK`, `TGL_JT`, `NM_PEG`) |
| Kartu penjualan | `DET_KRT_JUAL` (`NO_BUKTI` format `RJ...` = retur penjualan) |
| Piutang supplier | `PIUTANG_SUPPL` (99.937 baris) |
| Transfer piutang | `TRANSF_PIUTANG` / `DET_TRANSF_PIUTANG` (695 / 101.970) |
| Saldo awal piutang | `MST_SA_PIUTANG` / `DET_SA_PIUTANG`, `DET_SA_PRODUK` |
| Hapus piutang | `MST_DEL_PIUTANG` / `DET_DEL_PIUTANG` |
| Kupon/poin | `KUPON`, `MST_BYR_KUPON`, `DET_BYR_KUPON`, `TUKAR_POINT` |
| Kas masuk/keluar | `MST_KAS_MASUK` / `DET_KAS_MASUK`, `MST_KAS_KELUAR` / `DET_KAS_KELUAR` |
| Kas harian | `MST_KAS_HARIAN` / `DET_KAS_HARIAN` |

**Contoh nyata `DET_KRT_PIUTANG`:**
```
NOMOR=96874  TANGGAL=2026-07-02  NO_BUKTI=J2607/1/001/000017
KD_CUST=BWS219  KET=PENJUALAN  DEBET=114000  KREDIT=0  SALDO=114000
BYR_TUNAI=0  BYR_CEK=0  TGL_JT=2026-07-02  NM_PEG=SENDYA RAHMAN BWS
```

**View siap pakai:** `VW_KARTU_PIUTANG` (+`_2`, `_3`), `VW_BYR_PIUTANG`, `VW_MST_BYR_PIUTANG`, `VW_DET_BYR_PIUTANG`, `VW_MAX_BYR_PIUTANG`, `VW_SA_PIUTANG`, `VW_PIUTANG`, `VW_DEL_PIUTANG`, `VW_KAS_MASUK`, `VW_KAS_KELUAR`.

> ⚠️ Tidak ada status approval di DB. Tabel `payments` di web (dengan `approve`/`reject`) tetap perlu tabel sendiri,
> atau status approval disimpan di tabel tambahan milik aplikasi baru.

---

### 4.I Sales & Kunjungan — `sales_visits`, `VisitController`, `StockController`

| Kebutuhan Web | Padanan Legacy |
|---|---|
| Daftar sales | **`PEGAWAI`** dengan `STS_SALES='YA'` (view **`VW_SALES`**: `KD_PEG`, `NM_PEG`, `ALM_PEG`, `TELP1`, `TELP2`) |
| Nama/jabatan | `PEGAWAI.NM_PEG`, `KD_DEPT`, `DEPT` |
| Kontak | `PEGAWAI.HP`, `E_MAIL`, `TELP1`, `TELP2` |
| Wilayah tugas | `PEGAWAI.KD_WIL` |
| Status | `PEGAWAI.ST_AKTIF` = `AKTIF`, `STS_SALES` = `YA` |
| **Kunjungan (proxy)** | **`SO_MOB`** — 1 kunjungan = kombinasi unik (`KD_PEG`, `KD_CUST`, `TANGGAL`) |
| Jadwal kunjungan | `MST_JADWAL` / `DET_JADWAL` + `JNS_JADWAL` (**0 baris — belum dipakai**) |
| Rekap efektifitas | **`REKAP_OMZET_EFF_CALL`** (`KD_PEG`, `TOTAL_OMZET`, `BYK_FAKTUR`, `EFF_CALL`) |
| Rekap detail | `REKAP_OMZET_EFF_CALL_TMP` (1.919), `_TMP_2` (2.013) |
| Target/insentif | `DET_SETTING_INSENTIF`, `REKAP_INSENTIF_SALES`, `REKAP_INSENTIF_PGRM`, `DET_BRG_INSENTIF` |

**Data `PEGAWAI`:** **15 baris, semua `STS_SALES='YA'` dan `ST_AKTIF='AKTIF'`** (T2453 IMAM JUNAIDI, T2454 MOHAMMAD ZAINURI BWS, T2455 MOHAMMAD ZAINURI STB, T2456 DIMAS BWS, T2457 OFFICE, T2445 SURYADI, T2458 SAMSUL, T2459 RAFIQ BWS, dst).

**`REKAP_OMZET_EFF_CALL` (siap pakai untuk dashboard sales):**

| KD_PEG | TOTAL_OMZET | BYK_FAKTUR | EFF_CALL |
|---|---|---|---|
| T2455 | 454.186.726 | 154 | 153 |
| T2463 | 425.816.078 | 142 | 142 |
| T2466 | 89.981.969 | 450 | 450 |
| T2462 | 84.885.472 | 461 | 461 |
| T2465 | 56.752.370 | 384 | 383 |
| T2461 | 50.793.494 | 312 | 312 |

**Perhitungan kunjungan (terverifikasi):**

| KD_PEG | Kunjungan = unik (`KD_PEG`,`KD_CUST`,`TANGGAL`) | `EFF_CALL` di `REKAP_OMZET_EFF_CALL` |
|---|---|---|
| T2462 | 859 | 461 |
| T2465 | 597 | 383 |
| T2466 | 572 | 450 |
| T2461 | 519 | 312 |
| T2455 | 216 | 153 |
| T2463 | 211 | 142 |
| **Total** | **2.974** | — |

> `EFF_CALL` legacy ≠ jumlah baris `SO_MOB`. Jadi **jangan** menyamakan `EFF_CALL` dengan jumlah kunjungan.
> Kunjungan riil = unik (`KD_PEG`, `KD_CUST`, `TANGGAL`) dari `SO_MOB` = **2.974 kunjungan**.

> ⚠️ Tidak ada tabel `sales_visits`. Kolom GPS di `SO_MOB` kosong → **fitur check-in lokasi tidak bisa di-backfill dari data lama**.

---

### 4.J Laporan / Dashboard — `LaporanController`, `DashboardController`

| Kebutuhan | Tabel/View Legacy |
|---|---|
| Penjualan harian | **`TRANS_HARIAN`** (`TANGGAL`, `JUAL_RP`, `R_JUAL_RP`, `BELI_RP`, `R_BELI_RP`, `BYR_HUTANG_RP`, `BYR_PIUTANG_RP`, `STS_TUTUP`) — 1.094 baris |
| Rekap omzet per sales | `REKAP_OMZET_EFF_CALL`, `REKAP_JL` (12), `REKAP_JL_TMP` (23) |
| Rekap per barang | `REKAP_BARANG` (13), `REKAP_BRG_SUPPL` (32), `VW_REKAP_BARANG` |
| Mutasi barang | `MUTASI_BARANG` + `MUTASI_BARANG_A..E` |
| Buku besar / akuntansi | `BUKU_BESAR` (6.154), `JURNAL`, `NERACA`, `LABA_RUGI`, `LABA_RUGI_AKUM`, `ARUS_KAS`, `AKUN` (151), `REK_AKTIVA`, `MUTASI_KEU` |
| View laporan siap pakai | `VW_PENJUALAN`, `VW_LAP_JUAL`, `VW_REKAP_JUAL`, `VW_REK_JUAL`, `VW_MUTASI`, `VW_BUKU_BESAR`, `VW_NRC_SALDO`, `VW_HUTANG`, `VW_PIUTANG` |

**Contoh `TRANS_HARIAN`:**
```
TANGGAL=2026-07-31  JUAL_RP=86.240.610  R_JUAL_RP=37.000   STS_TUTUP=BELUM
TANGGAL=2026-07-30  JUAL_RP=87.450.200  R_JUAL_RP=203.449  STS_TUTUP=BELUM
```

---

### 4.K ⚠️ POLA "CLOSING BULANAN" — Batasan Data Paling Kritis

Hasil uji cakupan periode (`db_analyze7`) mengungkap bahwa ERP legacy ini **menutup buku tiap bulan** dan memindahkan data ke tabel arsip:

| Tabel | Isi | Rentang Tanggal | Baris |
|---|---|---|---|
| `MST_JUAL` | **semua header faktur** (gabungan berjalan + arsip) | 2022-12-08 → 2026-07-31 | 99.809 |
| **`DET_JUAL`** | **detail item — HANYA BULAN BERJALAN** | **2026-07-02 → 2026-07-31** | **9.116** |
| `MST_JUAL_PIUTANG` | arsip header faktur kredit (bulan tertutup) | 2022-12-08 → **2026-06-30** | 96.859 |
| `MST_ORD_JUAL` | order penjualan | 2026-07-01 → 2026-08-06 | 2.978 |
| `DET_ORD_JUAL` | detail order (⚠️ **tidak punya kolom `TANGGAL`**) | — (hanya Jul–Agu 2026) | 9.228 |
| `SO_MOB` | order dari mobile sales | 2026-06-30 → 2026-08-06 | 9.236 |
| `JUAL_BARANG` | **agregat penjualan per barang/tanggal** (pengganti detail historis) | 2022-12-06 → 2026-07-31 | 8.694 (24 SKU) |
| `DET_KRT_PIUTANG` | kartu piutang | 2022-12-08 → 2026-07-31 | 99.929 |
| `TRANS_HARIAN` | rekap harian | s/d 2026-07-31 | 1.094 |

**Rekonsiliasi angka (bukti matematis pola arsip):**

```
MST_JUAL_PIUTANG (arsip s/d Juni 2026)         =  96.859
MST_JUAL bulan Juli 2026                       =   2.950
                                                  -------
                                            total = 99.809  = COUNT(MST_JUAL)  ✅ COCOK PERSIS
```

**Penanda migrasi di kartu piutang** — `DET_KRT_PIUTANG.KET`:

| KET | Jumlah | Arti |
|---|---|---|
| `PENJUALAN (SALDO AWAL)` | **96.857** | saldo awal hasil migrasi (tanpa detail item) |
| `PENJUALAN` | 2.950 | faktur bulan berjalan (punya detail `DET_JUAL`) |
| `RETUR ATAS FAKTUR No. ...` | ±103 | retur per faktur |

> ### ❌ DAMPAK LANGSUNG KE DESAIN WEBSITE
>
> 1. **Halaman detail invoice akan KOSONG untuk 96.859 faktur lama** (97% data).
>    → Wajib ada UI: *"Rincian item tidak tersedia (data migrasi saldo awal)"*.
> 2. **Filter wajib**: `MST_JUAL.STS_SO_AWAL = 0` → hanya 2.950 faktur yang punya item.
> 3. **Laporan penjualan per barang** hanya bisa akurat untuk Juli–Agustus 2026 (`DET_JUAL`).
>    Untuk periode lebih lama, gunakan **`JUAL_BARANG`** (agregat per SKU, tanpa detail per pelanggan).
> 4. **Laporan laba kotor (HPP)** hanya bisa per item untuk bulan berjalan (`DET_JUAL.HPP`, `LABA_KOTOR`).
> 5. **Retur penjualan** (`MST_RET_JUAL`/`DET_RET_JUAL`, 122/230 baris) hanya bulan berjalan.
> 6. **Piutang** tetap lengkap sepanjang masa karena ada di `DET_KRT_PIUTANG` + `CUSTOMER.JML_PIUTANG`.
>
> **Kesimpulan:** modul **piutang, pelanggan, master barang, stok, dan kas harian → siap penuh**.
> Modul **detail transaksi (invoice/order) → hanya untuk Juli–Agustus 2026**.

#### Bukti tambahan: `MST_JUAL.TOTAL` vs `NETTO`

Diuji pada 2.950 faktur Juli 2026:

| Cek | Hasil |
|---|---|
| `TOTAL == Σ DET_JUAL.SUB_TOTAL` | **2.422** faktur |
| `NETTO == Σ DET_JUAL.SUB_TOTAL` (sisanya 528, faktur bergrosir/berdiskon) | **528** faktur |
| Faktur Juli tanpa detail | **0** ✅ |
| `TOTAL == NETTO` di seluruh `MST_JUAL` | 92.657 / 99.809 |

Contoh faktur berdiskon (`J2607/1/001/002948`): `TOTAL = 6.840.000` → `NETTO = 6.703.200` = `Σ SUB_TOTAL`.

> ✅ **Aturan aman:** untuk **nilai tagihan** pakai **`MST_JUAL.NETTO`** (bukan `TOTAL`).
> Verifikasi order (`MST_ORD_JUAL`) — `TOTAL = Σ DET_ORD_JUAL.SUB_TOTAL` **cocok 27/27** order Agustus (tidak ada `TOTAL` vs `NETTO` di level order).

---

## 5. Ringkasan Pemetaan (Tabel Legacy → Modul Web)

| Modul Web | Tabel/View Legacy yang Dipakai | Status Data | Kelayakan |
|---|---|---|---|
| **Login & Role** | `MST_PENGGUNA`, `MST_OTORITAS`, `PENGGUNA`, `OTORITAS`, `OBYEK_FORM` | 5 user, 10 role, 145 form | ⚠️ Password tidak kompatibel → pakai tabel `users` sendiri |
| **Master Pelanggan** | `CUSTOMER` (+`KAT_CUSTOMER`, `WILAYAH`), `VW_CUST` | 2.277 | ✅ Sangat siap |
| **Master Produk** | `BARANG`, `JNS_BARANG`, `HRG_JUAL_BARANG`, `VW_BARANG` | 24 SKU | ✅ Siap (stok di tabel lain) |
| **Gudang & Stok** | `GUDANG`, `MUTASI_BARANG`, `VW_STOK_BARANG` | 2 gudang, 48 stok | ✅ Siap |
| **Order Penjualan** | `MST_ORD_JUAL`, `DET_ORD_JUAL`, `VW_ORD_JUAL` | 2.978 / 9.228 | ✅ Siap (nilai & satuan terverifikasi) |
| **Order Mobile** | `SO_MOB` | 2.974 kunjungan / 9.236 item / 152 batch | ✅ Siap (GPS kosong) |
| **Faktur/Tagihan** | `MST_JUAL`, `DET_JUAL`, `MST_RET_JUAL`, `DET_RET_JUAL`, `VW_PIUTANG` | 99.809 faktur / 9.116 item | ⚠️ Siap, tapi hanya 2.950 faktur punya rincian (lihat **4.K**); nilai tagihan pakai `NETTO` |
| **Pembayaran/Piutang** | `DET_KRT_PIUTANG`, `MST_BYR_PIUTANG`, `TRANSF_PIUTANG`, `VW_KARTU_PIUTANG` | 99.929 mutasi | ✅ Siap penuh sepanjang masa (tanpa approval) |
| **Sales & Kunjungan** | `PEGAWAI`, `VW_SALES`, `SO_MOB`, `REKAP_OMZET_EFF_CALL` | 15 sales | ⚠️ Kunjungan hanya bisa di-*proxy* dari `SO_MOB` (2.974 kunjungan) |
| **Laporan/Dashboard** | `TRANS_HARIAN`, `REKAP_*`, `BUKU_BESAR`, view `VW_*` | berjalan | ✅ Sangat siap |
| **Laporan per barang (historis)** | `JUAL_BARANG` (2022-12 → 2026-07) | 8.694 baris, 24 SKU | ⚠️ Agregat saja, tanpa detail per pelanggan |

---

## 6. Tabel Legacy yang TIDAK Relevan untuk Website

Dari 340 tabel, mayoritas tidak dibutuhkan modul web saat ini:

- **Akuntansi lengkap:** `AKUN`, `AKTIVA`, `BUKU_BESAR`, `JURNAL`, `NERACA`, `LABA_RUGI*`, `ARUS_KAS`, `MUTASI_KEU*`, `RATIO`, `REK_AKTIVA`, `KAT_AKTV`, `JNS_BIAYA`, `TRANS_BIAYA`.
- **Pembelian/hutang:** `MST_BELI*`, `DET_BELI*`, `MST_BYR_HUTANG*`, `DET_BYR_HUTANG*`, `MST_ORD_BELI*`, `MST_PURCHASE_ORD*`, `MST_RET_BELI*`, `MST_DEL_HUTANG*`, `SUPPLIER`.
- **Produksi:** `MST_RK_PRODUKSI`, `DET_RK_PRODUKSI`, `MST_LH_PRODUKSI`, `HASIL_PRODUKSI`, `HP_PRODUKSI`, `JNS_PROSES_PRODUKSI`, `SUB_DET_*`.
- **HRD/penggajian:** `DET_GAJI_PEG`, `JNS_GAJI`, `DET_JNS_GAJI`, `ABSENSI`, `DET_ABSENSI`, `JNS_ABSEN`, `LIBUR_NAS`, `LIBUR_PEG`, `DEPT`, `JNS_PEKERJAAN`.
- **POS/kasir:** `REG_BANK*`, `REG_CH_GB*`, `KK_SIMPLE`, `KM_SIMPLE`, `REK_KAS_KASIR`, `DEPOSIT*`, `EKSPEDISI*`.
- **Kredit/pinjaman pegawai:** `DET_KRT_BELI`, `DET_KRT_HUTANG`, `DET_KRT_PINJAMAN`, `DET_KRT_SIMPANAN`, `DET_KRT_STOK`, `VW_PEG_PINJ`, `VW_PEG_SIMP`.
- **~50 tabel `*_TMP`** — staging input form desktop (mis. `DET_JUAL_TMP`, `MST_JUAL_TMP`).
- **~25 tabel "sampah"/personal:** `COBA`, `ARINDA`, `DARMAN`, `DAVID`, `DEWI`, `DITA`, `DWI`, `ELLYDA`, `HENIS`, `HERNI`, `HERNI3`, `HUDA`, `IMA`, `LAILI`, `PRIMA`, `RIA`, `SALVI`, `SOFI`, `SONI`, `SUSI`, `SYSDBA`, `WAWAN`, `YUNITA`, `BIMBIN` — semua 0 baris, kemungkinan hasil eksperimen/tabel temporary per-user.

---

## 7. Catatan Teknis Firebird 2.5 (yang harus diperhatikan saat coding)

1. **Firebird 2.5 tidak mendukung** `character_length` sebagai alias dan tidak punya kolom `RDB$CONST_NAME_UQ` / `RDB$GENERATOR_VALUE` → query metadata harus pakai cara alternatif (lihat `storage/app/db_inspect.php`).
2. **Limitasi baris:** pakai `SELECT FIRST n ... SKIP m`, **bukan** `LIMIT`/`OFFSET`.
3. **Tanpa `AUTO_INCREMENT`:** nomor dokumen dari **generator** + **trigger** (mis. `DET_JUAL_NOMOR_GEN = 9116`). Insert dari Laravel harus sadar trigger.
4. **`VARCHAR` legacy** → selalu cek `DB_CHARSET=UTF8` dan konversi di sisi aplikasi.
5. **Identifier case:** Firebird mengembalikan nama kolom UPPERCASE. Kolom yang didefinisikan tanpa quote menjadi UPPERCASE.
6. **Hanya 4 FK** → tidak bisa mengandalkan constraint DB; validasi relasi harus di aplikasi.
7. **Kolom `GUDANG` di `VW_STOK_BARANG_BRG` bertipe `CHAR(0)`** → jangan dipakai sebagai key.
8. **BLOB** dipakai untuk `KETERANGAN`, `GAMBAR`, `RDB$VIEW_SOURCE` → perlu handling khusus saat select.

---

## 8. Rekomendasi Arsitektur

### Opsi A — **DB Terpisah (paling direkomendasikan)**

```
Laravel (MySQL)                 Firebird Legacy (READ-ONLY)
├── users                       ├── MST_PENGGUNA / MST_OTORITAS  (sumber role)
├── sessions, cache, jobs       ├── CUSTOMER, BARANG, GUDANG
├── sales_visits  (baru)        ├── MST_ORD_JUAL, DET_ORD_JUAL, SO_MOB
├── payments + approval (baru)  ├── MST_JUAL, DET_JUAL, VW_PIUTANG
└── sinkronisasi / API ────────►└── DET_KRT_PIUTANG, TRANS_HARIAN
```

**Alasan:**
- Firebird legacy dipakai aplikasi desktop → **jangan di-`migrate`**.
- 392 trigger + 75 generator = risiko tinggi kalau di-insert dari luar.
- Tabel `users`/`sessions`/`cache`/`jobs` tidak boleh ditaruh di DB legacy (tidak ada + mengganggu).

### Opsi B — Firebird sebagai satu-satunya DB

Hanya jika keputusan manajemen: aplikasi desktop **dimatikan**.
Tetap perlu: buat tabel `users`, `sessions`, `cache` di Firebird + tangani generator/trigger manual.

### Langkah Selanjutnya yang Disarankan

1. **Konfirmasi ke pemilik sistem** algoritma `MST_PENGGUNA.KATAKUNCI` (kalau ingin SSO dengan aplikasi desktop) — nilai order sudah terverifikasi, tidak perlu dikonfirmasi.
2. **Konfirmasi** apakah penulisan order dari web boleh langsung insert ke Firebird (memperhatikan trigger/generator) atau lewat sinkronisasi.
3. **Tentukan arah sinkronisasi**: read-only legacy → web, atau dua arah.
4. **Buat Repository/Service layer** di Laravel (`app/Repositories/Firebird/`) yang memetakan tabel legacy ke kontrak aplikasi — jangan pakai model Eloquent langsung ke tabel legacy.
5. **Snapshot skema** sudah tersimpan di `storage/app/db_report.txt` (19.253 baris) untuk referensi.

---

## 9. Artefak Hasil Analisis

| File | Isi |
|---|---|
| `storage/app/db_report.txt` | Laporan lengkap: 340 tabel, 210 view, 10.820 kolom, PK, FK, index, 75 generator, 392 trigger, definisi view, jumlah baris |
| `storage/app/db_nonzero.txt` | 72 tabel yang berisi data (268 sisanya kosong) |
| `storage/app/db_analyze2_out.txt` | Analisis `SO_MOB`, user, order, faktur, master, view |
| `storage/app/db_analyze3_out.txt` | Analisis hak akses legacy + validasi relasi kunci |
| `storage/app/db_analyze4_out.txt` | Verifikasi nilai order: header vs detail, arti `TOTAL`/`SUB_TOTAL` |
| `storage/app/db_analyze5_out.txt` | Verifikasi konversi satuan: `JML_TUR = JUMLAH × KAPASITAS`, master satuan 24 SKU |
| `storage/app/db_analyze6_out.txt` | Kardinalitas `SO_MOB`, koreksi arti `NO_ENT`/`NO_ENT_ORD`, hitung kunjungan riil |
| `storage/app/db_analyze7_out.txt` | **Cakupan periode & pola closing bulanan** (`DET_JUAL` hanya bulan berjalan) |
| `storage/app/db_analyze8_out.txt` | **Verifikasi nilai `TOTAL`/`NETTO` vs Σ detail** di 3 level + bukti arsip `MST_JUAL_PIUTANG` |
| `storage/app/db_verify_examples_out.txt` | Uji 9 contoh query dokumen → **9 berhasil, 0 gagal** |
| `storage/app/db_inspect.php` | Script inspeksi skema (bisa dijalankan ulang: `php storage/app/db_inspect.php`) |
| `storage/app/db_analyze2.php` .. `db_analyze8.php` | Script analisis lanjutan (jalankan: `php storage/app/db_analyzeN.php`) |
| `storage/app/db_verify_examples.php` | Script uji contoh query |

---

## 10. Contoh Query Siap Pakai (Laravel + Firebird)

Karena tabel legacy bukan milik aplikasi web, sebaiknya **jangan** pakai Eloquent model langsung.
Gunakan query builder ke koneksi `firebird`:

```php
use Illuminate\Support\Facades\DB;

$db = DB::connection('firebird');

// 1) Order milik 1 sales + nama pelanggan (Firebird 2.5: FIRST/SKIP, bukan LIMIT/OFFSET)
$orders = $db->select("
    SELECT FIRST 50
           o.NO_ENT, o.TANGGAL, o.KD_CUST, c.NM_CUST, c.ALM_CUST,
           o.TOTAL, o.ST_JADI, o.JNS_BYR, o.TOP
    FROM MST_ORD_JUAL o
    LEFT JOIN CUSTOMER c ON c.KD_CUST = o.KD_CUST
    WHERE o.KD_PEG = ?
    ORDER BY o.TANGGAL DESC
", [$kdPeg]);

// 2) Detail order (SUB_TOTAL = nilai yang benar; TOTAL & SAK selalu 0)
$items = $db->select("
    SELECT d.NMR, d.KD_BRG, d.NM_BRG, d.SATUAN, d.JUMLAH, d.HARGA,
           d.DISC1, d.DISC2, d.DISC_RP, d.SUB_TOTAL, d.JML_TUR
    FROM DET_ORD_JUAL d
    WHERE d.NO_ENT = ?
    ORDER BY d.NMR
", [$noEnt]);

// 3) Tagihan / piutang berjalan (view sudah menghitung sisa & jatuh tempo)
$piutang = $db->select("
    SELECT FIRST 100
           p.NO_ENT, p.TANGGAL, p.KD_CUST, p.NM_CUST, p.NETTO,
           p.U_MUKA, p.JML_BAYAR, p.JML_KUPON, p.SISA_PIUTANG, p.TGL_JATUH_TEMPO
    FROM VW_PIUTANG p
    WHERE p.KD_CUST = ?
      AND p.SISA_PIUTANG > 0
    ORDER BY p.TGL_JATUH_TEMPO
", [$kdCust]);

// 4) Stok per gudang (stok riil ada di MUTASI_BARANG, bukan BARANG.STOK)
$stok = $db->select("
    SELECT m.GUDANG, m.KD_BRG, b.NM_BRG, m.QTY_AKHIR, m.RP_AKHIR, b.SATUAN2, b.KAPASITAS2
    FROM MUTASI_BARANG m
    JOIN BARANG b ON b.KD_BRG = m.KD_BRG
    WHERE m.GUDANG = ?
", [$gudang]);

// 5) Role/hak akses dari sistem legacy
$role = $db->selectOne("
    SELECT u.NO_USER, u.NM_USER, u.NO_OTOR, o.NM_OTOR
    FROM MST_PENGGUNA u
    LEFT JOIN MST_OTORITAS o ON o.NO_OTOR = u.NO_OTOR
    WHERE u.NM_USER = ?
", [$username]);

// 6) Kunjungan sales dari order mobile
//    PENTING: SO_MOB.NO_ENT & NO_ENT_ORD adalah ID BATCH, bukan nomor order.
//    1 kunjungan = kombinasi unik (KD_PEG, KD_CUST, TANGGAL).
$kunjungan = $db->select("
    SELECT m.TANGGAL, m.KD_CUST, c.NM_CUST, c.ALM_CUST,
           SUM(m.SUB_TOTAL) AS NILAI_ORDER, COUNT(*) AS JML_ITEM,
           MIN(m.LATITUDE) AS LAT, MIN(m.LONGITUDE) AS LON
    FROM SO_MOB m
    LEFT JOIN CUSTOMER c ON c.KD_CUST = m.KD_CUST
    WHERE m.KD_PEG = ? AND m.TANGGAL >= ?
    GROUP BY m.TANGGAL, m.KD_CUST, c.NM_CUST, c.ALM_CUST
    ORDER BY m.TANGGAL DESC
", [$kdPeg, $dariTanggal]);

// 7) Menghubungkan order mobile ke order desktop (kunci silang!)
$tautan = $db->select("
    SELECT FIRST 50
           o.NO_ENT AS NO_ORDER, o.NO_ENT_ORD AS ID_BATCH, o.TANGGAL,
           o.KD_CUST, o.TOTAL, o.ST_JADI
    FROM MST_ORD_JUAL o
    WHERE o.NO_ENT_ORD = ?          -- isi dengan SO_MOB.NO_ENT
    ORDER BY o.NO_ENT
", [$soMobNoEnt]);
```

**Catatan penting saat query:**
- Semua nama kolom di Firebird di-*return* **UPPERCASE** → normalisasi dengan
  `array_change_key_case($row, CASE_LOWER)` (pola yang sudah dipakai di `storage/app/db_*.php`).
- Untuk tanggal, bandingkan dengan `CAST(? AS DATE)` bila parameter berupa string.
- Jangan `SELECT *` pada tabel besar (`MST_JUAL` 99.809 baris, `DET_KRT_PIUTANG` 99.929, `DET_TRANSF_PIUTANG` 101.970) — selalu pakai filter + `FIRST n`.

---

*Dokumen ini dihasilkan dari inspeksi langsung database, bukan dari asumsi atau file migration.*
