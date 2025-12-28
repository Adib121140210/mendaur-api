# 📋 API Changes Report - Frontend Integration Guide

**Tanggal:** 28 Desember 2025  
**Status:** CRITICAL - Requires Frontend Updates  
**Severity:** Breaking Changes

---

## 🚨 BREAKING CHANGES - Database Schema

### Kolom `total_poin` Telah Dihapus

Kolom `total_poin` pada tabel `users` **TIDAK ADA LAGI**. Diganti dengan 3 kolom terpisah:

| Kolom Lama | Kolom Baru | Fungsi |
|------------|------------|---------|
| `total_poin` | `actual_poin` | Saldo poin yang **bisa digunakan** (withdrawal/redemption) |
| `total_poin` | `display_poin` | Poin untuk **ranking leaderboard** (bisa di-reset) |
| `total_poin` | `poin_tercatat` | Total poin **tercatat** (untuk badge progress, tidak bisa di-reset) |

---

## 📊 Perubahan Response API

### 1. **Auth/Login Response**
**Endpoint:** `POST /api/auth/login`, `POST /api/auth/register`

#### ❌ Response Lama:
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "total_poin": 1500,          // ❌ TIDAK ADA LAGI
    "total_poin_display": 1500   // ❌ TIDAK ADA LAGI
  }
}
```

#### ✅ Response Baru:
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "actual_poin": 1500,      // ✅ NEW - Saldo yang bisa dipakai
    "display_poin": 1500,     // ✅ NEW - Poin untuk leaderboard
    "poin_tercatat": 2000     // ✅ Optional - Total tercatat (implicit)
  }
}
```

**Action Required:**
- Update state management untuk menggunakan `actual_poin` dan `display_poin`
- Ganti semua referensi `total_poin` menjadi `actual_poin`

---

### 2. **User Profile/Dashboard**
**Endpoint:** `GET /api/user`, `GET /api/user/{id}`

#### ❌ Response Lama:
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "total_poin": 1500
  }
}
```

#### ✅ Response Baru:
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "actual_poin": 1500,      // Untuk transaksi
    "display_poin": 1500      // Untuk ditampilkan
  }
}
```

**Action Required:**
- Update komponen user profile
- Ganti display `total_poin` → `display_poin`
- Untuk transaksi (withdrawal/redemption), gunakan `actual_poin`

---

### 3. **Point History/Transactions**
**Endpoint:** `GET /api/user/{id}/points`

#### ✅ Response Baru:
```json
{
  "status": "success",
  "data": {
    "user": {
      "user_id": 7,
      "nama": "User Name",
      "actual_poin": 1500,      // ✅ NEW
      "display_poin": 1500,     // ✅ NEW
      "level": "Gold"
    },
    "recent_transactions": [...],
    "statistics": {...}
  }
}
```

**Action Required:**
- Update UI untuk menampilkan `actual_poin` sebagai saldo utama
- Display `display_poin` untuk ranking/leaderboard

---

### 4. **Leaderboard**
**Endpoint:** `GET /api/leaderboard`

#### Response Structure (Tidak Berubah, tapi field internal berubah):
```json
{
  "leaderboard": [
    {
      "rank": 1,
      "user_id": 5,
      "nama": "Top User",
      "poin": 5000,           // Ini dari display_poin
      "total_setor_sampah": 50
    }
  ]
}
```

**Note:** Backend secara otomatis menggunakan `display_poin` untuk leaderboard. Frontend tidak perlu perubahan selama menggunakan field `poin`.

---

### 5. **Admin - User Management**
**Endpoint:** `GET /api/admin/users`, `GET /api/admin/users/{id}`

#### ❌ Response Lama:
```json
{
  "users": [
    {
      "user_id": 1,
      "nama": "John Doe",
      "total_poin": 1500
    }
  ]
}
```

#### ✅ Response Baru:
```json
{
  "users": [
    {
      "user_id": 1,
      "nama": "John Doe",
      "actual_poin": 1500,      // ✅ NEW
      "display_poin": 1500      // ✅ NEW
    }
  ]
}
```

**Action Required:**
- Update tabel admin untuk menampilkan kedua kolom
- Kolom "Saldo" → gunakan `actual_poin`
- Kolom "Poin Leaderboard" → gunakan `display_poin`

---

### 6. **Admin - Approve Waste Deposit**
**Endpoint:** `PATCH /api/admin/penyetoran-sampah/{id}/approve`

#### Request Body (TIDAK BERUBAH):
```json
{
  "poin_diberikan": 10000
}
```

#### Response (Backend Logic Changed):
- Backend sekarang update 3 field: `actual_poin`, `display_poin`, `poin_tercatat`
- Frontend tidak perlu perubahan, tapi harus refresh user data setelah approval

---

### 7. **Withdrawal (Penarikan Tunai)**
**Endpoint:** `POST /api/penarikan-tunai`

#### Request Body (TIDAK BERUBAH):
```json
{
  "jumlah_poin": 5000,
  "metode_penarikan": "transfer",
  "nomor_rekening": "1234567890"
}
```

**Important:** Backend sekarang menggunakan `actual_poin` untuk validasi. Pastikan frontend juga menampilkan `actual_poin` sebagai saldo yang tersedia.

---

### 8. **Product Redemption (Penukaran Produk)**
**Endpoint:** `POST /api/penukaran-produk`

#### Request Body (TIDAK BERUBAH):
```json
{
  "produk_id": 1,
  "jumlah": 1
}
```

**Important:** Backend menggunakan `actual_poin` untuk validasi. Frontend harus menampilkan `actual_poin` sebagai saldo yang bisa digunakan.

---

### 9. **Badge Progress**
**Endpoint:** `GET /api/user/badges/progress`

#### ✅ Response Baru:
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 7,
      "nama": "User Name",
      "poin_tercatat": 2000,    // ✅ NEW - untuk badge progress
      "total_setor": 30
    },
    "summary": {...},
    "completed_badges": [...],
    "in_progress_badges": [...]
  }
}
```

**Action Required:**
- Update badge progress display untuk menggunakan `poin_tercatat`

---

### 10. **Admin - Poin Correction**
**Endpoint:** `POST /api/admin/users/{userId}/correct-poin`

#### Request Body (TIDAK BERUBAH):
```json
{
  "new_poin": 15000,
  "reason": "Koreksi manual",
  "type": "correction"
}
```

#### Backend Behavior Changed:
- Sekarang mengupdate `actual_poin` DAN `display_poin` secara bersamaan
- Frontend harus refresh user data setelah koreksi

---

## 🔍 Field Comparison Summary

### User Object - Field Mapping

| Frontend Variable | Old API Field | New API Field | Usage |
|------------------|---------------|---------------|-------|
| `userBalance` | `total_poin` | `actual_poin` | Saldo untuk transaksi |
| `userPoints` | `total_poin` | `display_poin` | Untuk ditampilkan/leaderboard |
| `totalEarned` | - | `poin_tercatat` | Total tercatat (optional) |

---

## 📝 Frontend Migration Checklist

### 1. State Management (Redux/Zustand/Context)
- [ ] Update user state schema
  ```javascript
  // ❌ Old
  user: {
    total_poin: 1500
  }
  
  // ✅ New
  user: {
    actual_poin: 1500,
    display_poin: 1500
  }
  ```

### 2. API Client/Services
- [ ] Update API response types/interfaces
- [ ] Update mock data untuk testing

### 3. UI Components
- [ ] **Navbar/Header** - Display `display_poin` atau `actual_poin`
- [ ] **User Profile** - Show both `actual_poin` dan `display_poin`
- [ ] **Withdrawal Page** - Validate against `actual_poin`
- [ ] **Redemption Page** - Validate against `actual_poin`
- [ ] **Leaderboard** - Use `display_poin` (field `poin` dari API)
- [ ] **Admin User Table** - Show both columns
- [ ] **Badge Progress** - Use `poin_tercatat`

### 4. Form Validation
- [ ] Withdrawal form - Check `actual_poin >= jumlah_penarikan`
- [ ] Redemption form - Check `actual_poin >= harga_produk`

### 5. Display Logic
```javascript
// Recommended display logic
const displayBalance = user.actual_poin;        // Untuk saldo utama
const leaderboardPoints = user.display_poin;   // Untuk ranking
```

---

## 🎯 Recommended UI Display

### User Dashboard
```
┌─────────────────────────────────┐
│ 💰 Saldo Poin: 1,500           │  ← actual_poin
│ 🏆 Poin Ranking: 1,500          │  ← display_poin
│                                  │
│ [Tarik Tunai] [Tukar Hadiah]   │
└─────────────────────────────────┘
```

### Admin User Detail
```
┌─────────────────────────────────┐
│ User: John Doe                  │
│ Saldo Aktual: 1,500            │  ← actual_poin
│ Poin Leaderboard: 1,500        │  ← display_poin
│ Total Tercatat: 2,000          │  ← poin_tercatat (optional)
└─────────────────────────────────┘
```

---

## ⚠️ Common Pitfalls

### 1. **Mixing Fields**
```javascript
// ❌ WRONG
if (user.total_poin >= withdrawalAmount) { ... }

// ✅ CORRECT
if (user.actual_poin >= withdrawalAmount) { ... }
```

### 2. **Displaying Wrong Field**
```javascript
// ❌ WRONG - Showing actual_poin in leaderboard
<LeaderboardItem points={user.actual_poin} />

// ✅ CORRECT - Use display_poin for leaderboard
<LeaderboardItem points={user.display_poin} />
```

### 3. **Not Refreshing After Transactions**
After withdrawal, redemption, or admin approval, **always refetch user data** to get updated poin values.

---

## 🧪 Testing Scenarios

### Test Case 1: User Withdrawal
1. User has `actual_poin: 5000`
2. User withdraws 3000 poin
3. Expected: `actual_poin: 2000`, `display_poin: 2000`

### Test Case 2: Badge Reward (Konvensional)
1. User unlocks badge with 500 poin reward
2. Expected: `actual_poin += 500`, `display_poin += 500`

### Test Case 3: Admin Approval
1. Admin approves deposit with 10000 poin
2. Expected: All fields updated (`actual_poin`, `display_poin`, `poin_tercatat`)

### Test Case 4: Leaderboard Display
1. User A: `display_poin: 1000`
2. User B: `display_poin: 1500`
3. Expected: User B ranked higher

---

## 📞 Support & Questions

**Backend Team Contact:**
- GitHub: Adib121140210/mendaur-api
- Latest Commit: `636a8d5` - "fix: replace all total_poin references"

**Migration Deadline:** ASAP - Breaking changes deployed

**Rollback Plan:** No rollback available - schema migration already applied

---

## 🔗 Related Commits

1. `d1a665a` - Initial deployment fixes (emoji URL, artikel ID lookup)
2. `636a8d5` - Complete total_poin → actual_poin/display_poin migration
3. `f24b350` - Camera photo upload fix

---

## 📚 Additional Resources

### TypeScript Interface (Recommended)
```typescript
interface User {
  user_id: number;
  nama: string;
  email: string;
  actual_poin: number;      // Saldo untuk transaksi
  display_poin: number;     // Poin untuk leaderboard
  poin_tercatat?: number;   // Optional - total tercatat
  total_setor_sampah: number;
  level: string;
}
```

### React Hook Example
```typescript
const useUserBalance = () => {
  const user = useUser();
  
  return {
    availableBalance: user.actual_poin,    // Untuk transaksi
    displayPoints: user.display_poin,      // Untuk UI
    canWithdraw: (amount: number) => user.actual_poin >= amount,
    canRedeem: (price: number) => user.actual_poin >= price,
  };
};
```

---

**End of Report**
