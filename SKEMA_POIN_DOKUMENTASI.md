# 📊 Dokumentasi Skema Poin Mendaur API

## Pendahuluan

Sistem Mendaur menggunakan **dual-point system** dengan dua field poin yang berbeda fungsinya:

| Field | Kegunaan | Kapan Bertambah | Kapan Berkurang | Bisa Reset? |
|-------|----------|-----------------|-----------------|-------------|
| `display_poin` | Leaderboard & ranking | ✅ Setor sampah, bonus, badge | ❌ TIDAK PERNAH berkurang | ✅ Ya (reset periodik) |
| `actual_poin` | Transaksi (withdrawal, redeem) | ✅ Setor sampah, bonus, badge, refund | ✅ Withdrawal, redeem produk | ❌ Tidak (saldo aktual) |

---

## 🎯 Aturan Utama

### 1. MENAMBAH POIN (Earning Points)
**Sumber:** Setor sampah, bonus event, badge reward, manual adjustment

```
✅ display_poin += amount  (untuk leaderboard)
✅ actual_poin += amount   (untuk saldo)
✅ Catat di poin_transaksis dengan poin_didapat POSITIF
```

### 2. MENGURANGI POIN (Spending Points)  
**Sumber:** Redeem produk, request penarikan tunai

```
❌ display_poin TIDAK BERUBAH (leaderboard tetap)
✅ actual_poin -= amount   (saldo berkurang)
✅ Catat di poin_transaksis dengan poin_didapat NEGATIF
```

### 3. REFUND POIN (Point Refund)
**Sumber:** Cancel redeem, reject withdrawal

```
❌ display_poin TIDAK BERUBAH (karena sebelumnya tidak dikurangi)
✅ actual_poin += amount   (saldo kembali)
✅ Catat di poin_transaksis dengan poin_didapat POSITIF (refund)
```

---

## 📋 Implementasi di Setiap Fitur

### A. Setor Sampah (TabungSampahController@approve)

```php
// ✅ BENAR: Tambah display_poin DAN actual_poin
$user->increment('display_poin', $poinDiberikan);
$user->increment('actual_poin', $poinDiberikan);

// Catat transaksi positif
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => $poinDiberikan,  // POSITIF
    'sumber' => 'setor_sampah',
    'keterangan' => "Setor {$berat}kg {$jenis}",
]);
```

### B. Penukaran Produk (PenukaranProdukController@store)

```php
// ✅ BENAR: Hanya kurangi actual_poin, display_poin TETAP
$user->decrement('actual_poin', $poinDigunakan);
// ❌ JANGAN: $user->decrement('display_poin', ...);

// Catat transaksi negatif
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => -$poinDigunakan,  // NEGATIF
    'sumber' => 'penukaran_produk',
    'keterangan' => "Penukaran produk: {$namaProduk}",
]);
```

### C. Cancel Penukaran Produk (PenukaranProdukController@cancel)

```php
// ✅ BENAR: Hanya tambah actual_poin (refund), display_poin TETAP
$user->increment('actual_poin', $poinDikembalikan);
// ❌ JANGAN: $user->increment('display_poin', ...);

// Catat transaksi positif (refund)
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => $poinDikembalikan,  // POSITIF (refund)
    'sumber' => 'refund_penukaran',
    'keterangan' => "Pengembalian poin dari penukaran yang dibatalkan",
]);
```

### D. Request Penarikan Tunai (PenarikanTunaiController@store)

```php
// ✅ BENAR: Hanya kurangi actual_poin, display_poin TETAP
$user->decrement('actual_poin', $jumlahPoin);
// ❌ JANGAN: $user->decrement('display_poin', ...);

// Catat transaksi negatif
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => -$jumlahPoin,  // NEGATIF
    'sumber' => 'penarikan_tunai',
    'keterangan' => "Penarikan tunai: {$jumlahPoin} poin",
]);
```

### E. Approve Penarikan Tunai (AdminPenarikanTunaiController@approve)

```php
// ✅ BENAR: Tidak ada perubahan poin
// Poin sudah dikurangi saat request (store)
// Hanya update status menjadi 'approved'
```

### F. Reject Penarikan Tunai (AdminPenarikanTunaiController@reject)

```php
// ✅ BENAR: Hanya tambah actual_poin (refund), display_poin TETAP
$user->increment('actual_poin', $jumlahPoin);
// ❌ JANGAN: $user->increment('display_poin', ...);

// Catat transaksi positif (refund)
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => $jumlahPoin,  // POSITIF (refund)
    'sumber' => 'pengembalian_penarikan',
    'keterangan' => "Pengembalian poin dari penarikan yang ditolak",
]);
```

### G. Bonus/Event Points (PointController@awardBonus)

```php
// ✅ BENAR: Tambah display_poin DAN actual_poin
$user->increment('display_poin', $bonusPoin);
$user->increment('actual_poin', $bonusPoin);

// Catat transaksi positif
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => $bonusPoin,  // POSITIF
    'sumber' => 'bonus',
    'keterangan' => $alasanBonus,
]);
```

### H. Badge Reward (BadgeService@checkAndAwardBadges)

```php
// ✅ BENAR: Tambah display_poin DAN actual_poin
$user->increment('display_poin', $rewardPoin);
$user->increment('actual_poin', $rewardPoin);

// Catat transaksi positif
PoinTransaksi::create([
    'user_id' => $user->user_id,
    'poin_didapat' => $rewardPoin,  // POSITIF
    'sumber' => 'badge_reward',
    'keterangan' => "Reward dari badge: {$namaBadge}",
]);
```

---

## 🔢 Leaderboard & Statistics

### Leaderboard (DashboardController@getLeaderboard)
```php
// ✅ BENAR: Gunakan display_poin untuk ranking
$users = User::orderBy('display_poin', 'desc')->get();
```

### User Balance (untuk fitur)
```php
// ✅ BENAR: Gunakan actual_poin untuk saldo yang bisa digunakan
$availablePoin = $user->actual_poin;
// atau gunakan method:
$availablePoin = $user->getUsablePoin();
```

### User Statistics
```php
// Total poin yang pernah didapat (untuk statistik)
$totalEarned = PoinTransaksi::where('user_id', $userId)
    ->where('poin_didapat', '>', 0)
    ->sum('poin_didapat');

// Total poin yang digunakan
$totalSpent = abs(PoinTransaksi::where('user_id', $userId)
    ->where('poin_didapat', '<', 0)
    ->sum('poin_didapat'));
```

---

## 📊 Tabel Referensi Cepat

| Aksi | display_poin | actual_poin | poin_transaksis |
|------|--------------|-------------|-----------------|
| Setor Sampah Approved | +X | +X | +X |
| Bonus Event | +X | +X | +X |
| Badge Reward | +X | +X | +X |
| Request Withdrawal | 0 | -X | -X |
| Withdrawal Approved | 0 | 0 | 0 |
| Withdrawal Rejected | 0 | +X (refund) | +X |
| Redeem Produk | 0 | -X | -X |
| Cancel Redeem | 0 | +X (refund) | +X |
| Reset Leaderboard | Reset ke 0 | 0 | 0 |

---

## ⚠️ Kesalahan Umum yang Harus Dihindari

### ❌ SALAH: Mengurangi display_poin saat spending
```php
// JANGAN LAKUKAN INI!
$user->decrement('display_poin', $amount);
$user->decrement('actual_poin', $amount);
```

### ❌ SALAH: Menambah display_poin saat refund
```php
// JANGAN LAKUKAN INI!
$user->increment('display_poin', $amount); // SALAH untuk refund
$user->increment('actual_poin', $amount);  // INI BENAR
```

### ❌ SALAH: Menggunakan actual_poin untuk leaderboard
```php
// JANGAN LAKUKAN INI!
$users = User::orderBy('actual_poin', 'desc')->get();
// GUNAKAN display_poin
```

---

## 🔄 Sumber Transaksi (sumber field)

| Sumber | Keterangan | poin_didapat |
|--------|------------|--------------|
| `setor_sampah` | Poin dari setor sampah | POSITIF |
| `bonus` | Bonus event/promo | POSITIF |
| `badge_reward` | Reward dari badge | POSITIF |
| `event` | Event khusus | POSITIF |
| `manual` | Adjustment manual oleh admin | POSITIF/NEGATIF |
| `penukaran_produk` | Redeem produk | NEGATIF |
| `refund_penukaran` | Refund dari cancel redeem | POSITIF |
| `penarikan_tunai` | Request withdrawal | NEGATIF |
| `pengembalian_penarikan` | Refund dari reject withdrawal | POSITIF |

---

## 📝 Catatan Tambahan

1. **Nasabah Modern**: `getUsablePoin()` selalu return 0 karena tidak bisa menggunakan fitur poin
2. **Sinkronisasi**: Jika `actual_poin` tidak sinkron, panggil `$user->updateActualPoin()`
3. **Reset Periodik**: `display_poin` bisa di-reset untuk leaderboard bulanan/tahunan
4. **Audit Trail**: Semua perubahan poin HARUS dicatat di `poin_transaksis`

---

*Dokumentasi dibuat: 1 Januari 2026*
*Versi: 1.0*
