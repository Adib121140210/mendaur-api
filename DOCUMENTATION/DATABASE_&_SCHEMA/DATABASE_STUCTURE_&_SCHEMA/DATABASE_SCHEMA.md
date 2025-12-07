# 📊 Mendaur API - Database Schema Documentation

## ✅ Successfully Implemented Database Schema

---

## 📋 **Tables Overview**

### 1. **users**
Menyimpan data pengguna/nasabah.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| nama | string | Nama lengkap user |
| email | string | Email (unique) |
| password | string | Password (hashed) |
| no_hp | string | Nomor HP |
| alamat | text | Alamat lengkap |
| foto_profil | string | Path foto profil |
| total_poin | integer | Total poin terkumpul (default: 0) |
| total_setor_sampah | integer | Total setor sampah (default: 0) |
| level | string | Level user (default: 'Pemula') |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Levels**: Pemula, Bronze, Silver, Gold, Platinum

---

### 2. **jadwal_penyetorans**
Jadwal penyetoran sampah.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| tanggal | date | Tanggal penyetoran |
| waktu_mulai | time | Waktu mulai |
| waktu_selesai | time | Waktu selesai |
| lokasi | string | Lokasi penyetoran |
| kapasitas | integer | Kapasitas maksimal (default: 100) |
| status | enum | Status jadwal (default: 'aktif') |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Status**: aktif, penuh, selesai, dibatalkan

---

### 3. **tabung_sampah**
Data penyetoran sampah dari user.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key → users |
| jadwal_id | bigint | Foreign key → jadwal_penyetorans |
| nama_lengkap | string | Nama penyetor |
| no_hp | string | No HP penyetor |
| titik_lokasi | text | Lokasi/alamat |
| jenis_sampah | string | Jenis sampah |
| berat_kg | decimal | Berat dalam kg (default: 0) |
| foto_sampah | text | Path foto sampah |
| status | enum | Status setoran (default: 'pending') |
| poin_didapat | integer | Poin yang didapat (default: 0) |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Status**: pending, approved, rejected

---

### 4. **jenis_sampahs**
Jenis-jenis sampah yang diterima beserta harganya.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| nama | string | Nama jenis sampah |
| deskripsi | text | Deskripsi |
| harga_per_kg | decimal | Harga per kg (dalam rupiah) |
| icon | string | Icon/emoji |
| warna | string | Hex color code |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Example Data**:
- Plastik: Rp 3.000/kg
- Kertas: Rp 2.000/kg
- Logam: Rp 5.000/kg
- Kaca: Rp 1.500/kg
- Elektronik: Rp 8.000/kg

---

### 5. **produks**
Produk yang dapat ditukar dengan poin.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| nama | string | Nama produk |
| deskripsi | text | Deskripsi produk |
| harga_poin | integer | Harga dalam poin |
| stok | integer | Stok tersedia (default: 0) |
| kategori | string | Kategori produk |
| foto | string | Path foto produk |
| status | enum | Status produk (default: 'tersedia') |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Status**: tersedia, habis, nonaktif

**Kategori**: Tas, Botol, Pupuk, Sedotan, Voucher, dll

---

### 6. **transaksis**
Transaksi penukaran poin dengan produk.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key → users |
| produk_id | bigint | Foreign key → produks |
| kategori_id | bigint | Foreign key → kategori_transaksi |
| jumlah | integer | Jumlah produk |
| total_poin | integer | Total poin digunakan |
| status | enum | Status transaksi (default: 'pending') |
| metode_pengiriman | string | Metode pengiriman |
| alamat_pengiriman | text | Alamat tujuan |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Status**: pending, diproses, dikirim, selesai, dibatalkan

---

### 7. **kategori_transaksi**
Kategori/tipe transaksi.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| nama | string | Nama kategori |
| deskripsi | text | Deskripsi |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Example Data**:
- Penukaran Poin
- Penyetoran Sampah
- Bonus Reward

---

### 8. **artikels**
Artikel edukasi tentang lingkungan dan sampah.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| judul | string | Judul artikel |
| slug | string | URL slug (unique) |
| konten | longText | Isi artikel |
| foto_cover | string | Path foto cover |
| penulis | string | Nama penulis |
| kategori | string | Kategori artikel |
| tanggal_publikasi | date | Tanggal publikasi |
| views | integer | Jumlah views (default: 0) |
| created_at | timestamp | - |
| updated_at | timestamp | - |

---

### 9. **badges**
Badge/lencana achievement untuk gamifikasi.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| nama | string | Nama badge |
| deskripsi | text | Deskripsi |
| icon | string | Icon/emoji badge |
| syarat_poin | integer | Syarat poin minimum (default: 0) |
| syarat_setor | integer | Syarat setor minimum (default: 0) |
| tipe | enum | Tipe badge (default: 'poin') |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Tipe**: poin, setor, kombinasi

---

### 10. **user_badges**
Badge yang sudah didapat oleh user.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key → users |
| badge_id | bigint | Foreign key → badges |
| tanggal_dapat | date | Tanggal mendapat badge |

---

### 11. **notifikasi**
Notifikasi untuk user.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key → users |
| judul | string | Judul notifikasi |
| pesan | text | Isi pesan |
| tipe | enum | Tipe notifikasi (default: 'info') |
| dibaca | boolean | Status dibaca (default: false) |
| tanggal | date | Tanggal notifikasi |
| created_at | timestamp | - |
| updated_at | timestamp | - |

**Tipe**: info, success, warning, error

---

### 12. **log_aktivitas**
Log semua aktivitas user.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key → users |
| tipe_aktivitas | string | Tipe aktivitas |
| deskripsi | text | Deskripsi aktivitas |
| poin_perubahan | integer | Perubahan poin (default: 0) |
| tanggal | date | Tanggal aktivitas |
| created_at | timestamp | - |

**Tipe Aktivitas**: 
- Setor Sampah
- Tukar Poin
- Dapat Badge
- Bonus Reward

---

## 🔗 **Relationships**

```
users
├── hasMany → tabung_sampah
├── hasMany → transaksis
├── hasMany → notifikasi
├── hasMany → log_aktivitas
└── belongsToMany → badges (through user_badges)

jadwal_penyetorans
└── hasMany → tabung_sampah

produks
└── hasMany → transaksis

kategori_transaksi
└── hasMany → transaksis

badges
└── belongsToMany → users (through user_badges)
```

---

## 🎯 **Sample Data Seeded**

- ✅ 3 Users (Adib, Siti, Budi)
- ✅ 3 Jadwal Penyetoran
- ✅ 3 Tabung Sampah Records
- ✅ 5 Jenis Sampah Types
- ✅ 5 Produk Items
- ✅ 3 Kategori Transaksi

---

## 🚀 **Migration Commands**

```bash
# Fresh migration (drop all tables and recreate)
php artisan migrate:fresh

# Fresh migration with seeder
php artisan migrate:fresh --seed

# Seed only
php artisan db:seed

# Seed specific seeder
php artisan db:seed --class=UserSeeder
```

---

## 📝 **Next Steps**

1. ✅ Create Models for all tables
2. ✅ Create Controllers for API endpoints
3. ✅ Create API routes
4. ✅ Update React frontend to use new schema
5. ✅ Add authentication (Sanctum)
6. ✅ Add authorization (Policies)

---

## 🔐 **Test Credentials**

```
Email: adib@example.com
Password: password

Email: siti@example.com
Password: password

Email: budi@example.com
Password: password
```

---

**Last Updated**: November 14, 2025
