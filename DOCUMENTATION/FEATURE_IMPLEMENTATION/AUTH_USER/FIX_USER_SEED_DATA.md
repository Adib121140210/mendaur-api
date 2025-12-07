# 🔧 FIX: USER SEED DATA - KONVENSIONAL vs MODERN SEPARATION

## 📋 Masalah yang Ditemukan

User konvensional di data seed memiliki kolom `nama_bank` dan `nomor_rekening`, padahal seharusnya:
- **Konvensional users**: `nama_bank = NULL`, `nomor_rekening = NULL`, `atas_nama_rekening = NULL`
- **Modern users**: Harus memiliki banking information

---

## ✅ PERBAIKAN YANG DILAKUKAN

### 1. **Update UserSeeder** (`database/seeders/UserSeeder.php`)

**Sebelum:**
- 4 users tanpa tipe_nasabah yang jelas
- Tidak ada banking info untuk siapa pun

**Sesudah:**
- 4 Konvensional users: `tipe_nasabah='konvensional'`, `nama_bank=NULL`, `nomor_rekening=NULL`
- 2 Modern users: `tipe_nasabah='modern'`, dengan banking info (BNI, MANDIRI, no rek, dll)
- 1 Test user: Konvensional, tanpa banking info
- Semua users punya `poin_tercatat` field

**Konvensional Users:**
```
1. Adib Surya (adib@example.com)
2. Siti Aminah (siti@example.com)
3. Budi Santoso (budi@example.com)
4. test (test@test.com)

Semua: nama_bank=NULL, nomor_rekening=NULL, atas_nama_rekening=NULL
```

**Modern Users:**
```
1. Reno Wijaya (reno@example.com)
   - Bank: BNI
   - No Rek: 1234567890
   - Atas Nama: Reno Wijaya

2. Rina Kusuma (rina@example.com)
   - Bank: MANDIRI
   - No Rek: 9876543210
   - Atas Nama: Rina Kusuma
```

---

### 2. **Update Migration** (`database/migrations/2025_11_27_000004_add_rbac_dual_nasabah_to_users_table.php`)

**Sebelum:**
```php
$table->string('nama_bank')->default('BNI46')->after('poin_tercatat')
    ->comment('Default bank: BNI46');
```

**Sesudah:**
```php
$table->string('nama_bank')->nullable()->after('poin_tercatat')
    ->comment('Bank name - only for modern users (konvensional = NULL)');
```

**Alasan:**
- Database columns harus `nullable()` untuk Konvensional users
- Tidak ada default 'BNI46' di database level
- Data seeding/validation akan menangani ini di application level

---

### 3. **Update User Model** (`app/Models/User.php`)

**Sebelum:**
```php
protected $attributes = [
    'tipe_nasabah' => 'konvensional',
    'nama_bank' => 'BNI46',  // Default bank as per requirement
    'total_poin' => 0,
    'poin_tercatat' => 0,
    'total_setor_sampah' => 0,
];
```

**Sesudah:**
```php
protected $attributes = [
    'tipe_nasabah' => 'konvensional',
    'total_poin' => 0,
    'poin_tercatat' => 0,
    'total_setor_sampah' => 0,
];
// NOTE: nama_bank NOT set here - only Modern users should have banking info
```

**Alasan:**
- `nama_bank` default tidak boleh auto-applied ke semua users
- Hanya Modern users yang boleh punya banking info
- Konvensional users harus selalu NULL untuk banking fields

---

## 🎯 DUAL-NASABAH LOGIC (CORRECTED)

| Aspect | Konvensional | Modern |
|--------|--------------|--------|
| **tipe_nasabah** | 'konvensional' | 'modern' |
| **total_poin** | Active (usable) | 0 (blocked) |
| **poin_tercatat** | Same as total_poin | Recorded only (audit) |
| **nama_bank** | `NULL` | Has value (e.g., 'BNI') |
| **nomor_rekening** | `NULL` | Has value |
| **atas_nama_rekening** | `NULL` | Has value |
| **Transaksi** | Direct use poin | Must withdrawal to bank |
| **Feature Access** | All features ✅ | Blocked from withdrawal/redemption ❌ |

---

## 🚀 DEPLOYMENT STEPS

```bash
# 1. Update migration dan model sudah dilakukan ✅

# 2. Re-seed database dengan data yang benar
php artisan db:seed --class=UserSeeder

# 3. Verifikasi data seed
php verify_user_seed.php

# Expected output:
# ✅ SEMUA DATA VALID!
# ✅ Konvensional users (4): NO banking info
# ✅ Modern users (2): HAS banking info
```

---

## 📄 DOKUMENTASI LENGKAP

File: `USER_SEED_DATA_GUIDE.md` - Panduan lengkap tentang user seed data structure

---

## ⚠️ IMPORTANT NOTES

1. **Database-level vs Application-level Defaults**
   - Database: banking columns are `nullable()` (no defaults)
   - Application: defaults set in User model $attributes
   - Seeders: explicitly set values for each user type

2. **Data Validation in Controllers**
   - Controllers must validate that konvensional users NEVER have banking info
   - Modern users MUST have complete banking info before withdrawal feature

3. **Backward Compatibility**
   - Existing konvensional users might have banking info from before this fix
   - Recommendation: Run migration + reseed to clean up database

---

## ✅ VERIFICATION CHECKLIST

- [x] UserSeeder updated with correct data structure
- [x] Migration updated (banking columns nullable, no defaults)
- [x] User model updated (removed nama_bank default)
- [x] Documentation created (USER_SEED_DATA_GUIDE.md)
- [x] Verification script created (verify_user_seed.php)
- [ ] Database re-seeded (`php artisan db:seed --class=UserSeeder`)
- [ ] Data verified (`php verify_user_seed.php`)

---

## 📊 SUMMARY

**Before Fix:**
```
User A: konvensional, nama_bank='BNI46' ❌
User B: konvensional, nomor_rekening='123' ❌
User C: modern, no banking info ❌
```

**After Fix:**
```
User A: konvensional, nama_bank=NULL ✅
User B: konvensional, nomor_rekening=NULL ✅
User C: modern, nama_bank='BNI' ✅, nomor_rekening='123' ✅
```

---

Date: November 28, 2025
Status: ✅ FIXED & DOCUMENTED
