# 🚀 Frontend Migration Guide - Perubahan API Mendaur

**Tanggal:** 1 Januari 2026  
**Versi API:** 2.0  
**Status:** WAJIB DITERAPKAN

---

## 📋 RINGKASAN PERUBAHAN

### Perubahan Utama: Sistem Dual-Poin

API sekarang menggunakan **2 field poin** yang berbeda fungsinya:

| Field Lama | Field Baru | Fungsi |
|------------|------------|--------|
| `total_poin` | `display_poin` | Poin untuk **LEADERBOARD** |
| `poin_tercatat` | `display_poin` | Poin untuk **LEADERBOARD** |
| - | `actual_poin` | Poin **SALDO** yang bisa digunakan |

---

## ⚠️ BREAKING CHANGES

### 1. Response User Profile

**SEBELUM:**
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "total_poin": 5000,
    "poin_tercatat": 5000
  }
}
```

**SESUDAH:**
```json
{
  "user": {
    "user_id": 1,
    "nama": "John Doe",
    "display_poin": 5000,
    "actual_poin": 3500
  }
}
```

### 2. Response Badge Progress

**SEBELUM:**
```json
{
  "user": {
    "id": 1,
    "poin_tercatat": 5000
  }
}
```

**SESUDAH:**
```json
{
  "user": {
    "id": 1,
    "display_poin": 5000,
    "actual_poin": 3500
  }
}
```

### 3. Response Admin User List

**SEBELUM:**
```json
{
  "users": [{
    "user_id": 1,
    "total_poin": 5000
  }]
}
```

**SESUDAH:**
```json
{
  "users": [{
    "user_id": 1,
    "total_poin": 3500,
    "display_poin": 5000,
    "actual_poin": 3500
  }]
}
```

---

## 🔧 PERUBAHAN YANG HARUS DILAKUKAN DI FRONTEND

### 1. Update Interface/Type User

```typescript
// SEBELUM
interface User {
  user_id: number;
  nama: string;
  email: string;
  total_poin: number;      // HAPUS
  poin_tercatat: number;   // HAPUS
}

// SESUDAH
interface User {
  user_id: number;
  nama: string;
  email: string;
  display_poin: number;    // Untuk leaderboard
  actual_poin: number;     // Untuk saldo/transaksi
}
```

### 2. Update Tampilan Saldo Poin

```typescript
// SEBELUM
const saldoPoin = user.total_poin;
// atau
const saldoPoin = user.poin_tercatat;

// SESUDAH
const saldoPoin = user.actual_poin;  // Untuk menampilkan saldo yang bisa digunakan
```

### 3. Update Tampilan Leaderboard

```typescript
// SEBELUM
const rankingPoin = user.total_poin;

// SESUDAH
const rankingPoin = user.display_poin;  // Untuk ranking/leaderboard
```

### 4. Update Validasi Penukaran Produk

```typescript
// SEBELUM
if (user.total_poin >= produk.harga_poin) {
  // Allow redemption
}

// SESUDAH
if (user.actual_poin >= produk.harga_poin) {
  // Allow redemption
}
```

### 5. Update Validasi Withdrawal

```typescript
// SEBELUM
if (user.total_poin >= jumlahWithdraw) {
  // Allow withdrawal
}

// SESUDAH
if (user.actual_poin >= jumlahWithdraw) {
  // Allow withdrawal
}
```

---

## 📱 CONTOH IMPLEMENTASI UI

### Halaman Profile User

```jsx
// Component Profile
function UserProfile({ user }) {
  return (
    <div>
      <h2>{user.nama}</h2>
      
      {/* Saldo Poin yang bisa digunakan */}
      <div className="saldo-card">
        <label>Saldo Poin</label>
        <span className="poin">{user.actual_poin.toLocaleString()}</span>
        <small>Dapat digunakan untuk penukaran & penarikan</small>
      </div>
      
      {/* Poin untuk leaderboard (opsional ditampilkan) */}
      <div className="ranking-card">
        <label>Total Poin Terkumpul</label>
        <span className="poin">{user.display_poin.toLocaleString()}</span>
        <small>Digunakan untuk peringkat leaderboard</small>
      </div>
    </div>
  );
}
```

### Halaman Leaderboard

```jsx
// Component Leaderboard
function Leaderboard({ users }) {
  // Gunakan display_poin untuk sorting
  const sortedUsers = users.sort((a, b) => b.display_poin - a.display_poin);
  
  return (
    <div>
      {sortedUsers.map((user, index) => (
        <div key={user.user_id} className="leaderboard-item">
          <span className="rank">#{index + 1}</span>
          <span className="name">{user.nama}</span>
          <span className="poin">{user.display_poin.toLocaleString()} poin</span>
        </div>
      ))}
    </div>
  );
}
```

### Halaman Penukaran Produk

```jsx
// Component RedeemProduct
function RedeemProduct({ user, produk }) {
  // Cek menggunakan actual_poin
  const canRedeem = user.actual_poin >= produk.harga_poin;
  const sisaPoin = user.actual_poin - produk.harga_poin;
  
  return (
    <div>
      <h3>{produk.nama}</h3>
      <p>Harga: {produk.harga_poin} poin</p>
      <p>Saldo Anda: {user.actual_poin} poin</p>
      
      {canRedeem ? (
        <>
          <p>Sisa setelah penukaran: {sisaPoin} poin</p>
          <button onClick={handleRedeem}>Tukar Sekarang</button>
        </>
      ) : (
        <p className="error">
          Poin tidak cukup. Kurang {produk.harga_poin - user.actual_poin} poin
        </p>
      )}
    </div>
  );
}
```

---

## 🔄 MAPPING FIELD LENGKAP

| Konteks | Field Lama | Field Baru | Catatan |
|---------|------------|------------|---------|
| Tampilan saldo | `total_poin` | `actual_poin` | Saldo yang bisa dipakai |
| Leaderboard | `total_poin` | `display_poin` | Ranking, tidak pernah turun |
| Badge progress | `poin_tercatat` | `display_poin` | Total poin historis |
| Validasi redeem | `total_poin` | `actual_poin` | Cek saldo cukup |
| Validasi withdraw | `total_poin` | `actual_poin` | Cek saldo cukup |
| Admin: user list | `total_poin` | `actual_poin` | Saldo user |
| Admin: statistics | `total_poin` | `display_poin` | Total poin sistem |

---

## 📊 ENDPOINT YANG BERUBAH RESPONSE-NYA

### User Endpoints
| Endpoint | Perubahan |
|----------|-----------|
| `GET /api/profile` | + `display_poin`, `actual_poin` |
| `GET /api/users/{id}` | + `display_poin`, `actual_poin` |
| `GET /api/user/{id}/poin` | + `display_poin`, `actual_poin` |

### Badge Endpoints
| Endpoint | Perubahan |
|----------|-----------|
| `GET /api/user/badges/progress` | `poin_tercatat` → `display_poin`, `actual_poin` |

### Dashboard Endpoints
| Endpoint | Perubahan |
|----------|-----------|
| `GET /api/dashboard/stats/{userId}` | + `display_poin`, `actual_poin` |
| `GET /api/dashboard/leaderboard` | Ranking by `display_poin` |

### Admin Endpoints
| Endpoint | Perubahan |
|----------|-----------|
| `GET /api/admin/users` | + `display_poin`, `actual_poin` |
| `GET /api/admin/leaderboard` | Ranking by `display_poin` |
| `POST /api/admin/points/award` | Response + `displayPoin`, `currentBalance` |

---

## ⚡ QUICK FIX - Find & Replace

Lakukan find & replace di codebase frontend:

| Find | Replace With | Context |
|------|--------------|---------|
| `user.total_poin` | `user.actual_poin` | Untuk saldo |
| `user.poin_tercatat` | `user.display_poin` | Untuk leaderboard |
| `total_poin` (dalam type) | `actual_poin` | Interface/Type |

---

## 🧪 CHECKLIST TESTING

Setelah update frontend, test scenario berikut:

- [ ] Login user → Cek saldo tampil dengan `actual_poin`
- [ ] Halaman profile → Cek kedua poin tampil benar
- [ ] Leaderboard → Cek ranking berdasarkan `display_poin`
- [ ] Penukaran produk → Validasi pakai `actual_poin`
- [ ] Withdrawal → Validasi pakai `actual_poin`
- [ ] Badge progress → Tampilkan `display_poin`
- [ ] Admin user list → Kedua field tampil

---

## 🆘 BACKWARD COMPATIBILITY

Untuk sementara, beberapa endpoint masih mengembalikan `total_poin` sebagai alias ke `actual_poin` untuk backward compatibility. Namun **SEGERA MIGRATE** karena field ini akan dihapus di versi berikutnya.

```json
{
  "total_poin": 3500,      // DEPRECATED - akan dihapus
  "display_poin": 5000,    // Gunakan ini untuk leaderboard
  "actual_poin": 3500      // Gunakan ini untuk saldo
}
```

---

## 📞 KONTAK

Jika ada pertanyaan atau menemukan bug setelah migrasi:
- Buat issue di repository
- Tag: `frontend-migration`

---

*Dokumen ini dibuat: 1 Januari 2026*
