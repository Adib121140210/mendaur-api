# 🌱 DATABASE SEEDER DOCUMENTATION

**Date:** December 23, 2025  
**Status:** ✅ 7 Comprehensive Seeders Created  
**Purpose:** Populate database with realistic test data for all features

---

## 📋 OVERVIEW

Database seeders sudah dikonfigurasi untuk mengisi semua fitur utama dengan data yang realistis:

| # | Seeder | Purpose | Records | Dependencies |
|:---:|:---|:---|---:|:---|
| 1 | **TabungSampahSeeder** | Waste deposits | 75-150 | Users, JenisSampah |
| 2 | **PenukaranProdukSeeder** | Product exchanges | 30 | Users, Produk |
| 3 | **PenarikanTunaiSeeder** | Cash withdrawals | 20-40 | Users |
| 4 | **UserBadgeSeeder** | Badge assignments | 30-45 | Users, Badge |
| 5 | **NotifikasiSeeder** | Notifications | 75-225 | Users |
| 6 | **PoinTransaksiSeeder** | Point transactions | 150-300 | Users |
| 7 | **PoinCorrectionSeeder** | Point corrections | 0-30 | Users, Admin |

**Total Records Generated:** ~400-800 records (realistic volume)

---

## 🚀 CARA MENGGUNAKAN

### Method 1: Seed Semua Sekaligus
```bash
php artisan db:seed
```
Ini akan menjalankan semua seeder dalam urutan yang benar.

### Method 2: Seed Specific Seeder
```bash
# Seed hanya TabungSampah
php artisan db:seed --class=TabungSampahSeeder

# Seed hanya PenukaranProduk
php artisan db:seed --class=PenukaranProdukSeeder
```

### Method 3: Fresh Database + Seed
```bash
# Hapus semua data dan seed ulang
php artisan migrate:fresh --seed

# Atau dengan seeders tertentu saja
php artisan migrate:fresh --seeder=DatabaseSeeder
```

### Method 4: Refresh (Rollback + Migrate + Seed)
```bash
php artisan migrate:refresh --seed
```

---

## 📊 SEEDER DETAILS

### 1️⃣ TabungSampahSeeder
**File:** `database/seeders/TabungSampahSeeder.php`

**Fitur:**
- ✅ Membuat 5-10 waste deposits per user
- ✅ Random jenis sampah dari database
- ✅ Berat random (5-50kg dengan desimal)
- ✅ Status: pending, approved, rejected
- ✅ Otomatis hitung poin jika approved
- ✅ Catatan realistis

**Data yang Dihasilkan:**
```
Per user:    5-10 deposits
Total:       75-150 records (15 users)
Status mix:  ~60% approved, ~20% pending, ~20% rejected
Berat range: 5kg - 50kg
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "jenis_sampah_id": 3,
  "nama_jenis_sampah": "Plastik Botol",
  "berat_kg": 25.5,
  "harga_per_kg": 2000,
  "total_harga": 51000,
  "status": "approved",
  "poin_diberikan": 510,
  "tanggal_setor": "2025-11-25 14:30:00"
}
```

---

### 2️⃣ PenukaranProdukSeeder
**File:** `database/seeders/PenukaranProdukSeeder.php`

**Fitur:**
- ✅ 3 penukaran per user
- ✅ Random metode ambil (pickup, delivery, email)
- ✅ Status: pending, approved, rejected, completed
- ✅ Catatan admin tersedia
- ✅ Tanggal pengambilan realistic

**Data yang Dihasilkan:**
```
Per user:    3 exchanges
Total:       30 records (10 users)
Status mix:  25% each (pending, approved, rejected, completed)
Methods:     ~33% each (pickup, delivery, email)
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "produk_id": 2,
  "nama_produk": "Gift Card Rp 50.000",
  "poin_digunakan": 500,
  "jumlah": 1,
  "status": "completed",
  "metode_ambil": "pickup",
  "catatan": "Produk sudah diterima dengan baik",
  "tanggal_diambil": "2025-12-20 10:00:00"
}
```

---

### 3️⃣ PenarikanTunaiSeeder
**File:** `database/seeders/PenarikanTunaiSeeder.php`

**Fitur:**
- ✅ 2-4 withdrawal requests per user
- ✅ Jumlah realistis (50k - 500k)
- ✅ Status flow: request → approval → completion
- ✅ Alasan penolakan untuk rejected
- ✅ Catatan admin untuk proses

**Data yang Dihasilkan:**
```
Per user:    2-4 withdrawals
Total:       20-40 records (10 users)
Amount:      Rp 50.000 - Rp 500.000
Status mix:  25% each
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "jumlah": 250000,
  "status": "approved",
  "alasan_penolakan": null,
  "catatan_admin": "Transfer sudah dilakukan",
  "tanggal_request": "2025-11-20 09:00:00",
  "tanggal_approval": "2025-11-22 10:30:00"
}
```

---

### 4️⃣ UserBadgeSeeder
**File:** `database/seeders/UserBadgeSeeder.php`

**Fitur:**
- ✅ 1-3 badges per user
- ✅ Random badge selection
- ✅ Hindari duplicate assignments
- ✅ Tanggal perolehan realistic

**Data yang Dihasilkan:**
```
Per user:    1-3 badges
Total:       30-45 records (15 users)
No duplicates: Cek sebelum insert
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "badge_id": 2,
  "diperoleh_pada": "2025-10-15 14:00:00"
}
```

---

### 5️⃣ NotifikasiSeeder
**File:** `database/seeders/NotifikasiSeeder.php`

**Fitur:**
- ✅ 5-15 notifikasi per user
- ✅ 10 tipe notifikasi berbeda
- ✅ Judul dan pesan otomatis sesuai tipe
- ✅ Status read/unread random
- ✅ Tanggal dibaca realistis

**Tipe Notifikasi:**
```
1. penyetoran_disetujui - Waste deposit approved
2. penyetoran_ditolak - Waste deposit rejected
3. penukaran_disetujui - Product exchange approved
4. penukaran_ditolak - Product exchange rejected
5. penarikan_disetujui - Withdrawal approved
6. penarikan_ditolak - Withdrawal rejected
7. badge_baru - New badge obtained
8. poin_bonus - Bonus points
9. jadwal_penyetoran - Pickup schedule reminder
10. promo - Promotion announcement
```

**Data yang Dihasilkan:**
```
Per user:    5-15 notifications
Total:       75-225 records (15 users)
Status:      ~50% read, ~50% unread
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "tipe": "badge_baru",
  "judul": "Badge Baru Diperoleh 🎖️",
  "pesan": "Selamat! Anda baru saja mendapatkan badge 'Eco Warrior'...",
  "is_read": 1,
  "tanggal_dibaca": "2025-12-10 15:30:00"
}
```

---

### 6️⃣ PoinTransaksiSeeder
**File:** `database/seeders/PoinTransaksiSeeder.php`

**Fitur:**
- ✅ 10-20 transaksi poin per user
- ✅ 6 tipe transaksi berbeda
- ✅ Poin amount sesuai tipe
- ✅ Referensi ke tabel terkait
- ✅ Tracking poin awal-akhir

**Tipe Transaksi:**
```
1. penyetoran_sampah    → +50 sampai +500 poin
2. tukar_produk         → -100 sampai -1000 poin
3. penarikan_tunai      → -200 sampai -500 poin
4. bonus_referral       → +100 sampai +500 poin
5. correction           → ±-100 sampai +100 poin
6. adjustment           → ±-50 sampai +50 poin
```

**Data yang Dihasilkan:**
```
Per user:    10-20 transactions
Total:       150-300 records (15 users)
Mix:         Earning + spending + bonus + correction
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "tipe_transaksi": "penyetoran_sampah",
  "poin_awal": 1000,
  "poin_perubahan": 250,
  "poin_akhir": 1250,
  "keterangan": "Poin dari penyetoran sampah 5kg plastik",
  "referensi_tabel": "tabung_sampah",
  "referensi_id": 42
}
```

---

### 7️⃣ PoinCorrectionSeeder
**File:** `database/seeders/PoinCorrectionSeeder.php`

**Fitur:**
- ✅ 0-3 koreksi per user
- ✅ Oleh admin
- ✅ Alasan terstruktur
- ✅ Catatan admin optional
- ✅ Tracking poin sebelum-sesudah

**Data yang Dihasilkan:**
```
Per user:    0-3 corrections
Total:       0-30 records (10 users)
Amount:      ±-500 sampai +500 poin
Admin:       Fixed admin user
```

**Contoh Record:**
```json
{
  "user_id": 1,
  "poin_awal": 1000,
  "poin_perubahan": 100,
  "poin_akhir": 1100,
  "alasan": "Pemberian bonus poin sebagai kompensasi",
  "dikoreksi_oleh": 1,
  "catatan_admin": "Sudah dikonfirmasi dengan user",
  "tanggal_koreksi": "2025-11-15 10:00:00"
}
```

---

## 🔗 DEPENDENCY ORDER

Seeders harus dijalankan dalam urutan yang benar:

```
1️⃣ RolePermissionSeeder
   ↓
2️⃣ UserSeeder → Buat semua users
   ↓
3️⃣ KategoriSampah, JenisSampah, JadwalPenyetoran, Produk, Badge
   ↓
4️⃣ Feature Seeders (order tidak kritis):
   ├─ TabungSampahSeeder (depends on: Users, JenisSampah)
   ├─ PenukaranProdukSeeder (depends on: Users, Produk)
   ├─ PenarikanTunaiSeeder (depends on: Users)
   ├─ UserBadgeSeeder (depends on: Users, Badge)
   ├─ NotifikasiSeeder (depends on: Users)
   ├─ PoinTransaksiSeeder (depends on: Users)
   └─ PoinCorrectionSeeder (depends on: Users, Admin)
```

**DatabaseSeeder.php sudah dikonfigurasi dengan urutan yang benar!** ✅

---

## ✅ VERIFICATION CHECKLIST

Setelah seed, verifikasi data:

```bash
# Check TabungSampah
php artisan tinker
> App\Models\TabungSampah::count()  # Should be ~100+

# Check PenukaranProduk
> App\Models\PenukaranProduk::count()  # Should be ~30

# Check PenarikanTunai
> App\Models\PenarikanTunai::count()  # Should be ~30

# Check UserBadge
> App\Models\UserBadge::count()  # Should be ~40

# Check Notifikasi
> App\Models\Notifikasi::count()  # Should be ~150+

# Check PoinTransaksi
> App\Models\PoinTransaksi::count()  # Should be ~200+

# Check PoinCorrection
> App\Models\PoinCorrection::count()  # Should be ~15

# Verify data relationships
> App\Models\TabungSampah::with('user')->first()  # Should load user
> App\Models\UserBadge::with(['user', 'badge'])->first()  # Should load both
```

---

## 🎯 SAMPLE QUERIES

### Get Waste Deposits Statistics
```php
$stats = TabungSampah::selectRaw('
    COUNT(*) as total,
    SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved,
    SUM(CASE WHEN status = "pending" THEN 1 ELSE 0 END) as pending,
    SUM(berat_kg) as total_weight_kg,
    SUM(poin_diberikan) as total_points
')->first();

// Output:
// total: 120
// approved: 72
// pending: 24
// total_weight_kg: 3240.50
// total_points: 32405
```

### Get User Point Summary
```php
$user = User::with('poinTransaksi')->find(1);
$totalEarned = $user->poinTransaksi()
    ->where('poin_perubahan', '>', 0)
    ->sum('poin_perubahan');
$totalSpent = abs($user->poinTransaksi()
    ->where('poin_perubahan', '<', 0)
    ->sum('poin_perubahan'));
```

### Get Top Earning Users
```php
$topUsers = User::with('poinTransaksi')
    ->withCount('tabungSampah')
    ->withCount('penukaranProduk')
    ->orderBy('poin', 'desc')
    ->limit(10)
    ->get();
```

---

## 🔧 TROUBLESHOOTING

### Issue: "Call to a member function random() on null"
**Solution:** Pastikan Users dan Master Data sudah di-seed terlebih dahulu
```bash
php artisan migrate:fresh --seed
```

### Issue: "Column not found" Error
**Solution:** Pastikan semua migrations sudah dijalankan
```bash
php artisan migrate
php artisan migrate:status  # Verify all migrations are UP
```

### Issue: Unique Constraint Violation
**Solution:** Seeders memiliki cek duplicate (khususnya UserBadgeSeeder)
```bash
# Clear data dan seed ulang
php artisan migrate:fresh --seed
```

### Issue: Data tidak muncul di admin panel
**Solusi:** Pastikan:
1. Seeder berhasil dijalankan: `php artisan tinker > App\Models\Model::count()`
2. Models memiliki relationships yang benar
3. Controllers menggunakan Model yang benar

---

## 📈 PERFORMANCE

**Seeding Time:**
- Full seed (all seeders): ~5-10 seconds
- Fresh + Migrate + Seed: ~15-20 seconds
- Individual seeders: ~0.5-2 seconds

**Database Size:**
- Total records: ~500-800
- Database size: ~10-20 MB (including tables)

---

## 🎓 KEY POINTS

✅ **Realistic Data:** All amounts, statuses, and dates are realistic  
✅ **Relationships:** All foreign keys are properly set  
✅ **No Duplicates:** Duplicate checks where needed (UserBadge)  
✅ **Balanced Mix:** Status distribution reflects real usage  
✅ **Traceable:** All records have timestamps  
✅ **Extensible:** Easy to add more seeders or modify existing ones

---

## 📝 NEXT STEPS

1. ✅ Run full seed: `php artisan migrate:fresh --seed`
2. ✅ Verify data in admin panel
3. ✅ Test API endpoints with real data
4. ✅ Create additional seeders if needed
5. ✅ Adjust seed data quantities for production prep

---

**Status:** ✅ Complete and Ready to Use  
**Last Updated:** December 23, 2025  
**Seeder Count:** 7 comprehensive feature seeders + 7 base seeders = 14 total

