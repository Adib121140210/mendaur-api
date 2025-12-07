# 📋 TABEL SESSIONS - PENJELASAN LENGKAP

**Date:** December 1, 2025  
**Tabel:** sessions  
**Status:** ✅ DIGUNAKAN (JANGAN DI-DROP)

---

## ❓ PERTANYAAN: Tabel Sessions Untuk Apa?

### **JAWABAN SINGKAT:**
Tabel `sessions` menyimpan **data session user yang sedang login** di aplikasi Mendaur. Digunakan oleh Laravel untuk melacak siapa yang login, berapa lama aktif, dan kapan mereka logout.

---

## 🔍 PENJELASAN DETAIL

### **Apa itu Session?**

```
Session = Temporary data about a logged-in user

Contoh:
├─ User login dengan username "adib"
├─ Laravel membuat session (identifier unik)
├─ Session menyimpan data: user_id, login_time, activity
├─ Setiap request, Laravel cek session
├─ Jika session expired/invalid, user di-logout
└─ User logout, session dihapus
```

### **Analogi Sederhana:**

```
Toko Online:
├─ Pelanggan A tiba, ambil keranjang belanja
├─ Keranjang = session (identifier unik A)
├─ A masukkan produk ke keranjang
├─ A bisa keliling toko, keranjang tetap ada
├─ A bisa checkout kapan saja
├─ A logout/keluar, keranjang dihapus
└─ Session = keranjang yang menyimpan status pelanggan
```

---

## 💾 TABEL SESSIONS - STRUKTUR

### **Isi Tabel (Data yang Disimpan):**

```sql
CREATE TABLE sessions (
  id VARCHAR(255) PRIMARY KEY,           -- Session ID (unique identifier)
  user_id BIGINT UNSIGNED,                -- ID user yang login (nullable)
  ip_address VARCHAR(45),                 -- IP address client
  user_agent TEXT,                        -- Browser/device info
  payload LONGTEXT,                       -- Session data (encrypted)
  last_activity INT                       -- Last activity timestamp
);
```

### **Contoh Data Real:**

```
id:            | lm9k2j3hk2j3hk2j3
user_id:       | 5 (user Adib)
ip_address:    | 192.168.1.100
user_agent:    | Mozilla/5.0 (Windows NT 10.0; Win64; x64)
payload:       | (encrypted data: user preferences, cart, etc)
last_activity: | 1733047234 (timestamp)
```

---

## 🔄 BAGAIMANA SESSIONS BEKERJA?

### **Workflow Login & Session:**

```
┌─────────────────────────────────────────────────────────┐
│ 1. USER MEMBUKA LOGIN PAGE                              │
├─────────────────────────────────────────────────────────┤
│ ├─ Browser: GET /login
│ ├─ Laravel: Buat session ID (unik)
│ ├─ Server: Masukkan ke SESSIONS table
│ └─ Browser: Terima session cookie
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 2. USER SUBMIT LOGIN FORM                               │
├─────────────────────────────────────────────────────────┤
│ ├─ Browser: POST /login (username: adib, password: xxx)
│ ├─ Laravel: Verifikasi username/password
│ ├─ Database: Cek tabel USERS (jika cocok)
│ ├─ Server: Update SESSIONS table dengan user_id=5
│ ├─ Server: Set last_activity = now()
│ └─ Browser: Redirect ke dashboard
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 3. USER BROWSING APLIKASI (Sudah Login)                 │
├─────────────────────────────────────────────────────────┤
│ ├─ Browser: GET /dashboard (+ session cookie)
│ ├─ Laravel: Cek SESSIONS table untuk session ID
│ ├─ Database: Query: SELECT * FROM sessions WHERE id = 'lm9k...'
│ ├─ Laravel: Verifikasi session masih valid
│ ├─ Server: Update last_activity = now()
│ └─ Browser: Tampilkan dashboard (tahu user_id = 5 dari session)
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ 4. USER LOGOUT                                          │
├─────────────────────────────────────────────────────────┤
│ ├─ Browser: Click logout button
│ ├─ Laravel: DELETE FROM sessions WHERE id = 'lm9k...'
│ ├─ Database: Session row dihapus
│ └─ Browser: Redirect ke login page (session expired)
└─────────────────────────────────────────────────────────┘
```

---

## 🛡️ MENGAPA SESSIONS PENTING?

### **1. Identifikasi User**

```
Tanpa session:
├─ Browser: GET /dashboard
├─ Server: "Siapa ini? Tidak tahu user mana"
├─ Server: Tidak bisa process request
└─ Akses denied

Dengan session:
├─ Browser: GET /dashboard + session_id='xyz'
├─ Server: Cek sessions table, temukan user_id = 5
├─ Server: Tahu ini user Adib, process request
└─ Tampilkan data Adib
```

### **2. Keamanan (Authentication)**

```
Session mencegah:
├─ Orang lain akses akun tanpa login
├─ Session timeout (auto logout jika idle)
├─ Session hijacking (encrypt session data)
└─ CSRF attacks (session token validation)
```

### **3. User Preferences**

```
Session menyimpan:
├─ Siapa user yang login (user_id)
├─ Kapan mereka login (last_activity)
├─ Preferensi user (language, theme, etc)
├─ Status current (data dalam processing)
└─ Shopping cart (jika e-commerce)
```

### **4. Activity Tracking**

```
Sessions table bisa tracking:
├─ Berapa user sedang online
├─ Kapan user terakhir aktif
├─ Browser/device apa yang dipakai
├─ IP address dari mana
└─ Audit trail untuk keamanan
```

---

## 📊 TABEL SESSIONS DI MENDAUR

### **Fungsi Spesifik untuk Mendaur:**

```
1. USER AUTHENTICATION
   ├─ Identifikasi nasabah yang login
   ├─ Track user_id untuk setiap request
   └─ Pastikan user hanya akses data mereka

2. ROLE-BASED ACCESS CONTROL
   ├─ Check apakah user adalah Admin, Superadmin, atau Nasabah
   ├─ Decode session, lihat user_id
   ├─ Query tabel ROLES berdasarkan user_id
   └─ Tentukan akses yang allowed

3. API REQUEST TRACKING
   ├─ Setiap GET /api/user/profile
   ├─ Laravel baca session, tahu user_id
   ├─ Return data user yang sesuai (bukan user lain)
   └─ Secure by design

4. LOGOUT FUNCTIONALITY
   ├─ User click logout
   ├─ DELETE session dari SESSIONS table
   ├─ Browser cookie cleared
   └─ Auto logout jika idle 120 minutes

5. ACTIVITY LOGGING
   ├─ Session last_activity diupdate
   ├─ Bisa track user activity
   ├─ Bisa detect suspicious logins
   └─ Audit trail untuk security
```

---

## 🔐 FLOW MENDAUR DENGAN SESSIONS

### **Scenario: Nasabah Login & View Points**

```
┌──────────────────────────────────┐
│ 1. Nasabah buka app              │
├──────────────────────────────────┤
│ ├─ GET /login
│ ├─ Laravel: Create session
│ ├─ INSERT INTO sessions (...)
│ └─ Browser: Receive session cookie
└──────────────────────────────────┘
           ↓
┌──────────────────────────────────┐
│ 2. Submit login form              │
├──────────────────────────────────┤
│ ├─ POST /login (username/password)
│ ├─ Laravel: Check USERS table
│ ├─ If valid: UPDATE sessions SET user_id=5
│ └─ Redirect: /dashboard
└──────────────────────────────────┘
           ↓
┌──────────────────────────────────┐
│ 3. GET /api/user/points          │
├──────────────────────────────────┤
│ ├─ Browser: Send request + session
│ ├─ Laravel: SELECT * FROM sessions WHERE id='...'
│ ├─ Found: user_id = 5
│ ├─ Query: SELECT total_poin FROM users WHERE id=5
│ └─ Response: {"total_poin": 500}
└──────────────────────────────────┘
           ↓
┌──────────────────────────────────┐
│ 4. GET /dashboard               │
├──────────────────────────────────┤
│ ├─ Browser: + session cookie
│ ├─ Laravel: Check session (user_id=5)
│ ├─ Check: Has user_id in session? YES
│ ├─ Show: Dashboard untuk user_id=5
│ └─ Only show Adib's data
└──────────────────────────────────┘
           ↓
┌──────────────────────────────────┐
│ 5. User logout                  │
├──────────────────────────────────┤
│ ├─ POST /logout
│ ├─ Laravel: DELETE FROM sessions WHERE id='...'
│ ├─ Session cleared
│ └─ Redirect: /login
└──────────────────────────────────┘
```

---

## ⚙️ TECHNICAL DETAILS

### **Laravel Session Configuration:**

```php
// config/session.php
[
    'driver' => 'database',           // Use database untuk sessions
    'lifetime' => 120,                // 120 minutes (2 hours)
    'expire_on_close' => false,       // Don't expire when browser closes
    'encrypt' => true,                // Encrypt session data
    'http_only' => true,              // Only HTTP (not JavaScript)
    'secure' => true,                 // HTTPS only
    'same_site' => 'lax',            // CSRF protection
]
```

### **Session Lifetime:**

```
├─ User login: Session created
├─ User inactive 120 minutes: Auto-logout (timeout)
├─ User active: last_activity updated on each request
├─ User logout: Session deleted
└─ Browser close: If expire_on_close=true, session dies
```

---

## 🚫 TIDAK BISA DROP SESSIONS TABLE

### **Alasan Penting:**

```
❌ JANGAN DROP SESSIONS TABLE karena:

1. FATAL ERROR - Aplikasi akan crash
   ├─ Semua user akan di-logout
   ├─ Tidak bisa login lagi
   └─ API endpoints akan error

2. AUTHENTICATION BROKEN
   ├─ Laravel tidak bisa track user
   ├─ Tidak bisa verify siapa user
   └─ Akses kontrol error

3. DATA SECURITY ISSUE
   ├─ Tidak bisa check user permission
   ├─ Tidak bisa enforce role-based access
   └─ Security vulnerability

4. COMPLETE APPLICATION FAILURE
   ├─ Semua protected routes fail
   ├─ Semua API endpoints fail
   └─ Aplikasi tidak bisa digunakan

KESIMPULAN: ❌ SESSIONS table adalah CRITICAL
            Harus KEEP, jangan pernah di-DROP
```

---

## ✅ TABEL YANG AMAN DI-DROP

### **Bandingkan dengan yang benar-benar unused:**

```
AMAN DROP (truly unused):
├─ cache (tidak dipakai, bisa ganti Redis)
├─ cache_locks (untuk cache, tidak dipakai)
├─ jobs (tidak ada queue implementation)
├─ failed_jobs (no jobs = no failed jobs)
└─ job_batches (untuk batch jobs, tidak ada)

JANGAN DROP (critical):
├─ sessions ✅ (authentication - CRITICAL)
├─ users ✅ (user data - CRITICAL)
├─ transaksis ✅ (business logic - CRITICAL)
└─ ... (23 business logic tables - CRITICAL)
```

---

## 📊 SESSIONS VS OTHER TABLES

| Table | Purpose | Type | Drop? | Why |
|-------|---------|------|-------|-----|
| sessions | User auth | Framework | ❌ NO | Critical for login |
| cache | Cache storage | Framework | ✅ YES | Not used, can use Redis |
| cache_locks | Cache locks | Framework | ✅ YES | Depends on cache |
| jobs | Queue jobs | Framework | ✅ YES | No queue implementation |
| failed_jobs | Failed jobs | Framework | ✅ YES | No jobs = no failures |
| users | User data | Business | ❌ NO | Store user accounts |
| transaksis | Transactions | Business | ❌ NO | Core business logic |
| badges | Gamification | Business | ❌ NO | Reward system |

---

## 🎯 KESIMPULAN

### **SESSIONS TABLE:**

```
✅ STATUS:          DIGUNAKAN (USED)
✅ DIBUTUHKAN:      YA (YES - CRITICAL)
✅ FUNGSI:          User authentication & session management
✅ BOLEH DI-DROP:   TIDAK (NO - WILL BREAK APP)
✅ ALTERNATIF:      Tidak ada (harus menggunakan sessions)
✅ RISK JIKA DROP:  100% - Aplikasi tidak bisa digunakan
```

### **REKOMENDASI:**

```
❌ JANGAN PERNAH DROP SESSIONS TABLE

Tabel yang benar-benar aman di-DROP:
✅ cache
✅ cache_locks
✅ failed_jobs
✅ jobs
✅ job_batches

Simpan sessions tetap!
```

---

## 🔄 JIKA PERLU CLEAR SESSIONS

### **Hanya ingin clear data session (bukan drop table):**

```sql
-- Clear semua sessions (semua user logout)
DELETE FROM sessions;

-- Atau clear expired sessions:
DELETE FROM sessions WHERE last_activity < (UNIX_TIMESTAMP() - 7200);
-- 7200 = 2 hours

-- Tabel sessions tetap ada, hanya data yang dihapus
```

**Note:** Clear sessions = semua user di-logout, tapi tabel tetap ada.

---

**Kesimpulan:** Tabel `sessions` adalah **CRITICAL** untuk aplikasi. Harus di-KEEP, jangan di-DROP! ✅
