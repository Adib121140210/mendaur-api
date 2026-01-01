# Perbaikan Skema Poin di Controller

## Tanggal: Januari 2026

## Ringkasan Masalah

Beberapa controller dan service langsung memanipulasi field `actual_poin` dan `display_poin` tanpa menggunakan `PointService`, yang menyebabkan inkonsistensi data poin.

### Aturan Skema Poin (PENTING!)

| Field | Fungsi | Naik Saat | Turun Saat |
|-------|--------|-----------|------------|
| `display_poin` | Leaderboard/ranking | Dapat poin | **TIDAK PERNAH** |
| `actual_poin` | Saldo untuk transaksi | Dapat poin | Spend (withdraw/redeem) |

---

## File yang Diperbaiki

### 1. `AdminPenukaranProdukController.php`

**Masalah:**
- `approve()`: Salah decrement `display_poin` saat approve
- `reject()`: Salah increment `display_poin` saat refund

**Perbaikan:**
```php
// SEBELUM (SALAH)
$user->decrement('actual_poin', $exchange->poin_digunakan);
$user->decrement('display_poin', $exchange->poin_digunakan); // ❌ SALAH!

// SESUDAH (BENAR)
// approve() - poin sudah di-hold saat nasabah submit, tidak perlu deduct lagi
// reject() - gunakan PointService::refundRedemptionPoints()
PointService::refundRedemptionPoints($user, $poin, $id, "Alasan refund");
```

### 2. `AdminPointsController.php`

**Masalah:**
- `award()`: Hanya insert ke tabel transaksi, TIDAK update user poin!

**Perbaikan:**
```php
// SEBELUM (SALAH)
DB::table('poin_transaksis')->insert([...]); // Tidak update user poin!

// SESUDAH (BENAR)
PointService::earnPoints(
    $user,
    $request->points,
    $request->category . '_admin',
    $request->reason,
    null,
    'AdminAward'
);
```

### 3. `AdminWasteController.php`

**Masalah:**
- `approve()`: Langsung increment semua field termasuk `poin_tercatat`

**Perbaikan:**
```php
// SEBELUM
$user->increment('actual_poin', $poin);
$user->increment('display_poin', $poin);
$user->increment('poin_tercatat', $poin); // Field redundan

// SESUDAH (BENAR)
PointService::earnPoints(
    $user,
    $request->poin_diberikan,
    'admin_approve_deposit',
    'Poin dari persetujuan penyetoran sampah oleh admin',
    $deposit->tabung_sampah_id,
    'TabungSampah'
);
```

### 4. `DualNasabahFeatureAccessService.php`

**Masalah:**
- `addPoinForDeposit()`: Langsung manipulasi poin
- `deductPoinForRedemption()`: Langsung decrement tanpa PointService
- `deductPoinForWithdrawal()`: Langsung decrement tanpa PointService

**Perbaikan:**
- Sekarang menggunakan `PointService` untuk semua operasi poin
- Logika khusus untuk modern nasabah tetap dipertahankan:
  - Modern: hanya tambah `display_poin` (tidak bisa spend)
  - Konvensional: gunakan `PointService` standar

### 5. `PoinCorrectionController.php` (Tidak Diubah)

Controller ini untuk **koreksi manual oleh superadmin**. Boleh update kedua field secara langsung karena:
- Ini adalah override administratif
- Digunakan untuk perbaikan data/fraud prevention
- Ada audit log yang mencatat semua perubahan

---

## Controller yang Sudah Benar

Berikut controller yang sudah menggunakan `PointService` dengan benar:

1. ✅ `TabungSampahController.php` - `PointService::applyDepositPoints()`
2. ✅ `PenarikanTunaiController.php` - `PointService::deductPointsForWithdrawal()`
3. ✅ `PenukaranProdukController.php` - `PointService::deductPointsForRedemption()`, `refundRedemptionPoints()`
4. ✅ `AdminPenarikanTunaiController.php` - `PointService::refundWithdrawalPoints()`
5. ✅ `PointController.php` - Hanya read, return kedua field
6. ✅ `DashboardController.php` - Gunakan `display_poin` untuk leaderboard
7. ✅ `AdminLeaderboardController.php` - Gunakan `display_poin` untuk ranking

---

## Panduan untuk Developer

### WAJIB Gunakan PointService untuk:

| Operasi | Method |
|---------|--------|
| User dapat poin | `PointService::earnPoints()` |
| User spend poin (redeem) | `PointService::deductPointsForRedemption()` |
| User spend poin (withdraw) | `PointService::deductPointsForWithdrawal()` |
| Refund poin redemption | `PointService::refundRedemptionPoints()` |
| Refund poin withdrawal | `PointService::refundWithdrawalPoints()` |

### JANGAN Langsung:

```php
// ❌ JANGAN LAKUKAN INI
$user->increment('actual_poin', $amount);
$user->increment('display_poin', $amount);
$user->decrement('display_poin', $amount); // SALAH! display_poin tidak boleh turun

// ✅ GUNAKAN INI
PointService::earnPoints($user, $amount, 'sumber', 'keterangan', $refId, 'RefType');
PointService::spendPoints($user, $amount, 'tujuan', $refId, 'RefType', 'keterangan');
```

---

## Kesimpulan

Semua controller dan service yang berkaitan dengan poin sekarang menggunakan `PointService` untuk memastikan:

1. **Konsistensi data** - Tidak ada manipulasi langsung yang bisa merusak data
2. **Audit trail** - Semua transaksi dicatat di `poin_transaksis`
3. **Aturan bisnis** - `display_poin` tidak pernah berkurang

---

*Dokumen ini dibuat otomatis setelah perbaikan skema poin di controller.*
