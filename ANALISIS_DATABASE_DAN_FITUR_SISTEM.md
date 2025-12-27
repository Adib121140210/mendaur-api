# 📊 ANALISIS DATABASE DAN FITUR SISTEM MENDAUR
**Tugas Akhir Project - Sistem Bank Sampah**  
**Generated:** 24 Desember 2025

---

## 📑 DAFTAR ISI
1. [Ringkasan Tabel Database](#ringkasan-tabel-database)
2. [Analisis Per Tabel dengan Fitur](#analisis-per-tabel-dengan-fitur)
3. [Mapping Fitur ke Multiple Tables](#mapping-fitur-ke-multiple-tables)
4. [Diagram Relasi Antar Tabel](#diagram-relasi-antar-tabel)

---

## 📊 RINGKASAN TABEL DATABASE

| No | Nama Tabel | Jumlah Kolom | Primary Key | Fungsi Utama |
|----|------------|--------------|-------------|--------------|
| 1 | `users` | 19 | `user_id` | Data pengguna sistem (nasabah & admin) |
| 2 | `roles` | 5 | `role_id` | Manajemen role & hak akses |
| 3 | `badges` | 9 | `badge_id` | Badge/pencapaian yang bisa didapat user |
| 4 | `user_badges` | 6 | `user_badge_id` | Relasi user dengan badge yang dimiliki |
| 5 | `produks` | 9 | `produk_id` | Katalog produk untuk penukaran poin |
| 6 | `artikels` | 10 | `artikel_id` | Artikel edukasi lingkungan |
| 7 | `jadwal_penyetorans` | 8 | `jadwal_penyetoran_id` | Jadwal operasional bank sampah |
| 8 | `kategori_sampah` | 7 | `kategori_sampah_id` | Kategori sampah (Plastik, Kertas, dll) |
| 9 | `jenis_sampah` | 8 | `jenis_sampah_id` | Jenis spesifik sampah & harganya |
| 10 | `tabung_sampah` | 12 | `tabung_sampah_id` | Transaksi penyetoran sampah |
| 11 | `penukaran_produk` | 12 | `penukaran_produk_id` | Transaksi penukaran poin dengan produk |
| 12 | `penarikan_tunai` | 12 | `penarikan_tunai_id` | Transaksi penarikan poin ke uang tunai |
| 13 | `notifikasi` | 9 | `notifikasi_id` | Notifikasi untuk user |
| 14 | `poin_transaksis` | 13 | `poin_transaksi_id` | History transaksi poin (debit/kredit) |

**Total: 14 Tabel**

---

# 📋 ANALISIS PER TABEL DENGAN FITUR

## 1️⃣ TABEL: `users`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Login & Authentication** | `email`, `password` | Autentikasi user saat login |
| **Registrasi User** | `nama`, `email`, `password`, `no_hp`, `alamat` | Input data saat registrasi |
| **Profil User** | `nama`, `email`, `no_hp`, `alamat`, `foto_profil` | Menampilkan & edit profil |
| **Dashboard Nasabah** | `total_poin`, `total_setor_sampah`, `level`, `tipe_nasabah` | Statistik user di dashboard |
| **Manajemen User (Admin)** | Semua kolom | CRUD user oleh admin |
| **Penarikan Tunai** | `nama_bank`, `nomor_rekening`, `atas_nama_rekening` | Info rekening untuk pencairan |
| **Role & Permission** | `role_id`, `status` | Kontrol akses berdasarkan role |
| **Gamifikasi** | `level`, `total_poin`, `total_setor_sampah` | Sistem leveling & leaderboard |

**Relasi:**
- → `roles` (via `role_id`)
- → `user_badges` (via `user_id`)
- → `tabung_sampah` (via `user_id`)
- → `penukaran_produk` (via `user_id`)
- → `penarikan_tunai` (via `user_id`)
- → `notifikasi` (via `user_id`)
- → `poin_transaksis` (via `user_id`)

---

## 2️⃣ TABEL: `roles`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Role-Based Access Control (RBAC)** | `nama_role`, `level_akses` | Menentukan hak akses user |
| **Admin Panel Access** | `nama_role`, `level_akses` | Kontrol siapa bisa akses admin panel |
| **Manajemen Role** | Semua kolom | CRUD role oleh superadmin |

**Relasi:**
- ← `users` (via `role_id`)

**Enum Values:**
- `nama_role`: `superadmin`, `admin`, `nasabah`

---

## 3️⃣ TABEL: `badges`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Sistem Pencapaian (Achievement)** | `nama`, `deskripsi`, `icon` | Display badge di profil |
| **Gamifikasi** | `syarat_poin`, `syarat_setor`, `reward_poin` | Kriteria mendapat badge |
| **Manajemen Badge (Admin)** | Semua kolom | CRUD badge oleh admin |
| **Notifikasi Badge** | `nama`, `reward_poin` | Notif saat user dapat badge baru |

**Relasi:**
- → `user_badges` (via `badge_id`)

**Tipe Badge:**
- `setor` - Berdasarkan jumlah penyetoran
- `poin` - Berdasarkan total poin
- `ranking` - Berdasarkan peringkat

---

## 4️⃣ TABEL: `user_badges`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Koleksi Badge User** | `user_id`, `badge_id`, `tanggal_dapat` | Badge yang dimiliki user |
| **Reward Claim** | `reward_claimed` | Status apakah reward sudah diambil |
| **History Achievement** | `tanggal_dapat` | Tanggal mendapat badge |

**Relasi:**
- ← `users` (via `user_id`)
- ← `badges` (via `badge_id`)

---

## 5️⃣ TABEL: `produks`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Katalog Produk** | `nama`, `deskripsi`, `harga_poin`, `foto`, `kategori` | Display produk di marketplace |
| **Penukaran Poin** | `harga_poin`, `stok` | Validasi saat tukar poin |
| **Manajemen Produk (Admin)** | Semua kolom | CRUD produk oleh admin |
| **Filter & Search Produk** | `kategori`, `status` | Filter produk berdasarkan kategori |
| **Manajemen Stok** | `stok`, `status` | Update stok saat penukaran |

**Relasi:**
- → `penukaran_produk` (via `produk_id`)

**Status Produk:**
- `tersedia` - Produk ready stock
- `habis` - Stok habis
- `nonaktif` - Produk dihentikan

---

## 6️⃣ TABEL: `artikels`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Blog/Artikel Edukasi** | `judul`, `konten`, `foto_cover`, `slug` | Konten artikel di website |
| **Manajemen Artikel (Admin)** | Semua kolom | CRUD artikel oleh admin |
| **SEO & URL Friendly** | `slug` | URL artikel yang SEO-friendly |
| **Tracking Views** | `views` | Jumlah views artikel |
| **Filter Kategori** | `kategori` | Filter artikel berdasarkan kategori |
| **Info Penulis** | `penulis`, `tanggal_publikasi` | Metadata artikel |

**Fitur Display:**
- Landing page artikel
- Detail artikel
- Related articles
- Popular articles (berdasarkan views)

---

## 7️⃣ TABEL: `jadwal_penyetorans`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Jadwal Operasional** | `tanggal`, `waktu_mulai`, `waktu_selesai`, `lokasi` | Info jadwal buka bank sampah |
| **Booking Penyetoran** | `tanggal`, `kapasitas` | User pilih jadwal saat setor |
| **Manajemen Jadwal (Admin)** | Semua kolom | CRUD jadwal oleh admin |
| **Status Jadwal** | `status` | Jadwal buka/tutup |

**Relasi:**
- → `tabung_sampah` (via `jadwal_penyetoran_id`)

**Status:**
- `buka` - Jadwal aktif
- `tutup` - Jadwal tidak aktif

---

## 8️⃣ TABEL: `kategori_sampah`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Master Data Kategori** | `nama_kategori`, `deskripsi` | Kategori utama sampah |
| **UI Display** | `icon`, `warna` | Icon & warna untuk UI |
| **Manajemen Kategori (Admin)** | Semua kolom | CRUD kategori oleh admin |
| **Filter Jenis Sampah** | `kategori_sampah_id` | Filter jenis sampah per kategori |

**Relasi:**
- → `jenis_sampah` (via `kategori_sampah_id`)

**Contoh Kategori:**
- Plastik 🔵
- Kertas 📄
- Logam 🔩
- Organik 🌿
- Kaca 🪟

---

## 9️⃣ TABEL: `jenis_sampah`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Master Data Jenis Sampah** | `nama_jenis`, `harga_per_kg` | Daftar jenis & harga sampah |
| **Kalkulasi Poin** | `harga_per_kg` | Hitung poin dari berat sampah |
| **Form Penyetoran** | `nama_jenis`, `kategori_sampah_id` | Dropdown pilihan jenis sampah |
| **Manajemen Jenis Sampah (Admin)** | Semua kolom | CRUD jenis sampah oleh admin |
| **Kode Unik** | `kode` | Kode identifikasi jenis sampah |

**Relasi:**
- ← `kategori_sampah` (via `kategori_sampah_id`)

**Contoh Data:**
- Plastik HDPE - Rp 3000/kg
- Kertas HVS - Rp 2000/kg
- Botol Kaca - Rp 1500/kg

---

## 🔟 TABEL: `tabung_sampah`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Form Penyetoran Sampah** | `nama_lengkap`, `no_hp`, `titik_lokasi`, `jenis_sampah`, `berat_kg`, `foto_sampah` | Input data penyetoran |
| **Approval Workflow** | `status`, `poin_didapat` | Admin approve/reject |
| **History Penyetoran** | Semua kolom | Riwayat penyetoran user |
| **Dashboard Nasabah** | `berat_kg`, `poin_didapat`, `status` | Statistik penyetoran |
| **Manajemen Penyetoran (Admin)** | Semua kolom | Admin kelola penyetoran |
| **Kalkulasi Poin** | `berat_kg`, `jenis_sampah`, `poin_didapat` | Hitung poin berdasarkan berat |
| **Tracking Lokasi** | `titik_lokasi` | Koordinat lokasi penyetoran |
| **Verifikasi Foto** | `foto_sampah` | Admin verifikasi dari foto |

**Relasi:**
- ← `users` (via `user_id`)
- ← `jadwal_penyetorans` (via `jadwal_penyetoran_id`)
- → `poin_transaksis` (via `tabung_sampah_id`)

**Status Flow:**
- `pending` → User submit penyetoran
- `approved` → Admin setujui, poin masuk
- `rejected` → Admin tolak, alasan diberikan

---

## 1️⃣1️⃣ TABEL: `penukaran_produk`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Tukar Poin ke Produk** | `user_id`, `produk_id`, `jumlah`, `poin_digunakan` | Transaksi penukaran |
| **Metode Pengambilan** | `metode_ambil` | Ambil ditempat / kirim |
| **Approval Workflow** | `status` | Admin proses penukaran |
| **History Penukaran** | Semua kolom | Riwayat penukaran user |
| **Manajemen Penukaran (Admin)** | Semua kolom | Admin kelola penukaran |
| **Catatan Transaksi** | `catatan`, `tanggal_diambil` | Info tambahan transaksi |

**Relasi:**
- ← `users` (via `user_id`)
- ← `produks` (via `produk_id`)
- → `poin_transaksis` (via reference)

**Status Flow:**
- `pending` → User submit penukaran
- `approved` → Admin approve, stok dikurangi
- `rejected` → Admin reject, poin dikembalikan
- `completed` → Produk sudah diambil user

**Metode Ambil:**
- `ambil_ditempat` - User ambil langsung
- `dikirim` - Dikirim ke alamat

---

## 1️⃣2️⃣ TABEL: `penarikan_tunai`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Tarik Poin ke Uang** | `jumlah_poin`, `jumlah_rupiah` | Konversi poin ke rupiah |
| **Info Rekening** | `nomor_rekening`, `nama_bank`, `nama_penerima` | Data transfer |
| **Approval Workflow** | `status`, `catatan_admin` | Admin proses penarikan |
| **History Penarikan** | Semua kolom | Riwayat penarikan user |
| **Manajemen Penarikan (Admin)** | Semua kolom | Admin kelola penarikan |
| **Tracking Admin** | `processed_by`, `processed_at` | Admin yang memproses |

**Relasi:**
- ← `users` (via `user_id`)
- → `poin_transaksis` (via reference)

**Status Flow:**
- `pending` → User submit penarikan
- `approved` → Admin transfer, poin dikurangi
- `rejected` → Admin reject, poin tetap

---

## 1️⃣3️⃣ TABEL: `notifikasi`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **Push Notification** | `judul`, `pesan`, `tipe` | Notif real-time ke user |
| **Notif Penyetoran** | `related_id`, `related_type` | Link ke penyetoran |
| **Notif Penukaran** | `related_id`, `related_type` | Link ke penukaran |
| **Notif Badge** | `related_id`, `related_type` | Link ke badge baru |
| **Status Baca** | `is_read` | Track notif sudah dibaca |
| **Broadcast Notif (Admin)** | Semua kolom | Admin kirim notif ke user |

**Relasi:**
- ← `users` (via `user_id`)

**Tipe Notifikasi:**
- Penyetoran approved/rejected
- Penukaran approved/rejected
- Penarikan approved/rejected
- Badge baru didapat
- Artikel baru
- Pengumuman sistem

---

## 1️⃣4️⃣ TABEL: `poin_transaksis`

| **Fitur** | **Kolom yang Digunakan** | **Deskripsi** |
|-----------|-------------------------|---------------|
| **History Poin** | Semua kolom | Riwayat semua transaksi poin |
| **Poin dari Penyetoran** | `tabung_sampah_id`, `jenis_sampah`, `berat_kg`, `poin_didapat` | Record poin masuk |
| **Poin Keluar (Tukar/Tarik)** | `poin_didapat` (negatif), `sumber` | Record poin keluar |
| **Sumber Transaksi** | `sumber`, `keterangan` | Kategori transaksi |
| **Usable Points Logic** | `is_usable`, `reason_not_usable` | Poin bisa dipakai atau tidak |
| **Referensi Transaksi** | `referensi_id`, `referensi_tipe` | Link ke transaksi asal |

**Relasi:**
- ← `users` (via `user_id`)
- ← `tabung_sampah` (via `tabung_sampah_id`)

**Sumber Poin:**
- `setor` - Dari penyetoran sampah
- `bonus` - Bonus dari badge/event
- `tukar` - Penukaran produk (negatif)
- `tarik` - Penarikan tunai (negatif)

**Contoh Record:**
```
+50 poin  | setor   | Penyetoran Plastik 5kg
+10 poin  | bonus   | Badge Eco Warrior
-100 poin | tukar   | Penukaran Produk X
-500 poin | tarik   | Penarikan Tunai Rp 50,000
```

---

# 🔗 MAPPING FITUR KE MULTIPLE TABLES

## Fitur 1: **PENYETORAN SAMPAH**

| Tabel | Kolom | Fungsi |
|-------|-------|--------|
| `users` | `user_id`, `nama`, `total_poin`, `total_setor_sampah` | Data user yang setor |
| `jadwal_penyetorans` | `jadwal_penyetoran_id`, `tanggal`, `lokasi` | Jadwal yang dipilih |
| `jenis_sampah` | `nama_jenis`, `harga_per_kg` | Jenis & harga sampah |
| `kategori_sampah` | `nama_kategori` | Kategori sampah |
| `tabung_sampah` | Semua kolom | Data penyetoran utama |
| `poin_transaksis` | `poin_didapat`, `sumber`, `tabung_sampah_id` | Record transaksi poin |
| `notifikasi` | `judul`, `pesan` | Notif approval/reject |

**Flow:**
1. User pilih jadwal → `jadwal_penyetorans`
2. User input data → `tabung_sampah` (status: pending)
3. Admin approve → Update `tabung_sampah` (status: approved)
4. Sistem create record → `poin_transaksis`
5. Update total user → `users` (total_poin, total_setor_sampah)
6. Kirim notifikasi → `notifikasi`

---

## Fitur 2: **PENUKARAN PRODUK**

| Tabel | Kolom | Fungsi |
|-------|-------|--------|
| `users` | `user_id`, `total_poin` | User yang tukar |
| `produks` | `produk_id`, `nama`, `harga_poin`, `stok` | Produk yang ditukar |
| `penukaran_produk` | Semua kolom | Data penukaran utama |
| `poin_transaksis` | `poin_didapat` (negatif), `sumber` | Record poin keluar |
| `notifikasi` | `judul`, `pesan` | Notif penukaran |

**Flow:**
1. User pilih produk → `produks`
2. Validasi poin & stok
3. Create record → `penukaran_produk` (status: pending)
4. Admin approve → Update status
5. Kurangi stok → `produks`
6. Kurangi poin → `users`
7. Create record → `poin_transaksis` (negatif)
8. Kirim notifikasi → `notifikasi`

---

## Fitur 3: **PENARIKAN TUNAI**

| Tabel | Kolom | Fungsi |
|-------|-------|--------|
| `users` | `user_id`, `total_poin`, `nama_bank`, `nomor_rekening` | User & rekening |
| `penarikan_tunai` | Semua kolom | Data penarikan |
| `poin_transaksis` | `poin_didapat` (negatif), `sumber` | Record poin keluar |
| `notifikasi` | `judul`, `pesan` | Notif penarikan |

**Flow:**
1. User input jumlah → `penarikan_tunai` (status: pending)
2. Admin approve → Transfer uang
3. Update status → `penarikan_tunai`
4. Kurangi poin → `users`
5. Create record → `poin_transaksis` (negatif)
6. Kirim notifikasi → `notifikasi`

---

## Fitur 4: **SISTEM BADGE & GAMIFIKASI**

| Tabel | Kolom | Fungsi |
|-------|-------|--------|
| `users` | `total_poin`, `total_setor_sampah`, `level` | Progress user |
| `badges` | Semua kolom | Master data badge |
| `user_badges` | `user_id`, `badge_id`, `reward_claimed` | Badge user |
| `poin_transaksis` | `poin_didapat`, `sumber` | Bonus poin dari badge |
| `notifikasi` | `judul`, `pesan` | Notif badge baru |

**Flow:**
1. User mencapai syarat badge
2. Sistem cek → `badges` (syarat_poin, syarat_setor)
3. Assign badge → `user_badges`
4. Give reward → `poin_transaksis` (bonus)
5. Update poin → `users`
6. Kirim notifikasi → `notifikasi`

---

## Fitur 5: **DASHBOARD & ANALYTICS**

### Dashboard Nasabah
| Tabel | Data yang Ditampilkan |
|-------|----------------------|
| `users` | Total poin, level, setor sampah |
| `tabung_sampah` | History & statistik penyetoran |
| `poin_transaksis` | Grafik poin masuk/keluar |
| `user_badges` | Badge yang dimiliki |
| `penukaran_produk` | History penukaran |
| `penarikan_tunai` | History penarikan |

### Dashboard Admin
| Tabel | Data yang Ditampilkan |
|-------|----------------------|
| `users` | Total users, leaderboard |
| `tabung_sampah` | Statistik penyetoran (pending, approved, rejected) |
| `penukaran_produk` | Statistik penukaran |
| `penarikan_tunai` | Statistik penarikan |
| `produks` | Stok produk |
| `artikels` | Statistik artikel |

---

# 🗺️ DIAGRAM RELASI ANTAR TABEL

```
┌─────────────────┐
│     USERS       │
│  (user_id) PK   │──┐
└─────────────────┘  │
         │           │
         │ 1:N       │
         ▼           │
┌─────────────────┐  │
│  USER_BADGES    │  │
│(user_badge_id)PK│  │
│  user_id FK     │  │
│  badge_id FK    │  │
└─────────────────┘  │
         │           │
         │ N:1       │
         ▼           │
┌─────────────────┐  │
│    BADGES       │  │
│  (badge_id) PK  │  │
└─────────────────┘  │
                     │
                     │ 1:N
                     ▼
         ┌─────────────────────┐
         │  TABUNG_SAMPAH      │
         │(tabung_sampah_id)PK │
         │   user_id FK        │
         │jadwal_penyetoran_id │
         └─────────────────────┘
                     │
                     │ N:1
                     ▼
         ┌─────────────────────┐
         │ JADWAL_PENYETORANS  │
         │(jadwal_penyetoran_id│
         └─────────────────────┘

┌─────────────────┐
│ KATEGORI_SAMPAH │
│(kategori_sampah │
│   _id) PK       │
└─────────────────┘
         │ 1:N
         ▼
┌─────────────────┐
│  JENIS_SAMPAH   │
│(jenis_sampah_id)│
│kategori_sampah  │
│   _id FK        │
└─────────────────┘

┌─────────────────┐
│    PRODUKS      │
│  (produk_id) PK │
└─────────────────┘
         │ 1:N
         ▼
┌─────────────────┐
│PENUKARAN_PRODUK │
│(penukaran_produk│
│   _id) PK       │
│   user_id FK    │
│  produk_id FK   │
└─────────────────┘

┌─────────────────┐
│PENARIKAN_TUNAI  │
│(penarikan_tunai │
│   _id) PK       │
│   user_id FK    │
└─────────────────┘

┌─────────────────┐
│ POIN_TRANSAKSIS │
│(poin_transaksi  │
│   _id) PK       │
│   user_id FK    │
│tabung_sampah_id │
└─────────────────┘

┌─────────────────┐
│   NOTIFIKASI    │
│(notifikasi_id)PK│
│   user_id FK    │
└─────────────────┘

┌─────────────────┐
│    ARTIKELS     │
│ (artikel_id) PK │
└─────────────────┘

┌─────────────────┐
│     ROLES       │
│  (role_id) PK   │
└─────────────────┘
```

---

# 📊 SUMMARY STATISTIK

## Tabel dengan Relasi Terbanyak
1. **users** - Hub utama sistem (7 relasi)
2. **tabung_sampah** - Core transaksi (3 relasi)
3. **poin_transaksis** - History lengkap (2 relasi)

## Tabel Transaksi
- `tabung_sampah` (Penyetoran)
- `penukaran_produk` (Penukaran)
- `penarikan_tunai` (Penarikan)
- `poin_transaksis` (History)

## Tabel Master Data
- `roles`
- `badges`
- `produks`
- `artikels`
- `kategori_sampah`
- `jenis_sampah`
- `jadwal_penyetorans`

## Tabel Pivot/Junction
- `user_badges` (users ↔ badges)

---

# 🎯 KESIMPULAN

Sistem Mendaur memiliki **14 tabel** yang saling terintegrasi untuk mendukung:

1. ✅ **Manajemen User & RBAC** (users, roles)
2. ✅ **Transaksi Sampah** (tabung_sampah, jenis_sampah, kategori_sampah)
3. ✅ **Sistem Poin** (poin_transaksis)
4. ✅ **Reward System** (badges, user_badges, penukaran_produk)
5. ✅ **Cash Out** (penarikan_tunai)
6. ✅ **Content Management** (artikels)
7. ✅ **Scheduling** (jadwal_penyetorans)
8. ✅ **Communication** (notifikasi)

Semua tabel menggunakan **snake_case** naming convention dan terintegrasi dengan baik untuk mendukung alur bisnis sistem bank sampah digital.

---

**End of Document**
