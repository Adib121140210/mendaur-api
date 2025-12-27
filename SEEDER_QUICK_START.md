# 🌱 SEEDER QUICK START GUIDE

**Created:** December 23, 2025  
**Status:** ✅ Ready to Use  
**Total Seeders:** 7 new feature seeders

---

## ⚡ QUICK START (5 MENIT)

### Langkah 1: Fresh Database + Seed
```bash
php artisan migrate:fresh --seed
```

**Output yang diharapkan:**
```
Seeding database...
✅ RolePermissionSeeder berhasil dijalankan
✅ UserSeeder berhasil dijalankan
✅ KategoriSampahSeeder berhasil dijalankan
✅ JenisSampahSeeder berhasil dijalankan
✅ TabungSampahSeeder berhasil dijalankan (Total approved: 72)
✅ PenukaranProdukSeeder berhasil dijalankan
✅ PenarikanTunaiSeeder berhasil dijalankan
✅ UserBadgeSeeder berhasil dijalankan
✅ NotifikasiSeeder berhasil dijalankan (Total: 150 notifikasi)
✅ PoinTransaksiSeeder berhasil dijalankan (Total: 225 transaksi)
✅ PoinCorrectionSeeder berhasil dijalankan (Total: 18 koreksi)
```

### Langkah 2: Verifikasi Data
```bash
php artisan tinker
> App\Models\TabungSampah::count()       # ~120
> App\Models\PenukaranProduk::count()    # ~30
> App\Models\PenarikanTunai::count()     # ~30
> App\Models\UserBadge::count()          # ~40
> App\Models\Notifikasi::count()         # ~150+
> App\Models\PoinTransaksi::count()      # ~225+
> exit
```

### Langkah 3: Test API
```bash
php artisan serve
# Buka: http://localhost:8000/api/admin/penyetoran-sampah
# Login terlebih dahulu jika diperlukan
```

---

## 📊 SEEDER SUMMARY

| Seeder | Records | Description |
|:---|---:|:---|
| **TabungSampahSeeder** | 75-150 | Waste deposits dengan approval workflow |
| **PenukaranProdukSeeder** | 30 | Product exchanges dengan berbagai status |
| **PenarikanTunaiSeeder** | 20-40 | Cash withdrawals dengan approval/rejection |
| **UserBadgeSeeder** | 30-45 | Badge assignments ke users |
| **NotifikasiSeeder** | 75-225 | 10 tipe notifikasi untuk users |
| **PoinTransaksiSeeder** | 150-300 | Point transactions (earn/spend/bonus) |
| **PoinCorrectionSeeder** | 0-30 | Admin corrections ke user points |
| **TOTAL** | **~400-800** | Realistic test data for all features |

---

## 🎯 FITUR SEEDER

### ✅ TabungSampahSeeder
Generates waste deposit data dengan:
- Random jenis sampah
- Berat 5-50kg dengan desimal
- Status: pending/approved/rejected (random)
- Otomatis hitung poin jika approved
- Realistic dates (1-60 hari lalu)

```php
// Jalankan hanya seeder ini:
php artisan db:seed --class=TabungSampahSeeder
```

### ✅ PenukaranProdukSeeder
Generates product exchange data dengan:
- Random produk dari database
- Status flow realistic
- Metode ambil: pickup/delivery/email
- Tanggal diambil untuk completed items

```php
php artisan db:seed --class=PenukaranProdukSeeder
```

### ✅ PenarikanTunaiSeeder
Generates cash withdrawal requests dengan:
- Jumlah: Rp 50k - 500k (realistic)
- Status: pending/approved/rejected/completed
- Alasan penolakan untuk rejected
- Catatan admin untuk tracking

```php
php artisan db:seed --class=PenarikanTunaiSeeder
```

### ✅ UserBadgeSeeder
Assigns badges ke users dengan:
- 1-3 badges per user
- No duplicate assignments
- Random dari existing badges
- Realistic dates (1-60 hari lalu)

```php
php artisan db:seed --class=UserBadgeSeeder
```

### ✅ NotifikasiSeeder
Creates notifications dengan:
- 5-15 notifications per user
- 10 tipe notifikasi berbeda
- Auto-generated judul dan pesan sesuai tipe
- Status read/unread random
- Tanggal dibaca realistic

**10 Tipe Notifikasi:**
1. penyetoran_disetujui ✅
2. penyetoran_ditolak ❌
3. penukaran_disetujui ✅
4. penukaran_ditolak ❌
5. penarikan_disetujui ✅
6. penarikan_ditolak ❌
7. badge_baru 🎖️
8. poin_bonus 🎁
9. jadwal_penyetoran 📅
10. promo 🎉

```php
php artisan db:seed --class=NotifikasiSeeder
```

### ✅ PoinTransaksiSeeder
Tracks point transactions dengan:
- 10-20 transaksi per user
- 6 tipe transaksi (earn/spend/bonus/correction)
- Amount sesuai tipe transaksi
- Referensi ke tabel terkait
- Tracking poin awal-akhir

**Tipe Transaksi:**
- penyetoran_sampah: +50-500 poin
- tukar_produk: -100-1000 poin
- penarikan_tunai: -200-500 poin
- bonus_referral: +100-500 poin
- correction: ±100 poin
- adjustment: ±50 poin

```php
php artisan db:seed --class=PoinTransaksiSeeder
```

### ✅ PoinCorrectionSeeder
Admin corrections untuk points dengan:
- 0-3 koreksi per user
- Oleh admin user
- 7 alasan terstruktur
- Catatan admin optional
- Tracking sebelum-sesudah

```php
php artisan db:seed --class=PoinCorrectionSeeder
```

---

## 🔄 COMMON COMMANDS

```bash
# 1. Fresh database + seed semua
php artisan migrate:fresh --seed

# 2. Seed ulang tanpa reset database
php artisan migrate:refresh --seed

# 3. Seed seeder tertentu saja
php artisan db:seed --class=TabungSampahSeeder

# 4. Lihat status migrations
php artisan migrate:status

# 5. Rollback migrations
php artisan migrate:rollback

# 6. Tinker untuk explore data
php artisan tinker
> App\Models\TabungSampah::with('user', 'jenisSampah')->first()
```

---

## 📈 DATA STATISTICS

Setelah seed, database akan memiliki:

```
Users:               ~20+ (dari UserSeeder)
TabungSampah:        ~120 (75-150)
PenukaranProduk:     ~30
PenarikanTunai:      ~30
UserBadge:           ~40
Notifikasi:          ~150+
PoinTransaksi:       ~225+
PoinCorrection:      ~18
```

---

## ✅ VERIFICATION CHECKLIST

- [ ] `php artisan migrate:fresh --seed` berhasil
- [ ] Semua seeder menampilkan "✅" message
- [ ] Database memiliki ~400+ records
- [ ] Admin panel menampilkan data
- [ ] API endpoints mengembalikan data realistis
- [ ] User badges assignments visible
- [ ] Notifications menampilkan berbagai tipe
- [ ] Point transactions tercatat dengan benar

---

## 🎯 USE CASES

### Testing Admin Dashboard
```bash
php artisan migrate:fresh --seed
php artisan serve
# Visit: http://localhost:8000/admin/penyetoran-sampah
# See: 120+ waste deposits dengan berbagai status
```

### Testing Notification System
```bash
php artisan migrate:fresh --seed
# Admin dapat: GET /api/admin/notifications
# Return: 150+ notifications dengan 10 tipe berbeda
```

### Testing Point System
```bash
php artisan migrate:fresh --seed
# Check: /api/admin/analytics/points
# See: Earning pattern dari waste deposits
# See: Spending pattern dari product exchanges
```

### Testing Badge System
```bash
php artisan migrate:fresh --seed
# Check: /api/admin/badges
# See: Users dengan badges yang telah assigned
```

---

## 🔧 TROUBLESHOOTING

**Q: Migration error "Column not found"**  
A: Pastikan semua migrations berjalan: `php artisan migrate:status`

**Q: "Call to a member function random() on null"**  
A: Users belum di-seed. Jalankan: `php artisan migrate:fresh --seed`

**Q: Data tidak muncul di admin panel**  
A: 1. Cek relationships di Model  
   2. Cek controller query  
   3. Verify: `App\Models\Model::count()` di tinker

**Q: Seeder crash di tengah jalan**  
A: Check error message, fix, kemudian:  
   `php artisan migrate:fresh --seed`

---

## 📝 MODIFYING SEEDERS

### Menambah records
Edit di seeder file:
```php
for ($i = 0; $i < rand(5, 10); $i++) {  // Change range here
    // ...
}
```

### Mengubah status distribution
```php
$statuses = ['pending', 'approved', 'rejected'];  // Add more status
```

### Mengubah amount range
```php
$jumlahOptions = [50000, 100000, 150000, 200000, 250000, 500000];
// Add or modify amounts
```

---

## 🎓 LEARNING RESOURCES

File dokumentasi lengkap tersedia di:  
📄 `DATABASE_SEEDER_DOCUMENTATION.md`

Berisi:
- Detailed seeder explanations
- Sample queries
- Performance metrics
- Advanced usage
- Best practices

---

## 📊 QUICK STATS AFTER SEEDING

```sql
-- Total records by type
SELECT 'tabung_sampah' as type, COUNT(*) as count FROM tabung_sampah
UNION SELECT 'penukaran_produk', COUNT(*) FROM penukaran_produk
UNION SELECT 'penarikan_tunai', COUNT(*) FROM penarikan_tunai
UNION SELECT 'user_badges', COUNT(*) FROM user_badges
UNION SELECT 'notifikasi', COUNT(*) FROM notifikasi
UNION SELECT 'poin_transaksi', COUNT(*) FROM poin_transaksi
UNION SELECT 'poin_correction', COUNT(*) FROM poin_corrections;

-- Result:
-- tabung_sampah: 120
-- penukaran_produk: 30
-- penarikan_tunai: 30
-- user_badges: 40
-- notifikasi: 150
-- poin_transaksi: 225
-- poin_correction: 18
-- TOTAL: 613 records
```

---

**Status:** ✅ Ready to Use  
**Version:** 1.0  
**Date:** December 23, 2025

