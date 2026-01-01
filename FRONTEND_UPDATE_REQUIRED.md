# 🔄 FRONTEND UPDATE REQUIRED - Follow Up Backend Changes

**Tanggal**: 1 Januari 2026  
**Backend Commit**: `f5a6adf`  
**Status**: ✅ Production Deployed

---

## 📋 RINGKASAN PERUBAHAN

Backend telah mengimplementasikan **Dual-Point System** yang memisahkan poin menjadi dua field:

| Field Lama | Field Baru | Keterangan |
|------------|------------|------------|
| `total_poin` | `display_poin` | Poin untuk **leaderboard/ranking** (tidak pernah turun) |
| `poin_tercatat` | `actual_poin` | Poin **saldo aktual** (bisa naik/turun untuk transaksi) |

---

## 🚨 PERUBAHAN WAJIB DI FRONTEND

### 1️⃣ **Ganti Field `total_poin` → `display_poin`**

**Lokasi yang perlu diupdate:**
- Leaderboard/Ranking display
- Profile user (tampilan poin achievements)
- Badge progress indicator
- Statistics dashboard

**Sebelum:**
```javascript
// ❌ DEPRECATED
const userPoints = response.data.total_poin;
const ranking = response.data.user.total_poin;
```

**Sesudah:**
```javascript
// ✅ GUNAKAN INI
const userPoints = response.data.display_poin;
const ranking = response.data.user.display_poin;
```

---

### 2️⃣ **Ganti Field `poin_tercatat` → `actual_poin`**

**Lokasi yang perlu diupdate:**
- Saldo poin untuk transaksi (withdrawal, redeem produk)
- Cek apakah user punya cukup poin
- Form tukar poin
- Form tarik tunai

**Sebelum:**
```javascript
// ❌ DEPRECATED
const balance = response.data.poin_tercatat;
const canRedeem = user.poin_tercatat >= product.harga_poin;
```

**Sesudah:**
```javascript
// ✅ GUNAKAN INI
const balance = response.data.actual_poin;
const canRedeem = user.actual_poin >= product.harga_poin;
```

---

### 3️⃣ **Update Response Handling dari API User**

**API Response Baru dari `/api/user` atau `/api/profile`:**
```json
{
  "status": "success",
  "data": {
    "user_id": 1,
    "nama": "John Doe",
    "email": "john@example.com",
    "display_poin": 1500,    // ← UNTUK LEADERBOARD (tidak pernah turun)
    "actual_poin": 800,      // ← UNTUK TRANSAKSI (bisa turun)
    "badge_title": "Green Hero",
    // ... field lainnya
  }
}
```

---

## 📱 MAPPING UI COMPONENT

| UI Component | Field yang Digunakan | Keterangan |
|--------------|---------------------|------------|
| **Leaderboard** | `display_poin` | Ranking berdasarkan total perolehan |
| **Profile Poin Display** | `display_poin` | Menunjukkan pencapaian user |
| **Badge Progress** | `display_poin` | Syarat badge berdasarkan display_poin |
| **Saldo Poin (untuk transaksi)** | `actual_poin` | Berapa yang bisa dipakai |
| **Form Tukar Poin** | `actual_poin` | Validasi kecukupan saldo |
| **Form Tarik Tunai** | `actual_poin` | Validasi kecukupan saldo |
| **History Transaksi** | `poin_didapat` / `poin_digunakan` | Per transaksi |

---

## 🔍 CONTOH IMPLEMENTASI REACT/FLUTTER

### React Native Example:

```javascript
// services/userService.js
export const getUserProfile = async () => {
  const response = await api.get('/api/user');
  return {
    ...response.data.data,
    // Map ke nama yang lebih jelas
    pointsForDisplay: response.data.data.display_poin,  // Untuk leaderboard
    pointsBalance: response.data.data.actual_poin,      // Untuk transaksi
  };
};

// components/ProfileScreen.js
const ProfileScreen = () => {
  const [user, setUser] = useState(null);
  
  return (
    <View>
      {/* Tampilan Poin Pencapaian */}
      <Text>Total Poin: {user.display_poin}</Text>
      
      {/* Saldo untuk Transaksi */}
      <Text>Saldo Poin: {user.actual_poin}</Text>
    </View>
  );
};

// components/RedeemProduct.js
const RedeemProduct = ({ product }) => {
  const canRedeem = user.actual_poin >= product.harga_poin;
  
  return (
    <Button 
      disabled={!canRedeem}
      onPress={handleRedeem}
    >
      {canRedeem 
        ? `Tukar (${product.harga_poin} poin)` 
        : `Poin tidak cukup (butuh ${product.harga_poin - user.actual_poin} lagi)`
      }
    </Button>
  );
};
```

### Flutter Example:

```dart
// models/user.dart
class User {
  final int userId;
  final String nama;
  final int displayPoin;  // Untuk leaderboard
  final int actualPoin;   // Untuk transaksi
  
  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      userId: json['user_id'],
      nama: json['nama'],
      displayPoin: json['display_poin'] ?? 0,  // ← GUNAKAN INI
      actualPoin: json['actual_poin'] ?? 0,    // ← GUNAKAN INI
    );
  }
}

// screens/profile_screen.dart
Widget build(BuildContext context) {
  return Column(
    children: [
      // Pencapaian Poin
      Text('Total Poin: ${user.displayPoin}'),
      
      // Saldo Transaksi
      Text('Saldo: ${user.actualPoin} poin'),
    ],
  );
}
```

---

## 📊 API ENDPOINTS YANG TERPENGARUH

### Endpoints dengan Response Field Baru:

| Endpoint | Method | Field Baru |
|----------|--------|------------|
| `/api/user` | GET | `display_poin`, `actual_poin` |
| `/api/profile` | GET | `display_poin`, `actual_poin` |
| `/api/leaderboard` | GET | `display_poin` |
| `/api/admin/users` | GET | `display_poin`, `actual_poin` |
| `/api/admin/users/{id}` | GET | `display_poin`, `actual_poin` |
| `/api/badge-progress` | GET | `current_poin` = `display_poin` |
| `/api/dashboard` | GET | User stats dengan `display_poin` |

### Endpoints Transaksi (validasi dengan `actual_poin`):

| Endpoint | Method | Validasi |
|----------|--------|----------|
| `/api/penukaran-produk` | POST | Cek `actual_poin >= harga_poin` |
| `/api/penarikan-tunai` | POST | Cek `actual_poin >= jumlah_penarikan` |

---

## ⚠️ DEPRECATED FIELDS (JANGAN GUNAKAN LAGI)

```javascript
// ❌ DEPRECATED - AKAN DIHAPUS DI VERSI MENDATANG
response.data.total_poin      // Ganti dengan display_poin
response.data.poin_tercatat   // Ganti dengan actual_poin
```

---

## ✅ CHECKLIST UPDATE FRONTEND

- [ ] Update model/class User untuk include `display_poin` dan `actual_poin`
- [ ] Ganti semua `total_poin` → `display_poin` di leaderboard
- [ ] Ganti semua `poin_tercatat` → `actual_poin` di transaksi
- [ ] Update Profile screen untuk tampilkan kedua poin
- [ ] Update Badge Progress untuk gunakan `display_poin`
- [ ] Update Form Tukar Poin validasi dengan `actual_poin`
- [ ] Update Form Tarik Tunai validasi dengan `actual_poin`
- [ ] Test semua fitur yang menggunakan poin
- [ ] Remove deprecated field references

---

## 📞 KONTAK

Jika ada pertanyaan atau butuh klarifikasi:
- Backend Developer: [Contact Info]
- API Documentation: https://mendaur.up.railway.app

---

## 📅 TIMELINE

| Tanggal | Status |
|---------|--------|
| 1 Jan 2026 | ✅ Backend deployed dengan dual-point system |
| 1 Jan 2026 | 📋 Frontend update required |
| TBD | ⏳ Frontend deployment dengan field baru |
| TBD | 🗑️ Backend akan hapus `total_poin` dan `poin_tercatat` |

---

**PENTING**: Backend masih mengirim field lama (`total_poin`, `poin_tercatat`) untuk backward compatibility sementara. Namun field ini akan **DIHAPUS** setelah frontend selesai update.
