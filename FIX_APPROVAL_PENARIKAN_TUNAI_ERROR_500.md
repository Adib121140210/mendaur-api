# 🔧 FIX: Error 500 pada Approval Penarikan Tunai
## PATCH /api/admin/penarikan-tunai/{id}/approve

**Tanggal Fix:** 24 Desember 2025  
**Status:** ✅ DIPERBAIKI

---

## 🐛 MASALAH

Admin mendapat error 500 saat menyetujui request penarikan tunai:

```
PATCH http://127.0.0.1:8000/api/admin/penarikan-tunai/32/approve
Response: 500 (Internal Server Error)
```

---

## 🔍 ROOT CAUSE

### Error: Field Name Mismatch di AuditLog

Controller menggunakan field name yang salah saat membuat audit log:

```php
// ❌ SEBELUM (SALAH)
AuditLog::create([
    'user_id' => auth()->user()->user_id,        // Field tidak ada!
    'action_type' => 'approve_withdrawal',
    'resource_id' => $id,
    'old_values' => ['status' => 'pending'],
    'new_values' => ['status' => 'approved'],
    'ip_address' => request()->ip()
]);
```

**Penjelasan:**
- Tabel `audit_logs` menggunakan kolom `admin_id`, bukan `user_id`
- Missing required fields: `resource_type`, `user_agent`, `status`
- Menyebabkan SQL error saat insert ke database

---

## ✅ PERUBAHAN YANG DILAKUKAN

### File yang Diperbaiki

1. **app/Http/Controllers/Admin/AdminPenarikanTunaiController.php**
   - Method `approve()` - Line 179
   - Method `reject()` - Line 277
   - Method `destroy()` - Line 362

2. **app/Http/Controllers/UserController.php**
   - Method `uploadAvatar()` - Line 121

---

## 📝 DETAIL PERBAIKAN

### 1. Method approve() - Approve Penarikan Tunai

```php
// ❌ SEBELUM (SALAH)
AuditLog::create([
    'user_id' => auth()->user()->user_id,
    'action_type' => 'approve_withdrawal',
    'resource_id' => $id,
    'old_values' => ['status' => 'pending'],
    'new_values' => ['status' => 'approved'],
    'ip_address' => request()->ip()
]);

// ✅ SESUDAH (BENAR)
AuditLog::create([
    'admin_id' => auth()->user()->user_id,           // FIXED: user_id → admin_id
    'action_type' => 'approve_withdrawal',
    'resource_type' => 'PenarikanTunai',             // ADDED
    'resource_id' => $id,
    'old_values' => ['status' => 'pending'],
    'new_values' => ['status' => 'approved'],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),          // ADDED
    'status' => 'success'                            // ADDED
]);
```

### 2. Method reject() - Reject Penarikan Tunai

```php
// ✅ SESUDAH (BENAR)
AuditLog::create([
    'admin_id' => auth()->user()->user_id,
    'action_type' => 'reject_withdrawal',
    'resource_type' => 'PenarikanTunai',
    'resource_id' => $id,
    'old_values' => ['status' => 'pending'],
    'new_values' => ['status' => 'rejected'],
    'reason' => $validated['catatan_admin'],         // ADDED: Alasan reject
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'status' => 'success'
]);
```

### 3. Method destroy() - Delete Penarikan Tunai

```php
// ✅ SESUDAH (BENAR)
AuditLog::create([
    'admin_id' => auth()->user()->user_id,
    'action_type' => 'delete_withdrawal',
    'resource_type' => 'PenarikanTunai',
    'resource_id' => $id,
    'old_values' => $withdrawal->toArray(),
    'new_values' => [],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'status' => 'success'
]);
```

### 4. UserController::uploadAvatar()

```php
// ❌ SEBELUM (SALAH)
\App\Models\AuditLog::create([
    'admin_id' => $currentUser->user_id,
    'action' => 'upload_user_avatar',               // Field tidak ada!
    'resource_id' => $id,
    'changes' => ['foto_profil' => $path]           // Field tidak ada!
]);

// ✅ SESUDAH (BENAR)
\App\Models\AuditLog::create([
    'admin_id' => $currentUser->user_id,
    'action_type' => 'upload_user_avatar',          // FIXED
    'resource_type' => 'User',                      // ADDED
    'resource_id' => $id,
    'old_values' => ['foto_profil' => $user->getOriginal('foto_profil')],
    'new_values' => ['foto_profil' => $path],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'status' => 'success'
]);
```

---

## 📊 STRUKTUR TABEL audit_logs

```sql
CREATE TABLE audit_logs (
  audit_log_id    BIGINT PRIMARY KEY AUTO_INCREMENT,
  admin_id        BIGINT (FK → users.user_id),     -- ✅ BUKAN user_id!
  action_type     VARCHAR(255),                    -- ✅ BUKAN action!
  resource_type   VARCHAR(255),                    -- ✅ WAJIB
  resource_id     BIGINT,
  old_values      JSON,
  new_values      JSON,
  reason          TEXT,
  ip_address      VARCHAR(45),
  user_agent      TEXT,                            -- ✅ WAJIB
  status          VARCHAR(50),                     -- ✅ WAJIB
  error_message   TEXT,
  created_at      TIMESTAMP,
  updated_at      TIMESTAMP
);
```

---

## 📋 REQUEST FORMAT (TIDAK BERUBAH)

### Approve Penarikan Tunai

```javascript
PATCH /api/admin/penarikan-tunai/{id}/approve

Headers:
{
  "Authorization": "Bearer {admin_token}",
  "Content-Type": "application/json"
}

Body (Optional):
{
  "catatan_admin": "Transfer telah diproses"
}
```

### Reject Penarikan Tunai

```javascript
PATCH /api/admin/penarikan-tunai/{id}/reject

Headers:
{
  "Authorization": "Bearer {admin_token}",
  "Content-Type": "application/json"
}

Body:
{
  "catatan_admin": "Rekening tidak valid" // REQUIRED
}
```

---

## ✅ RESPONSE FORMAT (TIDAK BERUBAH)

### Success Response - Approve (200)

```json
{
  "success": true,
  "message": "Penarikan tunai berhasil disetujui",
  "data": {
    "id": 32,
    "status": "approved",
    "catatan_admin": "Transfer telah diproses",
    "processed_at": "2025-12-24T15:30:00.000000Z"
  }
}
```

### Success Response - Reject (200)

```json
{
  "success": true,
  "message": "Penarikan tunai ditolak dan poin dikembalikan",
  "data": {
    "id": 32,
    "status": "rejected",
    "catatan_admin": "Rekening tidak valid",
    "processed_at": "2025-12-24T15:30:00.000000Z",
    "points_refunded": 500
  }
}
```

### Error Responses

```json
// Already processed (400)
{
  "success": false,
  "message": "Penarikan sudah diproses sebelumnya"
}

// Not found (404)
{
  "success": false,
  "message": "Withdrawal record not found"
}

// Unauthorized (403)
{
  "success": false,
  "message": "Unauthorized - Admin role required"
}
```

---

## 🔄 FLOW APPROVAL PENARIKAN TUNAI

```
1. Admin klik approve di UI
   ↓
2. Frontend kirim PATCH request ke /api/admin/penarikan-tunai/{id}/approve
   ↓
3. Backend validate admin role
   ↓
4. Check penarikan status = pending
   ↓
5. BEGIN TRANSACTION
   ↓
6. Update penarikan_tunai:
   - status → approved
   - catatan_admin
   - processed_by → admin_id
   - processed_at → now()
   ↓
7. Create audit log ✅ FIXED
   ↓
8. Create notification untuk nasabah
   ↓
9. COMMIT TRANSACTION
   ↓
10. Return success response
```

---

## 🧪 TESTING

### Test Case 1: Approve Normal

```bash
curl -X PATCH http://127.0.0.1:8000/api/admin/penarikan-tunai/32/approve \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "catatan_admin": "Transfer berhasil diproses"
  }'
```

**Expected:** Status 200, penarikan approved, audit log created

### Test Case 2: Approve yang Sudah Diproses

```bash
# Approve withdrawal yang statusnya sudah "approved"
curl -X PATCH http://127.0.0.1:8000/api/admin/penarikan-tunai/32/approve \
  -H "Authorization: Bearer {admin_token}"
```

**Expected:** Status 400, message "Penarikan sudah diproses sebelumnya"

### Test Case 3: Reject dengan Refund Poin

```bash
curl -X PATCH http://127.0.0.1:8000/api/admin/penarikan-tunai/33/reject \
  -H "Authorization: Bearer {admin_token}" \
  -H "Content-Type: application/json" \
  -d '{
    "catatan_admin": "Data rekening tidak lengkap"
  }'
```

**Expected:** 
- Status 200
- Penarikan rejected
- Poin dikembalikan ke user
- Audit log created
- Notifikasi dikirim

---

## 📝 CATATAN UNTUK FRONTEND

### ✅ Tidak Ada Perubahan di Frontend

Frontend **TIDAK PERLU** melakukan perubahan apapun:
- ✅ Request format tetap sama
- ✅ Response format tetap sama
- ✅ Endpoint URL tetap sama
- ✅ Field names tetap sama

### 🎯 Yang Sudah Diperbaiki di Backend

1. ✅ Field AuditLog (`user_id` → `admin_id`)
2. ✅ Field AuditLog (`action` → `action_type`)
3. ✅ Field AuditLog (`changes` → `old_values` + `new_values`)
4. ✅ Tambah required fields: `resource_type`, `user_agent`, `status`
5. ✅ Fix di 3 method: approve, reject, destroy
6. ✅ Fix di UserController::uploadAvatar

---

## 📊 AUDIT LOG YANG DICATAT

Setelah fix ini, sistem akan mencatat audit trail untuk:

1. **approve_withdrawal** - Admin approve penarikan tunai
2. **reject_withdrawal** - Admin reject penarikan tunai (+ refund poin)
3. **delete_withdrawal** - Admin hapus penarikan pending
4. **upload_user_avatar** - Admin upload avatar user

Semua audit log mencatat:
- Admin yang melakukan action
- Timestamp aksi
- IP address & User agent
- Old values & New values
- Resource type & ID
- Status (success/failed)

---

## ✅ STATUS

**FIXED & TESTED**

Error 500 pada approval penarikan tunai sudah diperbaiki. Admin dapat melanjutkan proses approval tanpa error.

---

**End of Fix Report**
