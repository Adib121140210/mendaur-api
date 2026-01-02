# Blackbox Testing Fixes - 2 Januari 2026

## ✅ Issues yang Sudah Diperbaiki di Backend

### BT-33 & BT-49: Admin Artikel - Tambah Artikel Error 500
**Status:** ✅ Fixed

**Masalah:** Error 500 saat POST artikel dengan foto

**Perbaikan:**
- Improved error handling dengan try-catch
- Better file validation (mimes: jpeg,jpg,png,gif,webp)
- Unique slug generation jika duplikat
- Fallback ke local storage jika Cloudinary gagal

**Endpoint:** `POST /api/admin/artikel`
```json
// Request (multipart/form-data)
{
  "judul": "Judul Artikel",
  "konten": "Isi artikel...",
  "foto_cover": [FILE], // optional
  "penulis": "Nama Penulis",
  "kategori": "edukasi",
  "tanggal_publikasi": "2026-01-02"
}
```

---

### BT-40: Admin Penukaran - Reject Error "Sudah diambil"
**Status:** ✅ Fixed

**Masalah:** Tidak bisa reject penukaran dengan status selain "pending"

**Perbaikan:**
- Sekarang bisa reject status: `pending`, `approved`, `diproses`, `dikirim`
- Poin otomatis dikembalikan ke user
- Jika sudah approved, stok produk dikembalikan

**Endpoint:** `PATCH /api/admin/penukar-produk/{id}/reject`
```json
{
  "alasan_penolakan": "Alasan ditolak"
}
```

---

### BT-43: Admin Penarikan - Poin Tidak Kembali
**Status:** ✅ Fixed

**Masalah:** Parameter type error pada refund function

**Perbaikan:**
- Fixed parameter dari `$user` (object) ke `$user->user_id` (int)
- Poin sekarang dikembalikan dengan benar

**Endpoint:** `PATCH /api/admin/penarikan-tunai/{id}/reject`
```json
{
  "catatan_admin": "Alasan penolakan"
}
```

---

### BT-45: Admin Users - Detail User
**Status:** ✅ Already Exists

**Endpoint sudah tersedia:**
`GET /api/admin/users/{userId}`

**Response:**
```json
{
  "status": "success",
  "data": {
    "user_id": 1,
    "nama": "John Doe",
    "email": "john@example.com",
    "no_hp": "081234567890",
    "alamat": "...",
    "role_id": 1,
    "role_name": "nasabah",
    "level": 1,
    "status": "active",
    "tipe_nasabah": "modern",
    "actual_poin": 5000,
    "display_poin": 10000,
    "total_setor_sampah": 50.5,
    "foto_profil": "https://...",
    "joinDate": "2026-01-01T00:00:00+00:00",
    "lastUpdated": "2026-01-02T00:00:00+00:00"
  }
}
```

---

### BT-47: Admin Dashboard - Total Users & Waste
**Status:** ✅ Enhanced

**Perbaikan:**
- Fixed kolom `total_poin_digunakan` → `poin_digunakan`
- Tambah lebih banyak statistik
- Tambah format weight (Kg/Ton)

**Endpoint:** `GET /api/admin/dashboard/overview`

**Response BARU:**
```json
{
  "status": "success",
  "data": {
    "waste": {
      "yearly_total_kg": 1500.50,
      "yearly_total_count": 150,
      "monthly_total_kg": 200.25,
      "total_formatted": "1,50 Ton"  // NEW!
    },
    "points": {
      "yearly_total": 50000,
      "monthly_total": 8000,
      "total_distributed": 100000  // NEW! Total poin semua user
    },
    "users": {
      "total": 100,
      "total_nasabah": 95,  // NEW! Hanya nasabah
      "active_30days": 50,
      "new_this_month": 10  // NEW!
    },
    "redemptions": {
      "yearly_total_points_redeemed": 15000,
      "yearly_total_count": 25  // NEW!
    },
    "pending": {  // NEW SECTION!
      "deposits": 5,
      "redemptions": 3
    }
  }
}
```

---

## 📋 Checklist Frontend Verification

### BT-05 to BT-07: Forgot Password Flow
**Backend Status:** ✅ Ready

| Step | Endpoint | Method |
|------|----------|--------|
| Request OTP | `/api/forgot-password` | POST |
| Verify OTP | `/api/verify-otp` | POST |
| Reset Password | `/api/reset-password` | POST |

**Frontend harus:**
- [ ] Kirim email ke `/api/forgot-password`
- [ ] Tampilkan form OTP setelah berhasil
- [ ] Kirim OTP + email ke `/api/verify-otp`
- [ ] Redirect ke form password baru
- [ ] Kirim password baru ke `/api/reset-password`

---

### BT-08 to BT-10: Profile Management
**Backend Status:** ✅ Ready

| Feature | Endpoint | Method |
|---------|----------|--------|
| Get Profile | `/api/profile` | GET |
| Update Profile | `/api/profile` | PUT |
| Upload Photo | `/api/profile/photo` | POST |

**Validasi foto:**
- Max size: 2MB
- Format: jpeg, jpg, png, gif, webp

**Frontend harus:**
- [ ] Validasi ukuran file sebelum upload
- [ ] Tampilkan error "Ukuran file maksimal 2MB" jika > 2MB

---

### BT-16: Setor Sampah Filter Status
**Backend Status:** ✅ Ready

**Endpoint:** `GET /api/tabung-sampah?status=pending`

**Filter values:** `pending`, `approved`, `rejected`

---

### BT-19: Penukaran Produk - Poin Tidak Cukup
**Backend Status:** ✅ Ready

**Endpoint:** `POST /api/penukaran-produk`

**Error Response (400):**
```json
{
  "success": false,
  "message": "Poin tidak mencukupi",
  "errors": {
    "poin": ["Poin Anda tidak cukup untuk menukar produk ini"]
  }
}
```

---

### BT-20: Riwayat Penukaran
**Backend Status:** ✅ Ready

**Endpoint:** `GET /api/penukaran-produk`

---

### BT-28: Level Auto Update
**Backend Status:** ✅ Ready

Level otomatis di-update saat:
- Poin bertambah (setor sampah approved)
- Badge earned

**Level Threshold:**
- Level 1: 0-999 poin
- Level 2: 1000-4999 poin
- Level 3: 5000-9999 poin
- Level 4: 10000-19999 poin
- Level 5: 20000+ poin

---

### BT-29 & BT-30: Notifikasi
**Backend Status:** ✅ Ready

| Feature | Endpoint | Method |
|---------|----------|--------|
| List Notifikasi | `/api/notifikasi` | GET |
| Mark as Read | `/api/notifikasi/{id}/read` | PATCH |
| Count Unread | `/api/notifikasi/unread-count` | GET |

---

## 🔧 Kemungkinan Issue di Frontend

### 1. Admin Artikel Upload Foto
**Kemungkinan Issue:**
- Frontend mengirim base64 bukan file
- Content-Type tidak multipart/form-data

**Solusi:**
```javascript
const formData = new FormData();
formData.append('judul', judul);
formData.append('konten', konten);
formData.append('foto_cover', file); // File object, bukan base64
formData.append('penulis', penulis);
formData.append('kategori', kategori);
formData.append('tanggal_publikasi', tanggal);

await api.post('/admin/artikel', formData, {
  headers: {
    'Content-Type': 'multipart/form-data'
  }
});
```

### 2. Dashboard Stats Showing 0
**Kemungkinan Issue:**
- Frontend belum refresh data setelah deploy
- Cache browser

**Solusi:**
- Hard refresh (Ctrl+Shift+R)
- Clear local storage/cache
- Pastikan panggil endpoint yang benar: `/api/admin/dashboard/overview`

### 3. User Detail Tidak Muncul
**Kemungkinan Issue:**
- Frontend belum implement fitur detail user
- Routing salah

**Endpoint yang tersedia:**
```
GET /api/admin/users/{userId}
```

---

## 📝 Summary Perubahan API

| Fitur | Status | Catatan |
|-------|--------|---------|
| Artikel POST | ✅ Fixed | Better error handling |
| Penukaran Reject | ✅ Fixed | Support more statuses |
| Penarikan Reject | ✅ Fixed | Points refunded correctly |
| User Detail | ✅ Ready | Endpoint exists |
| Dashboard Stats | ✅ Enhanced | More data fields |
| Leaderboard Overview | ✅ Ready | New endpoint |
| Jenis Sampah Stats | ✅ Ready | New endpoint |

---

*Dokumen dibuat: 2 Januari 2026*
*Backend commit: 1ec4ce2*
