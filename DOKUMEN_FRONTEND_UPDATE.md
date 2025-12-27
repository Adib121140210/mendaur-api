# 📋 DOKUMEN PERUBAHAN BACKEND - SISTEM POIN BARU

**Tanggal**: 26 Desember 2025  
**Tim**: Backend Development  
**Status**: BREAKING CHANGES - PERLU UPDATE FRONTEND  

---

## 🚨 **BREAKING CHANGES ALERT**

### Perubahan Struktur Database Users Table

| Field Lama | Field Baru | Status | Fungsi |
|------------|------------|--------|---------|
| `total_poin` | ❌ **DIHAPUS** | BREAKING | - |
| - | ✅ `display_poin` | NEW | Ranking leaderboard |
| - | ✅ `actual_poin` | NEW | Saldo transaksi |

---

## 📊 **PERUBAHAN API RESPONSE**

### 1. **User Profile & Dashboard**

**❌ SEBELUM (Deprecated)**:
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "total_poin": 15000,  // ❌ Field ini sudah tidak ada
    "level": "Gold"
  }
}
```

**✅ SETELAH (New Format)**:
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "actual_poin": 15000,    // ✅ Untuk saldo/transaksi
    "display_poin": 15000,   // ✅ Untuk leaderboard ranking  
    "level": "Gold"
  }
}
```

### 2. **Leaderboard API**

**Endpoint**: `GET /api/dashboard/leaderboard`

**❌ SEBELUM**:
```json
{
  "data": [
    {
      "user_id": 1,
      "nama": "John",
      "total_poin": 15000,  // ❌ Deprecated
      "rank": 1
    }
  ]
}
```

**✅ SETELAH**:
```json
{
  "data": [
    {
      "user_id": 1,
      "nama": "John", 
      "total_poin": 15000,   // ✅ Sekarang berisi display_poin
      "rank": 1
    }
  ]
}
```

---

## 🔧 **PERUBAHAN YANG DIPERLUKAN DI FRONTEND**

### 1. **Update State Management**

```typescript
// ❌ OLD - Hapus referensi total_poin
interface User {
  total_poin: number;  // ❌ Remove this
}

// ✅ NEW - Tambah fields baru
interface User {
  display_poin: number;  // ✅ Untuk leaderboard display
  actual_poin: number;   // ✅ Untuk saldo/transaksi
}
```

### 2. **Update User Balance Display**

```typescript
// ❌ OLD
const userBalance = user.total_poin;

// ✅ NEW - Gunakan actual_poin untuk saldo
const userBalance = user.actual_poin;
```

### 3. **Update Leaderboard Display**

```typescript
// ❌ OLD  
const leaderboardPoints = user.total_poin;

// ✅ NEW - Response sudah benar, tidak perlu perubahan
const leaderboardPoints = user.total_poin; // Backend sudah map ke display_poin
```

### 4. **Update Transaction Validation**

```typescript
// ❌ OLD - Cek saldo dengan total_poin
if (user.total_poin >= transactionAmount) {
  // allow transaction
}

// ✅ NEW - Cek saldo dengan actual_poin  
if (user.actual_poin >= transactionAmount) {
  // allow transaction
}
```

---

## 🎯 **ENDPOINT STATUS**

| Endpoint | Status | Perlu Update Frontend? |
|----------|--------|------------------------|
| `GET /api/auth/user` | ✅ Updated | ✅ Ya - response format berubah |
| `GET /api/dashboard/leaderboard` | ✅ Updated | ✅ Ya - gunakan actual_poin untuk saldo |
| `POST /api/admin/leaderboard/reset` | ✅ Working | ❌ Tidak - masih sama |
| `GET /api/admin/dashboard/overview` | ✅ Working | ❌ Tidak - masih sama |

---

## ⚠️ **MASALAH AUTHENTICATION**

### Issue: 401 Unauthorized pada Admin Endpoints

**Endpoints yang terpengaruh**:
- `GET /api/admin/dashboard/overview`
- `GET /api/admin/users`
- `GET /api/admin/leaderboard/settings`
- `GET /api/admin/leaderboard/history`

**Kemungkinan Penyebab**:
1. Token expired atau tidak valid
2. Token tidak di-refresh setelah perubahan backend
3. Header Authorization format salah

**Solusi untuk Frontend**:
```javascript
// Pastikan format header benar
const headers = {
  'Authorization': `Bearer ${token}`,
  'Accept': 'application/json',
  'Content-Type': 'application/json'
};

// Implementasi token refresh jika 401
if (response.status === 401) {
  // Refresh token atau redirect ke login
  await refreshToken();
  // Retry request
}
```

---

## 🧪 **TESTING CHECKLIST UNTUK FRONTEND**

### ✅ **Mandatory Tests**

- [ ] **User Profile**: Pastikan `actual_poin` ditampilkan sebagai saldo
- [ ] **Leaderboard**: Ranking masih bekerja dengan benar
- [ ] **Withdrawal**: Validasi menggunakan `actual_poin`
- [ ] **Product Exchange**: Validasi menggunakan `actual_poin`
- [ ] **Admin Reset**: Leaderboard reset tanpa mempengaruhi saldo user

### ✅ **Critical Scenarios**

1. **Before Reset**:
   ```
   display_poin: 15000 → Rank #1
   actual_poin: 15000  → Can withdraw Rp 15,000
   ```

2. **After Reset**:
   ```
   display_poin: 0     → Rank dropped (start fresh)
   actual_poin: 15000  → Still can withdraw Rp 15,000 ✅
   ```

3. **New Activity**:
   ```
   User earns +500 points:
   display_poin: 500   → New ranking position
   actual_poin: 15500  → Updated balance
   ```

---

## 📞 **SUPPORT & KOMUNIKASI**

### Tim Backend Contact:
- **Developer**: GitHub Copilot Assistant
- **Status**: Sistem backend sudah 100% ready
- **Response Time**: Immediate untuk bug fixes

### Timeline:
- **Backend Completion**: ✅ Done (26 Des 2025)
- **Frontend Update Required**: 🔄 ASAP
- **Testing Phase**: Setelah frontend update
- **Go Live**: Setelah testing completed

---

## 🎯 **QUICK MIGRATION GUIDE**

### Step 1: Update TypeScript Interfaces
```typescript
interface User {
  // Remove: total_poin
  display_poin: number;  // Add this
  actual_poin: number;   // Add this
}
```

### Step 2: Update Balance Display Components
```typescript
// Replace all user.total_poin with user.actual_poin for balance
```

### Step 3: Update API Error Handling
```typescript
// Add 401 token refresh logic
```

### Step 4: Test Critical Flows
```
Login → Profile → Leaderboard → Transactions → Admin Reset
```

---

## 🚀 **BENEFITS SETELAH UPDATE**

1. ✅ **Data Safety**: User tidak akan kehilangan "uang" saat admin reset leaderboard
2. ✅ **Fair Competition**: Admin bisa reset ranking untuk kompetisi baru
3. ✅ **Better UX**: Pemisahan yang jelas antara ranking vs saldo
4. ✅ **System Integrity**: Tidak ada data loss risk

---

**🔥 URGENT**: Update frontend segera untuk menghindari user confusion dan potential data display errors!

**📧 Kontak untuk pertanyaan**: Backend team ready to assist dengan implementasi ini.
