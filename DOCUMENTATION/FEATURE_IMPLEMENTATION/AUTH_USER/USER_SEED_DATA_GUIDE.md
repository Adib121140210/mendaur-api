# 📋 USER SEED DATA - KONVENSIONAL VS MODERN

## ✅ STRUKTUR DATA YANG BENAR

### KONVENSIONAL USERS
Pengguna yang mendapatkan poin langsung dan bisa langsung menggunakan untuk berbagai transaksi.

**Field yang HARUS NULL:**
```
- nama_bank: NULL
- nomor_rekening: NULL
- atas_nama_rekening: NULL
```

**Field yang HARUS ADA:**
```
- tipe_nasabah: 'konvensional'
- total_poin: angka (usable poin)
- poin_tercatat: same as total_poin
```

**Contoh Data:**
```php
[
    'nama' => 'Adib Surya',
    'email' => 'adib@example.com',
    'no_hp' => '081234567890',
    'total_poin' => 150,
    'poin_tercatat' => 150,
    'tipe_nasabah' => 'konvensional',
    'nama_bank' => null,  ← HARUS NULL
    'nomor_rekening' => null,  ← HARUS NULL
    'atas_nama_rekening' => null,  ← HARUS NULL
]
```

---

### MODERN USERS
Pengguna yang mendapatkan poin tercatat tapi TIDAK bisa langsung menggunakan. Poin tercatat hanya untuk audit/tracking. Modern users hanya bisa melakukan withdrawal ke rekening bank mereka.

**Field yang HARUS TERISI:**
```
- nama_bank: nama bank (e.g., 'BNI', 'MANDIRI', 'BCA')
- nomor_rekening: nomor rekening
- atas_nama_rekening: nama pemilik rekening
```

**Field yang HARUS ADA:**
```
- tipe_nasabah: 'modern'
- total_poin: ALWAYS 0 (blocked, not usable)
- poin_tercatat: angka (recorded only, not usable directly)
```

**Contoh Data:**
```php
[
    'nama' => 'Reno Wijaya',
    'email' => 'reno@example.com',
    'no_hp' => '085555666777',
    'total_poin' => 0,  ← HARUS 0 (BLOCKED)
    'poin_tercatat' => 500,  ← Recorded only
    'tipe_nasabah' => 'modern',
    'nama_bank' => 'BNI',  ← HARUS TERISI
    'nomor_rekening' => '1234567890',  ← HARUS TERISI
    'atas_nama_rekening' => 'Reno Wijaya',  ← HARUS TERISI
]
```

---

## 🔍 PERBEDAAN UTAMA

| Aspek | Konvensional | Modern |
|-------|--------------|--------|
| **Total Poin** | Usable (aktif) | 0 (blocked) |
| **Poin Tercatat** | Same as total poin | Recorded only |
| **Banking Info** | NULL (tidak perlu) | REQUIRED (untuk withdrawal) |
| **Transaksi Langsung** | ✅ Bisa pakai poin | ❌ Harus withdrawal dulu |
| **Feature Access** | ✅ Semua fitur | ❌ Blocked dari withdrawal/redemption |
| **Badge Rewards** | → total_poin (usable) | → poin_tercatat (audit trail only) |

---

## 🛠️ USER SEEDER UPDATE

File: `database/seeders/UserSeeder.php`

**Update Include:**
1. ✅ 4 Konvensional users (NO banking info)
2. ✅ 2 Modern users (WITH banking info)
3. ✅ 1 Test user (Konvensional, NO banking info)
4. ✅ Added `poin_tercatat` field untuk semua users
5. ✅ Added `tipe_nasabah` field untuk semua users

---

## ✅ VERIFIKASI DATA

Jalankan script ini untuk memverifikasi:

```bash
php artisan db:seed --class=UserSeeder
php verify_user_seed.php
```

**Output yang diharapkan:**
```
✅ SEMUA DATA VALID!

Summary:
  ✅ Konvensional users (4): NO banking info
  ✅ Modern users (2): HAS banking info

✅ Data seed sudah benar sesuai dual-nasabah logic!
```

---

## 🚀 COMMANDS YANG TERSEDIA

```bash
# Re-seed hanya user table
php artisan db:seed --class=UserSeeder

# Verifikasi data seed
php verify_user_seed.php

# Lihat semua users (Tinker)
php artisan tinker
>>> App\Models\User::all()

# Lihat konvensional users saja
>>> App\Models\User::where('tipe_nasabah', 'konvensional')->get()

# Lihat modern users saja
>>> App\Models\User::where('tipe_nasabah', 'modern')->get()
```

---

## 📝 CATATAN PENTING

1. **Konvensional users TIDAK boleh memiliki banking info**
   - Ini adalah fitur desain untuk membedakan dua tipe nasabah
   - Banking info hanya untuk modern users yang membutuhkan withdrawal

2. **Total Poin untuk Modern users HARUS 0**
   - Ini memastikan mereka tidak bisa mengakses fitur withdrawal/redemption dengan cara lain
   - Feature access control di middleware akan memverifikasi ini

3. **Poin Tercatat untuk tracking dan audit**
   - Semua poin dari badge/reward akan tercatat di poin_tercatat
   - Untuk konvensional: total_poin = poin_tercatat (both usable)
   - Untuk modern: hanya poin_tercatat yang diupdate (not usable)

---

Date: November 28, 2025
Status: ✅ Updated & Verified
