# 📋 Database Schema Summary - Mendaur API

Dokumentasi skema database untuk tim Frontend.

---

## 🧑‍💻 Tabel `users` - Data Pengguna

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `user_id` | int (PK) | ID unik pengguna |
| `nama` | string | Nama lengkap |
| `email` | string | Email login (unique) |
| `password` | string | Password (hashed) |
| `no_hp` | string | Nomor handphone |
| `alamat` | text | Alamat lengkap |
| `foto_profil` | string | URL foto profil (Cloudinary) |
| `foto_profil_public_id` | string | Cloudinary ID untuk hapus foto |
| **`display_poin`** | int | **Poin untuk LEADERBOARD** (tidak berkurang saat transaksi) |
| **`actual_poin`** | int | **Poin SALDO untuk transaksi** (penukaran, withdrawal) |
| `total_setor_sampah` | decimal | Total kg sampah yang disetor |
| `level` | int | Level: 1=nasabah, 2=admin, 3=superadmin |
| `role_id` | int (FK) | Foreign key ke roles |
| `status` | string | Status: active, suspended, inactive |
| `tipe_nasabah` | string | Tipe: 'konvensional' atau 'modern' |
| `nama_bank` | string | Nama bank (BCA, BNI, dll) - hanya modern |
| `nomor_rekening` | string | Nomor rekening - hanya modern |
| `atas_nama_rekening` | string | Nama pemilik rekening - hanya modern |
| `badge_title_id` | int (FK) | Badge yang dipilih sebagai title |

### ⚠️ Penting: Sistem Dual-Point

| Field | Fungsi | Kapan Bertambah | Kapan Berkurang |
|-------|--------|-----------------|-----------------|
| `display_poin` | Leaderboard/ranking | ✅ Setor sampah, bonus, badge | ❌ TIDAK PERNAH |
| `actual_poin` | Saldo transaksi | ✅ Setor sampah, bonus, badge, refund | ✅ Penukaran, withdrawal |

**Gunakan `actual_poin` untuk menampilkan saldo yang bisa digunakan user!**

---

## 📦 Tabel `produks` - Produk Reward

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `produk_id` | int (PK) | ID unik produk |
| `nama` | string | Nama produk |
| `deskripsi` | text | Deskripsi produk |
| `harga_poin` | int | Harga dalam poin |
| `stok` | int | Jumlah stok tersedia |
| `kategori` | string | Kategori produk |
| `foto` | string | URL foto produk |
| `status` | string | Status: tersedia, habis, nonaktif |

---

## 🔄 Tabel `penukaran_produk` - Penukaran Produk

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `penukaran_produk_id` | int (PK) | ID unik penukaran |
| `user_id` | int (FK) | ID nasabah |
| `produk_id` | int (FK) | ID produk |
| `nama_produk` | string | Nama produk (snapshot) |
| `poin_digunakan` | int | Poin yang digunakan |
| `jumlah` | int | Jumlah produk |
| `status` | string | Status: pending, approved, cancelled, completed |
| `metode_ambil` | string | Metode: ambil_sendiri, dikirim |
| `catatan` | text | Catatan |
| `tanggal_penukaran` | datetime | Tanggal request |
| `tanggal_diambil` | datetime | Tanggal diambil/diterima |

### Status Flow:
```
pending → approved → completed
       ↘ cancelled
```

---

## 💰 Tabel `penarikan_tunai` - Withdrawal (Nasabah Modern)

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `penarikan_tunai_id` | int (PK) | ID unik penarikan |
| `user_id` | int (FK) | ID nasabah |
| `jumlah_poin` | int | Jumlah poin ditarik |
| `jumlah_rupiah` | decimal | Nilai rupiah |
| `nomor_rekening` | string | Rekening tujuan |
| `nama_bank` | string | Nama bank |
| `nama_penerima` | string | Nama penerima |
| `status` | string | Status: pending, approved, rejected |
| `catatan_admin` | text | Catatan admin |
| `processed_by` | int (FK) | ID admin yang proses |
| `processed_at` | datetime | Waktu diproses |

### Status Flow:
```
pending → approved
       ↘ rejected (poin dikembalikan)
```

---

## ♻️ Tabel `tabung_sampah` - Penyetoran Sampah

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `tabung_sampah_id` | int (PK) | ID unik penyetoran |
| `user_id` | int (FK) | ID nasabah |
| `jadwal_penyetoran_id` | int (FK) | ID jadwal |
| `nama_lengkap` | string | Nama penyetor |
| `no_hp` | string | No HP penyetor |
| `titik_lokasi` | string | Lokasi penyetoran |
| `jenis_sampah` | string | Jenis: Plastik, Kertas, Logam, dll |
| `foto_sampah` | string | URL foto sampah |
| `status` | string | Status: pending, approved, rejected |
| `berat_kg` | decimal | Berat (diisi admin) |
| `poin_didapat` | int | Poin yang diberikan |

### Status Flow:
```
pending → approved (poin diberikan)
       ↘ rejected
```

---

## 📊 Tabel `poin_transaksis` - Ledger Poin

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `poin_transaksi_id` | int (PK) | ID unik transaksi |
| `user_id` | int (FK) | ID user |
| `tabung_sampah_id` | int (FK) | ID tabung sampah (opsional) |
| `jenis_sampah` | string | Jenis sampah (opsional) |
| `berat_kg` | decimal | Berat kg (opsional) |
| `poin_didapat` | int | Jumlah poin (+/-) |
| `sumber` | string | Sumber transaksi (lihat di bawah) |
| `keterangan` | text | Deskripsi |
| `referensi_id` | int | ID entitas terkait |
| `referensi_tipe` | string | Tipe entitas |

### Nilai `sumber`:
| Sumber | Keterangan | poin_didapat |
|--------|------------|--------------|
| `setor_sampah` | Dari setor sampah | + Positif |
| `bonus` | Bonus event/promo | + Positif |
| `badge_reward` | Reward badge | + Positif |
| `penukaran_produk` | Tukar produk | - Negatif |
| `refund_penukaran` | Cancel penukaran | + Positif |
| `penarikan_tunai` | Withdrawal | - Negatif |
| `pengembalian_penarikan` | Reject withdrawal | + Positif |

---

## 📅 Tabel `jadwal_penyetorans` - Jadwal Bank Sampah

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `jadwal_penyetoran_id` | int (PK) | ID unik jadwal |
| `hari` | string | Hari: Senin, Selasa, ..., Minggu |
| `waktu_mulai` | time | Jam buka (HH:mm) |
| `waktu_selesai` | time | Jam tutup (HH:mm) |
| `lokasi` | string | Lokasi bank sampah |
| `status` | string | Status: Buka, Tutup |

---

## 🏆 Tabel `badges` - Badge/Achievement

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `badge_id` | int (PK) | ID unik badge |
| `nama` | string | Nama badge |
| `deskripsi` | text | Cara mendapatkan |
| `icon` | string | URL icon |
| `syarat_poin` | int | Minimal poin untuk unlock |
| `syarat_setor` | int | Minimal kg untuk unlock |
| `reward_poin` | int | Bonus poin saat unlock |
| `tipe` | string | Tipe: poin, setor, kombinasi, ranking |

---

## 📰 Tabel `artikels` - Artikel

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `artikel_id` | int (PK) | ID unik artikel |
| `judul` | string | Judul artikel |
| `slug` | string | URL slug |
| `konten` | text | Isi artikel |
| `foto_cover` | string | URL foto cover |
| `penulis` | string | Nama penulis |
| `kategori` | string | Kategori: tips, berita, edukasi |
| `tanggal_publikasi` | date | Tanggal publish |
| `views` | int | Jumlah view |

---

## 🗂️ Tabel Kategori Sampah

### `kategori_sampah`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `kategori_sampah_id` | int (PK) | ID kategori |
| `nama_kategori` | string | Nama: Plastik, Kertas, Logam, dll |
| `deskripsi` | text | Deskripsi |
| `icon` | string | URL icon |
| `warna` | string | Warna hex (#FF5722) |
| `is_active` | boolean | Aktif/nonaktif |

### `jenis_sampah`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `jenis_sampah_id` | int (PK) | ID jenis |
| `kategori_sampah_id` | int (FK) | ID kategori |
| `nama_jenis` | string | Nama: Botol PET, Kardus, dll |
| `harga_per_kg` | decimal | Harga per kg (display) |
| `satuan` | string | Satuan: kg, pcs |
| `kode` | string | Kode unik |
| `is_active` | boolean | Aktif/nonaktif |

---

## 🔔 Tabel `notifikasi` - Notifikasi

| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `notifikasi_id` | int (PK) | ID notifikasi |
| `user_id` | int (FK) | ID penerima |
| `judul` | string | Judul notifikasi |
| `pesan` | text | Isi pesan |
| `tipe` | string | Tipe: info, success, warning, error |
| `is_read` | boolean | Sudah dibaca |
| `related_id` | int | ID entitas terkait |
| `related_type` | string | Tipe entitas |

---

## 👤 Tabel Sistem

### `roles`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `role_id` | int (PK) | ID role |
| `nama_role` | string | Nama: nasabah, admin, superadmin |
| `level_akses` | int | Level: 1, 2, 3 |
| `deskripsi` | text | Deskripsi role |

### `role_permissions`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `role_permission_id` | int (PK) | ID permission |
| `role_id` | int (FK) | ID role |
| `permission_code` | string | Kode: manage_users, approve_deposits |
| `deskripsi` | text | Deskripsi |

---

## 📝 Catatan Penting untuk Frontend

### 1. Tipe Nasabah
- **Konvensional**: Tidak bisa withdrawal, poin hanya untuk tukar produk
- **Modern**: Bisa withdrawal ke rekening bank

### 2. Field Poin yang Digunakan
```javascript
// Untuk menampilkan saldo yang bisa digunakan:
const saldoPoin = user.actual_poin;

// Untuk leaderboard/ranking:
const rankingPoin = user.display_poin;
```

### 3. Status yang Perlu Di-handle
- `pending` - Menunggu approval
- `approved` - Disetujui
- `rejected` - Ditolak
- `cancelled` - Dibatalkan user
- `completed` - Selesai

### 4. Primary Key
Semua tabel menggunakan primary key dengan format `{nama_tabel}_id`, contoh:
- `user_id`
- `produk_id`
- `tabung_sampah_id`

---

*Dokumentasi dibuat: 1 Januari 2026*
*Versi: 2.0*
