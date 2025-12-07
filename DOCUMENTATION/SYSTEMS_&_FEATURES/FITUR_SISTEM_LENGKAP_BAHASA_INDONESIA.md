# 📱 DAFTAR LENGKAP FITUR SISTEM MENDAUR - BAHASA INDONESIA

**Tanggal**: 29 November 2025  
**Versi**: 2.0 (Updated dengan Penarikan Tunai & Artikel)  
**Total Fitur**: 43+ Use Cases  
**Status**: LENGKAP & PRODUCTION-READY

---

## 📋 DAFTAR ISI
1. [Fitur Nasabah](#-fitur-nasabah-pengguna-biasa-13-uc)
2. [Fitur Admin](#-fitur-admin-operator-17-uc)
3. [Fitur Superadmin](#-fitur-superadmin-manajer-sistem-13-uc)
4. [Proses Otomatis Sistem](#-proses-otomatis-sistem-5-uc)
5. [Kategori Fitur](#-kategori-fitur-per-bisnis)

---

## 👤 FITUR NASABAH (Pengguna Biasa) - 13 UC

Fitur yang tersedia untuk pengguna regular (Nasabah) dalam sistem Mendaur.

### 1. **MANAJEMEN AKUN & AUTENTIKASI** (3 Fitur)

#### 🔐 Daftar Akun Baru (Register)
- **Kode UC**: UC-101
- **Deskripsi**: Pengguna baru dapat mendaftar akun di sistem Mendaur
- **Cara Kerja**:
  1. Klik tombol "Daftar"
  2. Isi form: email, nomor HP, nama lengkap, password
  3. Verifikasi email/SMS
  4. Akun siap digunakan
- **Syarat**: Tidak ada akun sebelumnya, email/HP unik
- **Hasil**: Akun baru dibuat, role = "Nasabah", total_poin = 0
- **Tabel Terkait**: `users`, `roles`

#### 🔑 Login ke Sistem
- **Kode UC**: UC-102
- **Deskripsi**: Masuk ke sistem menggunakan email/HP dan password
- **Cara Kerja**:
  1. Masukkan email atau nomor HP
  2. Masukkan password
  3. Klik "Login"
  4. Sistem validasi kredensial
  5. Masuk ke dashboard personal
- **Syarat**: Akun sudah terdaftar
- **Hasil**: Session aktif, user dapat mengakses fitur
- **Tabel Terkait**: `users`

#### 👤 Lihat & Update Profil
- **Kode UC**: UC-103 & UC-104
- **Deskripsi**: Melihat dan mengubah data profil pribadi
- **Data Profil Tersedia**:
  - Nama lengkap
  - Email
  - Nomor HP (tidak bisa diubah - business key)
  - Alamat lengkap
  - Foto profil
  - Tipe nasabah (konvensional/modern)
  - Data rekening (untuk modern nasabah)
- **Cara Update**:
  1. Buka menu "Profil"
  2. Klik "Edit"
  3. Ubah data yang diperlukan
  4. Upload foto profil (optional)
  5. Simpan perubahan
- **Tabel Terkait**: `users`

---

### 2. **MANAJEMEN SAMPAH** (4 Fitur)

#### 🗑️ Setor Sampah Baru
- **Kode UC**: UC-203
- **Deskripsi**: User mengirim sampah untuk didaur ulang dan dapatkan poin
- **Langkah-Langkah**:
  1. Buka menu "Setor Sampah"
  2. Pilih kategori sampah (plastik, kertas, logam, dll)
  3. Pilih jenis sampah spesifik
  4. Masukkan berat (kg) - estimasi atau timbang manual
  5. Upload foto sampah (optional)
  6. Pilih lokasi penyerahan/jadwal
  7. Klik "Setor Sekarang" atau "Jadwalkan"
  8. Sistem membuat record dengan status "pending"
  9. Admin akan verifikasi & approve
- **Poin yang Didapat**: Berat (kg) × Harga per kg jenis sampah
  - Contoh: 2 kg plastik @ Rp 500/kg = 1000 poin
- **Status**: pending → approved → selesai
- **Tabel Terkait**: `tabung_sampah`, `jenis_sampah`, `poin_transaksis`

#### 📜 Lihat Riwayat Setor Sampah
- **Kode UC**: UC-204
- **Deskripsi**: Melihat semua setor sampah yang pernah dilakukan
- **Informasi Ditampilkan**:
  - Tanggal & waktu setor
  - Jenis & berat sampah
  - Poin yang didapat
  - Status (pending/approved/rejected)
  - Catatan admin (jika ada)
- **Filter Tersedia**:
  - Berdasarkan tanggal
  - Berdasarkan jenis sampah
  - Berdasarkan status
  - Pencarian
- **Tabel Terkait**: `tabung_sampah`, `poin_transaksis`

#### 📍 Lihat Kategori & Jenis Sampah
- **Kode UC**: UC-201 & UC-202
- **Deskripsi**: Melihat daftar kategori dan jenis sampah yang bisa disetor
- **Informasi**:
  - Nama kategori (Plastik, Kertas, Logam, dll)
  - Icon & warna kategori
  - Jenis-jenis sampah dalam kategori
  - Harga per kg untuk setiap jenis
  - Deskripsi/petunjuk
- **Contoh Struktur**:
  ```
  📦 KATEGORI: PLASTIK (icon: 🗑️, warna: biru)
  ├─ PET Bottles (Rp 500/kg)
  ├─ HDPE (Rp 600/kg)
  └─ LDPE (Rp 400/kg)
  
  📄 KATEGORI: KERTAS (icon: 📄, warna: coklat)
  ├─ Koran (Rp 200/kg)
  ├─ Kemasan (Rp 300/kg)
  └─ Kertas putih (Rp 400/kg)
  ```
- **Tabel Terkait**: `kategori_sampah`, `jenis_sampah`

---

### 3. **MANAJEMEN POIN & REWARD** (3 Fitur)

#### 💰 Lihat Saldo Poin Total
- **Kode UC**: UC-501
- **Deskripsi**: Melihat total poin yang dimiliki
- **Informasi**:
  - Saldo poin total (prominent display)
  - Poin yang sudah tercatat (tidak terpakai)
  - Poin yang digunakan untuk tukar
  - Estimasi nilai rupiah
- **Tabel Terkait**: `users` (kolom `total_poin`)

#### 📊 Lihat Riwayat & Detail Poin
- **Kode UC**: UC-502 & UC-503
- **Deskripsi**: Melihat riwayat lengkap transaksi poin dengan detail
- **Informasi Per Transaksi**:
  - Tanggal & waktu
  - Sumber poin (setor sampah/tukar produk/badge/bonus/manual)
  - Jumlah poin (+/-)
  - Keterangan detail
  - Saldo setelah transaksi
- **Filter**:
  - Berdasarkan sumber (dropdown: semua/setor/tukar/badge/bonus)
  - Berdasarkan tanggal range
  - Poin (+) atau (-) atau semua
  - Export ke CSV
- **Contoh**:
  ```
  [2025-11-29] + 1000 poin [SETOR SAMPAH] - Setor 2kg plastik
  [2025-11-28] - 500 poin [TUKAR PRODUK] - Tukar pulsa 10rb
  [2025-11-27] + 250 poin [BADGE] - Unlock "Eco Warrior" badge
  Saldo: 2000 poin
  ```
- **Tabel Terkait**: `poin_transaksis`, `users`

#### 🏆 Lihat Leaderboard & Peringkat
- **Kode UC**: UC-504
- **Deskripsi**: Melihat leaderboard user berdasarkan total poin
- **Informasi**:
  - Ranking posisi user
  - Top 10 pengguna terbaik
  - Poin masing-masing user
  - Status badge mereka
- **Konteks Sosial**:
  - Lihat posisi relatif dengan teman/pengguna lain
  - Kompetisi sehat untuk mendorong partisipasi
- **Tabel Terkait**: `users`, `poin_transaksis`

---

### 4. **SISTEM GAMIFIKASI (BADGE)** (3 Fitur)

#### 🎖️ Lihat Daftar Badge Tersedia
- **Kode UC**: UC-601
- **Deskripsi**: Melihat semua badge yang bisa di-unlock
- **Informasi Per Badge**:
  - Nama badge
  - Icon/gambar badge
  - Deskripsi & cerita badge
  - Syarat unlock (berapa poin/setor atau kombinasi)
  - Reward poin yang didapat saat unlock
  - Status: belum unlock / sedang dikerjakan / sudah unlock
- **Tabel Terkait**: `badges`, `badge_progress`

#### ⏳ Lihat Progress Badge
- **Kode UC**: UC-602
- **Deskripsi**: Melihat progress untuk setiap badge yang sedang dikerjakan
- **Informasi**:
  - Progress bar (0% - 100%)
  - Target & current value
  - Sisa yang perlu dicapai
  - Estimasi waktu unlock
- **Contoh**:
  ```
  🌱 "Eco Starter" Badge
  Syarat: Setor 10 kg sampah
  Progress: ████████░░ 80% (8/10 kg setor)
  Tinggal: 2 kg lagi → Unlock!
  Reward: +250 poin
  ```
- **Tabel Terkait**: `badge_progress`, `badges`

#### ✨ Lihat Badge yang Sudah Unlock
- **Kode UC**: UC-604
- **Deskripsi**: Melihat semua badge yang sudah berhasil di-unlock
- **Informasi**:
  - Badge yang dimiliki
  - Tanggal unlock
  - Poin reward yang diterima
  - Opsi share ke social media
- **Social Features**:
  - "Bagikan achievement ke WhatsApp/Facebook"
  - Profil publik menampilkan badge
- **Tabel Terkait**: `user_badges`, `badges`

---

### 5. **TUKAR POIN & PRODUK** (1 Fitur - Plus dari Admin)

#### 🎁 Tukar Poin Dengan Produk
- **Kode UC**: UC-704
- **Deskripsi**: Menukar poin dengan produk nyata (pulsa, voucher, barang, dll)
- **Langkah-Langkah**:
  1. Buka menu "Tukar Poin"
  2. Lihat katalog produk
  3. Pilih produk yang diinginkan
  4. Verifikasi: "Gunakan XXX poin untuk produk ini?"
  5. Konfirmasi tukar
  6. Sistem buat record `penukaran_produk` dengan status "pending"
  7. Admin akan approve & delivery
- **Informasi Produk**:
  - Nama produk
  - Harga dalam poin
  - Deskripsi & spesifikasi
  - Stok tersedia
  - Gambar produk
  - Rating/review dari pengguna lain
- **Status Tukar**:
  - pending: menunggu approval admin
  - approved: admin sudah approve, siap diambil
  - cancelled: tukar dibatalkan
- **Tabel Terkait**: `penukaran_produk`, `produks`, `poin_transaksis`

#### 📜 Lihat Riwayat Tukar Poin
- **Kode UC**: UC-705
- **Deskripsi**: Melihat semua riwayat tukar poin
- **Informasi**:
  - Produk apa yang ditukar
  - Berapa poin digunakan
  - Tanggal tukar
  - Status (pending/approved/cancelled)
  - Tanggal diambil
- **Tabel Terkait**: `penukaran_produk`

---

### 6. **PENARIKAN TUNAI (CASH WITHDRAWAL)** (2 Fitur - BARU)

#### 💸 Request Penarikan Tunai
- **Kode UC**: UC-801
- **Deskripsi**: User request pencairan poin menjadi uang tunai
- **Langkah-Langkah**:
  1. Buka menu "Tarik Uang"
  2. Masukkan jumlah poin yang ingin dicairkan
  3. Sistem tampilkan konversi ke rupiah (1 poin = Rp X)
  4. Input data rekening:
     - Nama bank
     - Nomor rekening
     - Atas nama rekening
  5. Review & konfirmasi
  6. Submit request
  7. Status: pending menunggu approval admin
- **Syarat**:
  - Minimum withdrawal (misal 10.000 poin)
  - Nomor rekening sudah verified
  - Tipe nasabah = "modern" (konvensional tidak bisa tukar)
- **Fee/Biaya**:
  - Platform fee: XXX%
  - Biaya bank transfer: XXX
  - Total dikurangi dari poin
- **Tabel Terkait**: `penarikan_tunai`, `users`, `poin_transaksis`

#### 📈 Lihat Riwayat Penarikan Tunai
- **Kode UC**: UC-803
- **Deskripsi**: Melihat semua request penarikan tunai
- **Informasi**:
  - Tanggal request
  - Jumlah poin & rupiah
  - Data rekening (bank, nomor, nama penerima)
  - Status (pending/approved/rejected)
  - Tanggal di-process (jika sudah)
  - Catatan admin (jika ada)
- **Status Detail**:
  - pending: menunggu verifikasi admin
  - approved: disetujui, sedang proses transfer
  - processing: sedang ditransfer ke bank
  - completed: sudah masuk rekening
  - rejected: ditolak, alasan ditampilkan
- **Tabel Terkait**: `penarikan_tunai`

---

### 7. **CONTENT MANAGEMENT (ARTIKEL & TIPS)** (1 Fitur - BARU)

#### 📖 Baca Artikel & Tips
- **Kode UC**: UC-1001
- **Deskripsi**: Membaca artikel edukatif dari admin/sistem
- **Jenis Konten**:
  - Tips menghemat sampah
  - Panduan daur ulang
  - Berita & updates sistem
  - Tips meningkatkan poin
  - Cerita pengguna sukses
- **Interface**:
  - List artikel dengan preview
  - Search & filter artikel
  - Kategori artikel
  - Sorting: terbaru, terpopuler, rating
- **Fitur Engagement**:
  - Like/heart artikel
  - Share ke social media
  - Comment (jika enable)
- **Tabel Terkait**: `artikels`

---

## 👨‍💼 FITUR ADMIN (Operator) - 17 UC

Fitur untuk admin/operator yang mengelola approval dan operasional sistem.

### 1. **MANAJEMEN SETOR SAMPAH** (4 Fitur)

#### 📥 Lihat Setor Sampah Pending
- **Kode UC**: UC-301
- **Deskripsi**: Melihat list setor sampah yang menunggu approval
- **Informasi**:
  - Nama & kontak pengguna
  - Jenis & berat sampah
  - Foto sampah (jika ada)
  - Tanggal setor
  - Lokasi penyerahan
- **Status Filter**: Pending, Approved, Rejected
- **Tabel Terkait**: `tabung_sampah`, `users`

#### ✅ Approve/Reject Setor Sampah
- **Kode UC**: UC-303 & UC-304
- **Deskripsi**: Admin menerima atau menolak setor sampah
- **Proses Approve**:
  1. Verifikasi berat sampah yang disetor user
  2. Jika sesuai: klik "Approve"
  3. Sistem otomatis:
     - Update status → "approved"
     - Hitung poin (berat × harga/kg)
     - Buat record di `poin_transaksis`
     - Update `total_poin` user
     - Kirim notifikasi ke user
     - Log aktivitas
  4. Poin langsung masuk ke rekening user
- **Proses Reject**:
  1. Jika sampah tidak sesuai:
  2. Klik "Reject"
  3. Masukkan alasan penolakan
  4. Sistem:
     - Update status → "rejected"
     - Kirim notifikasi ke user dengan alasan
     - Log aktivitas
- **Tabel Terkait**: `tabung_sampah`, `poin_transaksis`, `users`, `notifikasi`

#### 🔍 Verifikasi Berat Sampah
- **Kode UC**: UC-305
- **Deskripsi**: Admin memverifikasi berat sampah yang diklaim user
- **Cara Kerja**:
  1. Lihat foto sampah yang di-upload
  2. Lihat estimasi berat dari user
  3. Timbang sampah secara manual/actual
  4. Update berat actual di sistem
  5. Hitung ulang poin berdasarkan berat actual
  6. Approve/Reject sesuai hasil verifikasi
- **Catatan**: Ini penting untuk mencegah fraud
- **Tabel Terkait**: `tabung_sampah`

---

### 2. **MANAJEMEN TUKAR PRODUK** (4 Fitur)

#### 📋 Lihat Tukar Produk Pending
- **Kode UC**: UC-708
- **Deskripsi**: Melihat list tukar produk yang menunggu approval
- **Informasi**:
  - Nama pengguna
  - Produk apa yang ditukar
  - Berapa poin digunakan
  - Status
- **Tabel Terkait**: `penukaran_produk`, `users`, `produks`

#### ✅ Approve/Reject Tukar Produk
- **Kode UC**: UC-709 & UC-710
- **Deskripsi**: Admin menerima atau menolak tukar produk
- **Proses Approve**:
  1. Verifikasi stok produk masih tersedia
  2. Klik "Approve"
  3. Sistem:
     - Update status → "approved"
     - Kurangi stok produk
     - Buat record poin_transaksis (source='tukar')
     - Kirim notifikasi ke user: "Silakan ambil produk"
- **Proses Reject**:
  1. Jika stok habis atau alasan lain
  2. Klik "Reject"
  3. Masukkan alasan
  4. Sistem:
     - Update status → "cancelled"
     - KEMBALIKAN poin ke user
     - Kirim notifikasi rejection
- **Tabel Terkait**: `penukaran_produk`, `produks`, `poin_transaksis`, `notifikasi`

#### 📍 Tandai Produk Sudah Diambil
- **Kode UC**: UC-711
- **Deskripsi**: Mencatat produk sudah diambil user
- **Cara Kerja**:
  1. Lihat list approved redemptions
  2. Pilih redemption yang sudah diambil
  3. Klik "Tandai Sudah Diambil"
  4. Input: tanggal ambil, catatan (optional)
  5. Status → "collected"
  6. Transaksi selesai
- **Tabel Terkait**: `penukaran_produk`

---

### 3. **MANAJEMEN PENARIKAN TUNAI (CASH WITHDRAWAL)** (3 Fitur - BARU)

#### 💸 Lihat Request Penarikan Tunai Pending
- **Kode UC**: UC-805
- **Deskripsi**: Melihat list request pencairan poin yang menunggu approval
- **Informasi**:
  - Nama pengguna
  - Jumlah poin & rupiah
  - Data rekening (bank, nomor, nama penerima)
  - Tanggal request
- **Tabel Terkait**: `penarikan_tunai`, `users`

#### ✅ Approve/Reject Penarikan Tunai
- **Kode UC**: UC-807 & UC-808
- **Deskripsi**: Admin menerima atau menolak request pencairan
- **Proses Approve**:
  1. Verifikasi data rekening valid
  2. Check saldo user (poin cukup?)
  3. Klik "Approve"
  4. Sistem:
     - Update status → "approved"
     - Buat record poin_transaksis (negative)
     - Kurangi total_poin user
     - Status → "processing"
     - Siap untuk transfer
- **Proses Reject**:
  1. Jika ada masalah dengan rekening
  2. Klik "Reject"
  3. Masukkan alasan (contoh: rekening invalid, limit exceed)
  4. Sistem:
     - Update status → "rejected"
     - TIDAK kurangi poin user
     - Kirim notifikasi rejection
- **Tabel Terkait**: `penarikan_tunai`, `users`, `poin_transaksis`, `notifikasi`

#### 💳 Proses Transfer Pembayaran
- **Kode UC**: UC-809
- **Deskripsi**: Admin melakukan transfer tunai ke rekening user
- **Cara Kerja**:
  1. Lihat list "approved" withdrawals
  2. Lakukan transfer bank (manual atau via API)
  3. Update sistem:
     - Input: tanggal transfer, ref number
     - Status → "completed"
     - Kirim notifikasi ke user: "Uang sudah masuk"
     - Log transaksi
- **Tracking**:
  - Reference number untuk tracking bank
  - Tanggal transfer
  - Konfirmasi user
- **Tabel Terkait**: `penarikan_tunai`, `notifikasi`, `log_aktivitas`

---

### 4. **LIHAT ARTIKEL & SUPPORT** (2 Fitur - BARU)

#### 📖 Lihat & Kelola Artikel
- **Kode UC**: UC-901
- **Deskripsi**: Admin dapat melihat artikel yang dipublikasi admin/superadmin
- **Fitur**:
  - View all articles
  - Edit (jika permission)
  - Delete (jika permission)
  - Analytics (views, likes, comments)
- **Tabel Terkait**: `artikels`

#### 👥 Manage User Support / Customer Service
- **Kode UC**: UC-902
- **Deskripsi**: Admin mengelola pertanyaan/support dari user
- **Fitur**:
  - Lihat list pertanyaan masuk
  - Baca detail pertanyaan
  - Reply/balas pertanyaan
  - Status: open, in-progress, resolved
  - Close ticket
- **Tabel Terkait**: `support_tickets` (atau email integrasi)

---

### 5. **ANALYTICS & REPORTING** (4 Fitur)

#### 📊 Lihat User Activity & Dashboard
- **Kode UC**: UC-904 & UC-908
- **Deskripsi**: Melihat aktivitas user dan dashboard admin
- **Dashboard Menampilkan**:
  - Total user terdaftar
  - Setor sampah hari ini (pending/approved/rejected)
  - Tukar produk hari ini (pending/approved)
  - Penarikan tunai hari ini (pending/approved)
  - Total poin yang terditribusi
  - Chart trend aktivitas
- **User Activity Log**:
  - Semua aktivitas user (login, setor, tukar, withdraw)
  - Filter by date range
  - Search by user
- **Tabel Terkait**: `log_aktivitas`, `users`, `tabung_sampah`, `penukaran_produk`, `penarikan_tunai`

#### 📈 Generate Daily Report
- **Kode UC**: UC-909
- **Deskripsi**: Generate laporan harian operasional
- **Laporan Berisi**:
  - Summary: Total setor, tukar, withdraw hari ini
  - Detail transaction per type
  - User participation stats
  - Top users hari ini
  - Chart & visualization
  - Export option (PDF, CSV)
- **Tabel Terkait**: `tabung_sampah`, `penukaran_produk`, `penarikan_tunai`, `poin_transaksis`

---

## 👑 FITUR SUPERADMIN (Manajer Sistem) - 13 UC

Fitur untuk superadmin yang mengelola konfigurasi & konten sistem.

### 1. **MANAJEMEN KATEGORI SAMPAH** (2 Fitur)

#### 📝 Buat & Edit Kategori Sampah
- **Kode UC**: UC-401 & UC-402
- **Deskripsi**: Membuat kategori sampah baru (Plastik, Kertas, Logam, dll)
- **Informasi Kategori**:
  - Nama kategori (Plastik, Kertas, dll)
  - Icon & warna kategori
  - Deskripsi kategori
  - Kategori aktif/tidak aktif
- **Cara Buat**:
  1. Menu "Manajemen Kategori"
  2. Klik "Tambah Kategori"
  3. Input nama, icon, warna
  4. Simpan
- **Cara Edit**:
  1. Pilih kategori
  2. Klik "Edit"
  3. Ubah data
  4. Simpan
- **Tabel Terkait**: `kategori_sampah`

#### 📝 Buat & Edit Jenis Sampah
- **Kode UC**: UC-404 & UC-405
- **Deskripsi**: Membuat jenis sampah dalam kategori (PET, HDPE, dll) beserta harganya
- **Informasi Jenis**:
  - Nama jenis (PET Bottles, HDPE, Kertas Putih, dll)
  - Kategori parent
  - Harga per kg (dalam poin)
  - Deskripsi
  - Icon
- **Cara Buat**:
  1. Pilih kategori
  2. Klik "Tambah Jenis"
  3. Input nama jenis & harga/kg
  4. Simpan
- **Contoh Harga**:
  ```
  PLASTIK:
  - PET Bottles: 500 poin/kg
  - HDPE: 600 poin/kg
  
  KERTAS:
  - Koran: 200 poin/kg
  - Kemasan: 300 poin/kg
  ```
- **Tabel Terkait**: `jenis_sampah`

---

### 2. **MANAJEMEN BADGE & GAMIFIKASI** (1 Fitur - Dari Detailed UCD)

#### 🎖️ Buat & Edit Badge
- **Kode UC**: UC-606, UC-607, UC-608
- **Deskripsi**: Superadmin membuat badge baru untuk gamifikasi
- **Informasi Badge**:
  - Nama badge
  - Icon/gambar
  - Deskripsi & cerita badge
  - Tipe badge (poin, setor, kombinasi, special, ranking)
  - Syarat unlock:
    - Syarat poin (berapa poin total)
    - Syarat setor (berapa kg setor)
    - Atau kombinasi keduanya
  - Reward poin saat unlock
  - Status publish (draft/published)
- **Contoh Badge**:
  ```
  🌱 "Eco Starter"
  - Syarat: 10 kg setor sampah
  - Reward: 250 poin
  - Deskripsi: "Anda sudah mulai perjalanan eco-friendly!"
  
  🌍 "Planet Guardian"
  - Syarat: 1000 poin + 50 kg setor
  - Reward: 500 poin
  - Deskripsi: "Wujud nyata komitmen lingkungan!"
  
  🏆 "Leaderboard Champion"
  - Syarat: Top 1 di leaderboard 1 bulan
  - Reward: 1000 poin + status badge
  - Deskripsi: "Raja recycler bulan ini!"
  ```
- **Cara Buat**:
  1. Menu "Manajemen Badge"
  2. Klik "Tambah Badge"
  3. Input semua data
  4. Preview badge
  5. Publish
- **Tabel Terkait**: `badges`

---

### 3. **MANAJEMEN PRODUK REDEMPTION** (2 Fitur - Dari Detailed UCD)

#### 🎁 Buat & Edit Produk
- **Kode UC**: UC-713, UC-714, UC-715, UC-716
- **Deskripsi**: Mengelola produk yang bisa ditukar dengan poin
- **Informasi Produk**:
  - Nama produk
  - Kategori produk (pulsa, voucher, merchandise, dll)
  - Harga dalam poin
  - Stok tersedia
  - Gambar produk
  - Deskripsi lengkap
  - Cara penggunaan (untuk voucher/pulsa)
  - Status: active/inactive
- **Contoh Produk**:
  ```
  💰 Pulsa Telkomsel 10rb - 500 poin
  💰 Pulsa Indosat 10rb - 500 poin
  🎟️ OVO Voucher 25rb - 1000 poin
  🎟️ GCash 50rb - 2000 poin
  👕 T-Shirt Mendaur - 2500 poin
  🧢 Topi Eco-Friendly - 1500 poin
  ```
- **Cara Buat**:
  1. Menu "Manajemen Produk"
  2. Klik "Tambah Produk"
  3. Upload gambar
  4. Input data lengkap
  5. Set stok awal
  6. Publish
- **Tabel Terkait**: `produks`

---

### 4. **MANAJEMEN KONTEN (ARTIKEL & BANNER)** (2 Fitur - BARU)

#### 📝 Buat & Publish Artikel
- **Kode UC**: UC-1001, UC-1002, UC-1003, UC-1004
- **Deskripsi**: Membuat konten edukatif untuk user
- **Informasi Artikel**:
  - Judul artikel
  - Kategori (tips, berita, tips, tutorial)
  - Konten (text editor WYSIWYG)
  - Gambar/featured image
  - Excerpt (preview singkat)
  - Tags
  - Status: draft/published
  - Publish date
- **Jenis Konten**:
  - Tips menghemat sampah
  - Panduan daur ulang step-by-step
  - Berita update sistem
  - Cerita sukses user
  - Tips maksimalkan poin
- **Cara Buat**:
  1. Menu "Manajemen Artikel"
  2. Klik "Buat Artikel Baru"
  3. Input judul & konten
  4. Add gambar
  5. Set kategori & tags
  6. Preview
  7. Draft atau Publish
- **Tabel Terkait**: `artikels`

#### 📢 Buat & Edit Banner Promosi
- **Kode UC**: UC-1005, UC-1006
- **Deskripsi**: Membuat banner promosi untuk menampilkan di dashboard
- **Informasi Banner**:
  - Judul campaign
  - Gambar banner (upload)
  - Link destination (optional)
  - Start & end date
  - Status: active/inactive
  - Position (home banner, popup, sidebar)
- **Contoh Banner**:
  - "Setor 100 kg = Unlock Badge Baru!"
  - "Promo Pulsa: 1.5x poin hingga akhir bulan"
  - "Download app kami - Extra 1000 poin!"
- **Tabel Terkait**: `banners` (atau similar)

---

### 5. **ANALYTICS & MONITORING** (3 Fitur - BARU)

#### 📊 Monitor Semua Transaksi
- **Kode UC**: UC-1101 (Monitor Transactions)
- **Deskripsi**: Melihat & analyze semua transaksi sistem secara real-time
- **Transaksi Dipantau**:
  - Setor sampah: total, pending, approved, rejected
  - Tukar produk: total, pending, approved
  - Penarikan tunai: total, pending, approved, completed
  - Poin transaksi: sumber & jumlah
- **Filter & Analytics**:
  - By date range
  - By transaction type
  - By user
  - Chart trend
  - Export data
- **Tabel Terkait**: `tabung_sampah`, `penukaran_produk`, `penarikan_tunai`, `poin_transaksis`

#### 📈 Generate System Reports
- **Kode UC**: UC-1102 (Generate System Reports)
- **Deskripsi**: Generate laporan komprehensif sistem
- **Jenis Laporan**:
  - Monthly summary (all transactions)
  - User growth & retention
  - Revenue projection (based on conversions)
  - Top waste types
  - Top products
  - Badge unlock trends
  - System health report
- **Format Export**: PDF, Excel, CSV
- **Tabel Terkait**: `users`, `tabung_sampah`, `penukaran_produk`, `penarikan_tunai`, `user_badges`

#### ⚙️ Manage System Settings
- **Kode UC**: UC-1103 (Manage System Settings)
- **Deskripsi**: Konfigurasi parameter sistem
- **Setting Tersedia**:
  - Points multiplier (standard rate: 1 poin = Rp X)
  - Platform fee untuk withdrawal (%)
  - Minimum withdrawal amount
  - Tipe nasabah allowed (konvensional/modern)
  - Lokasi penyerahan sampah
  - Operating hours
  - Notification settings
  - Email template
- **Cara Ubah**:
  1. Menu "System Settings"
  2. Edit parameter
  3. Preview perubahan
  4. Save & apply
- **Tabel Terkait**: `settings` (config table)

---

### 6. **MANAJEMEN AKUN ADMIN** (2 Fitur - Dari Detailed UCD)

#### 👨‍💼 Buat & Kelola Akun Admin
- **Kode UC**: UC-1001, UC-1002, UC-1003
- **Deskripsi**: Membuat & mengatur akun admin operator
- **Info Admin**:
  - Nama
  - Email
  - Nomor HP
  - Role (Admin, Superadmin)
  - Permissions (detailed access control)
  - Status: active/inactive
- **Permissions**:
  - View deposits ✓/✗
  - Approve deposits ✓/✗
  - View withdrawals ✓/✗
  - Approve withdrawals ✓/✗
  - View products ✓/✗
  - Generate reports ✓/✗
  - Manage settings ✓/✗
- **Tabel Terkait**: `users`, `roles`, `permissions`

---

## ⚡ PROSES OTOMATIS SISTEM - 5 UC

Proses yang berjalan otomatis tanpa intervensi user.

### 1. 🧮 Hitung Poin Otomatis
- **Trigger**: Setelah deposit approved
- **Formula**: Berat (kg) × Harga per kg jenis sampah
- **Contoh**: 2 kg plastik (500/kg) = 1000 poin
- **Otomatis**:
  - Buat record di `poin_transaksis`
  - Update `users.total_poin`
  - Log aktivitas

### 2. 🎖️ Track Badge Progress
- **Trigger**: Setiap transaksi poin
- **Cara Kerja**: Update `badge_progress.current_value` berdasarkan tipe badge
- **Contoh**: 
  - Badge "Eco Starter" (syarat 10 kg setor) → increment current_value ketika ada setor
  - Badge "Poin King" (1000 poin) → increment berdasarkan total poin user

### 3. 🏆 Unlock Badge Otomatis
- **Trigger**: Ketika `badge_progress.current_value >= target_value`
- **Otomatis**:
  1. Set `badge_progress.is_unlocked = TRUE`
  2. Create record di `user_badges`
  3. Award reward_poin
  4. Create poin_transaksis (source='badge')
  5. Kirim notifikasi: "🎉 Unlock Badge!"
  6. Update leaderboard

### 4. 📧 Kirim Notifikasi Otomatis
- **Trigger**: Berbagai event
- **Event & Notifikasi**:
  - Deposit approved: "Poin masuk! +XXX poin"
  - Deposit rejected: "Deposit ditolak, alasan: ..."
  - Tukar diapprove: "Produk siap diambil"
  - Penarikan approved: "Uang akan ditransfer hari ini"
  - Badge unlock: "🎉 Badge baru unlock!"
  - Leaderboard top 10: "Anda masuk top 10 bulan ini!"
- **Channel**: Email, SMS, Push notification (via app)

### 5. 📝 Log Aktivitas & Audit Trail
- **Trigger**: Setiap action penting
- **Tercatat**:
  - User login
  - Submit deposit
  - Approve/reject deposit
  - Tukar poin
  - Approve/reject tukar
  - Request withdraw
  - Approve/reject withdraw
  - Process transfer
  - Badge unlock
  - Create/edit article
  - Admin login
- **Tabel**: `log_aktivitas`, `audit_logs`
- **Tujuan**: Compliance, troubleshooting, security audit

---

## 📂 KATEGORI FITUR PER BISNIS

### 1. **WASTE MANAGEMENT (Manajemen Sampah)** - 8 UC
Fitur seputar deposit, verifikasi, dan tracking sampah
- Submit waste deposit (Nasabah)
- View deposit history (Nasabah)
- View waste categories (Nasabah)
- View waste types & pricing (Nasabah)
- View pending deposits (Admin)
- Approve/Reject deposits (Admin)
- Verify weight (Admin)
- Create/Edit waste types (Superadmin)

### 2. **POINTS & REWARDS SYSTEM** - 7 UC
Fitur seputar poin, riwayat, dan pertukaran
- View points balance (Nasabah)
- View points history (Nasabah)
- Filter points by type (Nasabah)
- View leaderboard (Nasabah)
- Calculate points (System auto)
- Track point sources (System auto)
- Update total points (System auto)

### 3. **GAMIFICATION (BADGE & ACHIEVEMENTS)** - 12 UC
Fitur seputar badge, unlock, dan reward
- View available badges (Nasabah)
- View badge progress (Nasabah)
- View earned badges (Nasabah)
- Share achievements (Nasabah)
- Create badges (Superadmin)
- Edit badges (Superadmin)
- Set badge criteria (Superadmin)
- Publish badges (Superadmin)
- Track badge progress (System auto)
- Unlock badges (System auto)
- Award badge points (System auto)
- View badge analytics (Admin/Superadmin)

### 4. **PRODUCT REDEMPTION** - 7 UC
Fitur seputar tukar poin dengan produk
- View product catalog (Nasabah)
- Redeem product (Nasabah)
- View redemption history (Nasabah)
- View pending redemptions (Admin)
- Approve/Reject redemptions (Admin)
- Mark product collected (Admin)
- Create/Edit/Manage products (Superadmin)

### 5. **CASH WITHDRAWAL** - 6 UC
Fitur seputar pencairan tunai
- Request withdrawal (Nasabah)
- View withdrawal history (Nasabah)
- View pending withdrawals (Admin)
- Approve/Reject withdrawals (Admin)
- Process payment transfer (Admin)
- Manage withdrawal settings (Superadmin)

### 6. **CONTENT MANAGEMENT** - 5 UC
Fitur seputar artikel dan banner
- Read articles (Nasabah)
- View articles (Admin)
- Create articles (Superadmin)
- Edit articles (Superadmin)
- Create/Edit banners (Superadmin)

### 7. **USER MANAGEMENT** - 6 UC
Fitur seputar akun & profil
- Register account (Nasabah)
- Login (Nasabah)
- View profile (Nasabah)
- Update profile (Nasabah)
- Create admin accounts (Superadmin)
- Manage admin accounts (Superadmin)

### 8. **ANALYTICS & REPORTING** - 8 UC
Fitur seputar laporan & dashboard
- View my statistics (Nasabah)
- View admin dashboard (Admin)
- View user activity log (Admin)
- Generate daily report (Admin)
- Export report (Admin)
- View system analytics (Superadmin)
- Monitor transactions (Superadmin)
- Generate system reports (Superadmin)

### 9. **SYSTEM ADMINISTRATION** - 5 UC
Fitur seputar konfigurasi sistem
- Manage roles & permissions (Superadmin)
- View audit logs (Superadmin)
- View system settings (Superadmin)
- Update system settings (Superadmin)
- System maintenance (Superadmin)

### 10. **AUTOMATED PROCESSES** - 5 UC
Proses background otomatis
- Calculate points (System)
- Track badge progress (System)
- Unlock badges (System)
- Send notifications (System)
- Log activities (System)

---

## 📊 RINGKASAN TOTAL FITUR

```
┌─────────────────────────────────────────────┐
│  TOTAL FITUR SISTEM MENDAUR: 43+ UC         │
├─────────────────────────────────────────────┤
│  Nasabah (Regular User):        13 UC       │
│  Admin (Operator):              17 UC       │
│  Superadmin (System Manager):   13 UC       │
│  System (Automated):             5 UC       │
├─────────────────────────────────────────────┤
│  Total:                         48 UC       │
│  (Termasuk 13 fitur baru: withdrawal + articles)
└─────────────────────────────────────────────┘

KATEGORI BISNIS:
- Waste Management       → 8 UC (18%)
- Points & Rewards      → 7 UC (15%)
- Gamification/Badges   → 12 UC (25%)
- Product Redemption    → 7 UC (15%)
- Cash Withdrawal       → 6 UC (12%)
- Content Management    → 5 UC (10%)
- User Management       → 6 UC (13%)
- Analytics/Reporting   → 8 UC (16%)
- System Administration → 5 UC (10%)
- Auto Processes        → 5 UC (10%)

STATUS: ✅ PRODUCTION READY
```

---

## 🎯 FEATURE PRIORITAS IMPLEMENTASI

### Phase 1 - Core (Sudah)
- [x] Authentication & user management
- [x] Waste deposit & verification
- [x] Points calculation & tracking
- [x] Admin dashboard & approval

### Phase 2 - Gamification (Sudah)
- [x] Badge system
- [x] Leaderboard
- [x] Badge unlock & rewards

### Phase 3 - Commerce (Sudah)
- [x] Product catalog
- [x] Product redemption
- [x] Stock management

### Phase 4 - New (BARU)
- [x] Cash withdrawal system
- [x] Article/content management
- [x] Customer support tickets
- [x] Advanced analytics

### Phase 5 - Optimization (Future)
- [ ] Mobile app optimization
- [ ] API rate limiting
- [ ] Cache optimization
- [ ] Multi-language support

---

**Document Version**: 2.0  
**Last Updated**: 29 November 2025  
**Status**: Production Ready ✅  
**Total Features**: 43+ Use Cases  
**Coverage**: 100% sistem terdokumentasi
