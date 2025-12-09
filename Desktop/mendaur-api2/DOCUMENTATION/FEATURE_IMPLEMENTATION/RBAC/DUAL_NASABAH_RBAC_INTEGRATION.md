# 🎯 Complete Integration: Dual-Nasabah Model + Role-Based Access Control

## Overview

Sistem MENDAUR sekarang mendukung 2 model bisnis (nasabah konvensional vs modern) dengan 3 level akses pengguna (nasabah, admin, superadmin), terintegrasi dengan sistem poin dual-track dan audit logging lengkap.

---

## 1. Architecture Overview

```
┌────────────────────────────────────────────────────────────────┐
│                    MENDAUR SYSTEM ARCHITECTURE                │
└────────────────────────────────────────────────────────────────┘

                        ┌──────────────┐
                        │   USERS      │
                        │  (Central)   │
                        └──────┬───────┘
                               │
                ┌──────────────┼──────────────┐
                │              │              │
                ▼              ▼              ▼
        ┌────────────┐  ┌──────────────┐  ┌─────────────┐
        │   role_id  │  │ tipe_nasabah │  │ poin fields │
        ├────────────┤  ├──────────────┤  ├─────────────┤
        │ 1: nasabah │  │ konvensional │  │ total_poin  │
        │ 2: admin   │  │ modern       │  │ poin_tercatat│
        │ 3: superadmin                  │ poin_usable │
        └────────────┘  └──────────────┘  └─────────────┘
             │                                      │
             │                                      │
      [PERMISSION CHECK]          [FEATURE ACCESS CONTROL]
             │                                      │
        ┌────▼────┐                      ┌──────────▼──────────┐
        │ ROLES   │                      │   DUAL POIN MODEL   │
        │ table   │                      ├─────────────────────┤
        └────▼────┘                      │                     │
             │                           │ KONVENSIONAL:      │
        ┌────▼──────────────┐            │ • poin = usable    │
        │ ROLE_PERMISSIONS  │            │ • displayed = YES  │
        │ table (40+ perms) │            │ • can redeem       │
        └────▼──────────────┘            │ • can withdraw     │
             │                           │                     │
        [MIDDLEWARE CHECK]               │ MODERN:            │
             │                           │ • poin = recorded  │
        ✅/❌ ALLOW/DENY                │ • displayed = 0    │
                                         │ • NO redeem/draw   │
                                         │ • badges OK        │
                                         └─────────────────────┘
                                                   │
                                    ┌──────────────┼──────────────┐
                                    │              │              │
                                    ▼              ▼              ▼
                         [DEPOSIT]  [BADGES]  [LEADERBOARD]
                         • Tercatat • Tercatat • Fair ranking
                         • usable   • Works    • Both types
```

---

## 2. User Registration Flow

```
┌─────────────────────────────────────────────────────────┐
│  NEW USER REGISTRATION FLOW                             │
└─────────────────────────────────────────────────────────┘

User registers with data:
{
  "nama": "Budi",
  "no_hp": "081234567890",
  "email": "budi@example.com",
  "password": "...",
  "alamat": "Jl. Merdeka",
  "tipe_nasabah": "konvensional" OR "modern"  ← Business decides
}
        │
        ▼
┌──────────────────────────┐
│ Create user record:      │
├──────────────────────────┤
│ • id: auto               │
│ • nama: Budi             │
│ • role_id: 1 (nasabah) ✅ │
│ • tipe_nasabah: input    │
│ • total_poin: 0          │
│ • poin_tercatat: 0       │
│                          │
│ IF tipe='modern':        │
│ • nama_bank: nullable    │
│ • nomor_rekening: input  │
│ • atas_nama: input       │
└──────────────────────────┘
        │
        ▼
┌──────────────────────────┐
│ Create badge_progress:   │
│ FOR EACH badge:          │
│ • user_id: new_user.id   │
│ • badge_id: badge        │
│ • current_value: 0       │
│ • is_unlocked: false     │
└──────────────────────────┘
        │
        ▼
✅ User ready to use system
   (with role=nasabah)
```

---

## 3. Feature Access Decision Tree

### 3.1 Deposit Feature (All nasabah can deposit)

```
USER REQUESTS: POST /api/deposits
        │
        ▼
┌─────────────────────┐
│ Middleware check:   │
│ Auth verified?      │
└──────┬──────────────┘
      /  \
    NO    YES
    │      ▼
    │  ┌──────────────────┐
    │  │ Permission check │
    │  │ has 'deposit...? │
    │  └──────┬───────────┘
    │        /  \
    │      NO    YES
    │      │      ▼
    │      │  ┌──────────────┐
    │      │  │ Role check:  │
    │      │  │ role_id>=1?  │
    │      │  └──────┬───────┘
    │      │        /  \
    │      │      NO    YES
    │      │      │      ▼
    │      │      │  ┌────────────────────┐
    │      │      │  │ Is nasabah type    │
    │      │      │  │ konvensional OR    │
    │      │      │  │ modern?            │
    │      │      │  └────────┬───────────┘
    │      │      │          /  \
    │      │      │        NO    YES
    │      │      │        │      ▼
    │      └──────┴────────┘   ✅ ALLOW
    │      │                   DEPOSIT
    │      ▼                   (poin calculated
    │  ❌ 403 FORBIDDEN          based on type)
    │
    ▼
❌ 401 UNAUTHORIZED
```

### 3.2 Withdrawal Feature (Only Konvensional)

```
USER REQUESTS: POST /api/withdrawals/request
        │
        ▼
┌──────────────────────┐
│ 1. Basic auth check  │
└──────┬───────────────┘
      /  \
    NO    YES
    │      ▼
    │  ┌─────────────────────────┐
    │  │ 2. Permission check:    │
    │  │ 'request_withdrawal'?   │
    │  └──────┬──────────────────┘
    │        /  \
    │      NO    YES
    │      │      ▼
    │      │  ┌──────────────────────┐
    │      │  │ 3. Nasabah type:     │
    │      │  │ is 'konvensional'?   │
    │      │  └──────┬───────────────┘
    │      │        /  \
    │      │      NO    YES
    │      │      │      ▼
    │      │      │  ┌──────────────────┐
    │      │      │  │ 4. Poin balance: │
    │      │      │  │ >= requested?    │
    │      │      │  └──────┬───────────┘
    │      │      │        /  \
    │      │      │      NO    YES
    │      │      │      │      ▼
    │      │      │      │  ✅ CREATE WITHDRAWAL
    │      │      │      │  (poin deducted,
    │      │      │      │   status=pending,
    │      │      │      │   needs admin approval)
    │      │      │      │
    │      │      │      ▼ Audit log created
    │      │      │      ┌──────────────────────┐
    │      │      │      │ AUDIT_LOGS entry:    │
    │      │      │      │ action: request_w... │
    │      │      │      │ admin_id: null       │
    │      │      │      │ user: regular user   │
    │      │      │      │ amount: requested    │
    │      │      │      └──────────────────────┘
    │      └──────┘                │
    │      │                       ▼
    │      └──────────┬─────────────────────┐
    │                 │                     │
    │                 ▼                     ▼
    │             ❌ INSUFFICIENT        ❌ NOT KONVENSIONAL
    │             POIN                   (Modern nasabah cannot
    │             (error response)       withdraw - feature restricted)
    │
    ▼
❌ ERROR RESPONSE
```

### 3.3 Admin Approval Flow

```
ADMIN REQUESTS: POST /api/admin/withdrawals/{id}/approve
        │
        ▼
┌────────────────────────────┐
│ 1. Auth & role check:      │
│ user.role_id >= 2?         │
└────────┬───────────────────┘
        /  \
      NO    YES
      │      ▼
      │  ┌──────────────────────┐
      │  │ 2. Permission check: │
      │  │ 'approve_withdrawal' │
      │  └────────┬─────────────┘
      │          /  \
      │        NO    YES
      │        │      ▼
      │        │  ┌─────────────────────┐
      │        │  │ 3. Audit logging:   │
      │        │  │ Record old/new vals │
      │        │  └─────────┬───────────┘
      │        │            ▼
      │        │  ┌────────────────────────────┐
      │        │  │ 4. Update withdrawal:      │
      │        │  │ status='approved'          │
      │        │  │ processed_by=admin.id      │
      │        │  │ processed_at=now           │
      │        │  └─────────┬──────────────────┘
      │        │            ▼
      │        │  ┌────────────────────────────┐
      │        │  │ 5. AUDIT_LOGS entry:       │
      │        │  │ action: approve_withdraw   │
      │        │  │ admin_id: admin.id ✅      │
      │        │  │ resource: penarikan_tunai  │
      │        │  │ reason: (optional)         │
      │        │  │ ip_address: captured       │
      │        │  │ user_agent: captured       │
      │        │  │ status: success            │
      │        │  │ old: {status: pending}     │
      │        │  │ new: {status: approved}    │
      │        │  └─────────┬──────────────────┘
      │        │            ▼
      │        │  ✅ WITHDRAWAL APPROVED
      │        │  (Ready for bank transfer)
      │        │
      └────────┘
      │
      ▼
❌ ERROR RESPONSE
```

---

## 4. Poin Tracking for Dual-Nasabah

### 4.1 When Konvensional Deposits (5kg = +50 poin)

```
DEPOSIT APPROVED (admin action):
        │
        ▼
┌──────────────────────────┐
│ Get user data:           │
│ tipe_nasabah='konven...' │
│ total_poin: 100          │
│ poin_tercatat: 150       │
└──────────┬───────────────┘
           ▼
┌───────────────────────────────────────┐
│ CREATE poin_transaksis:               │
│ • poin_didapat: +50                   │
│ • sumber: 'setor_sampah'              │
│ • is_usable: TRUE ✅ (can use)         │
│ • reason_not_usable: NULL             │
└───────────────────────────────────────┘
           │
           ▼ UPDATE poin_transaksis
┌───────────────────────────────────────┐
│ UPDATE users (konvensional):           │
│                                       │
│ total_poin: 100 → 150 ✅ (INCREASED) │
│ (This is USABLE poin)                 │
│                                       │
│ poin_tercatat: 150 → 200 ✅          │
│ (This is for audit/badges)            │
└───────────────────────────────────────┘
           │
           ▼ UPDATE log_aktivitas
┌───────────────────────────────────────┐
│ CREATE log_aktivitas:                 │
│ • poin_perubahan: +50                 │
│ • poin_tercatat: 200 (audit)         │
│ • poin_usable: 150 (actual balance)   │
│ • source_tipe: 'setor_sampah'         │
└───────────────────────────────────────┘
           │
           ▼ UPDATE badge_progress
┌───────────────────────────────────────┐
│ FOR EACH badge (poin type):           │
│ current_value = user.poin_tercatat    │
│ progress_percentage = (current÷target)│
│ → Trigger unlock if 100%              │
└───────────────────────────────────────┘
           │
           ▼
✅ DEPOSIT FULLY PROCESSED
   Display to user: "Anda mendapat 50 poin!"
```

### 4.2 When Modern Deposits (5kg = +50 poin tercatat)

```
DEPOSIT APPROVED (admin action):
        │
        ▼
┌──────────────────────────┐
│ Get user data:           │
│ tipe_nasabah='modern'    │
│ total_poin: 0            │
│ poin_tercatat: 100       │
└──────────┬───────────────┘
           ▼
┌───────────────────────────────────────┐
│ CREATE poin_transaksis:               │
│ • poin_didapat: +50                   │
│ • sumber: 'setor_sampah'              │
│ • is_usable: FALSE ❌ (recorded only) │
│ • reason_not_usable: 'nasabah_...     │
│   modern_restricted'                  │
└───────────────────────────────────────┘
           │
           ▼ UPDATE poin_transaksis
┌───────────────────────────────────────┐
│ UPDATE users (modern):                │
│                                       │
│ total_poin: 0 → 0 ❌ (UNCHANGED!)    │
│ (Modern: poin TIDAK bisa dipakai)    │
│                                       │
│ poin_tercatat: 100 → 150 ✅          │
│ (This is ONLY for badges/leaderboard)│
└───────────────────────────────────────┘
           │
           ▼ UPDATE log_aktivitas
┌───────────────────────────────────────┐
│ CREATE log_aktivitas:                 │
│ • poin_perubahan: +50                 │
│ • poin_tercatat: 150 (recorded)      │
│ • poin_usable: 0 (not usable)        │
│ • source_tipe: 'setor_sampah'         │
└───────────────────────────────────────┘
           │
           ▼ UPDATE badge_progress
┌───────────────────────────────────────┐
│ FOR EACH badge (poin type):           │
│ current_value = user.poin_tercatat    │
│ → Use SAME calculation as konvensional│
│ → Both types can progress on badges! │
│ → Trigger unlock if 100%              │
└───────────────────────────────────────┘
           │
           ▼ ADMIN TRANSFER
┌───────────────────────────────────────┐
│ Separate process (bank transfer):     │
│ Admin manually transfer ke rekening   │
│ nasabah (tidak otomatis)              │
│ PAYMENT_TRANSAKSIS record created     │
└───────────────────────────────────────┘
           │
           ▼
✅ DEPOSIT FULLY PROCESSED
   Display to user: 
   "Poin Anda tercatat (untuk badge).
    Admin akan transfer ke rekening."
```

---

## 5. Feature Permission Matrix Summary

| Feature | Nasabah Konv | Nasabah Modern | Admin | Superadmin |
|---------|:---:|:---:|:---:|:---:|
| **Deposit Sampah** | ✅ | ✅ | ✅ | ✅ |
| **View Poin** | ✅ (real) | ✅ (0 shown) | ✅ | ✅ |
| **Withdraw Poin** | ✅ | ❌ | ✅ approve | ✅ |
| **Redeem Product** | ✅ | ❌ | ✅ approve | ✅ |
| **View Badges** | ✅ | ✅ | ✅ | ✅ |
| **View Leaderboard** | ✅ | ✅ | ✅ | ✅ |
| **Approve Deposit** | ❌ | ❌ | ✅ | ✅ |
| **Adjust Poin** | ❌ | ❌ | ✅ + reason | ✅ |
| **Manage Admins** | ❌ | ❌ | ❌ | ✅ |
| **View Audit Logs** | ❌ | ❌ | ❌ | ✅ |

---

## 6. API Response Examples

### 6.1 Login Response (shows user role + nasabah type)

```json
{
  "success": true,
  "message": "Login successful",
  "token": "...",
  "user": {
    "id": 5,
    "nama": "Budi Wijaya",
    "no_hp": "081234567890",
    "email": "budi@example.com",
    "total_poin": 250,
    "poin_tercatat": 250,
    "displayed_poin": 250,
    "role": {
      "id": 1,
      "nama_role": "nasabah",
      "level_akses": 1
    },
    "tipe_nasabah": "konvensional",
    "permissions": [
      "deposit_sampah",
      "redeem_poin",
      "request_withdrawal",
      "view_own_badges",
      ...
    ]
  }
}
```

### 6.2 Deposit Approval Response (with audit log)

```json
{
  "success": true,
  "message": "Deposit berhasil disetujui",
  "deposit": {
    "id": 123,
    "user_id": 5,
    "status": "approved",
    "berat_kg": 5,
    "poin_didapat": 50,
    "approved_at": "2025-11-27T14:30:00Z",
    "approved_by": 10
  },
  "user_poin_update": {
    "total_poin": 300,
    "poin_tercatat": 300,
    "displayed_poin": 300,
    "poin_change": +50
  },
  "audit_log": {
    "id": 1001,
    "admin_id": 10,
    "action_type": "approve_deposit",
    "resource_type": "tabung_sampah",
    "resource_id": 123,
    "old_values": {
      "status": "pending",
      "poin_didapat": 50
    },
    "new_values": {
      "status": "approved"
    },
    "reason": "Verified weight manually",
    "ip_address": "192.168.1.100",
    "created_at": "2025-11-27T14:30:00Z"
  }
}
```

### 6.3 Withdrawal Request Denied (Modern nasabah)

```json
{
  "success": false,
  "message": "Feature tidak tersedia untuk nasabah modern",
  "error_code": "MODERN_NASABAH_CANNOT_WITHDRAW",
  "details": {
    "user_id": 6,
    "tipe_nasabah": "modern",
    "reason": "Nasabah modern menerima transfer langsung dari admin. Poin hanya untuk badge dan leaderboard.",
    "contact_support": "Hubungi admin untuk bantuan"
  }
}
```

---

## 7. Superadmin Monitoring Dashboard

```
SUPERADMIN DASHBOARD SHOWS:

┌─────────────────────────────────────────────────┐
│ System Overview                                 │
├─────────────────────────────────────────────────┤
│ Total Users:        2,450                       │
│ ├─ Nasabah:        2,400                       │
│ │  ├─ Konvensional: 1,800 (75%)                │
│ │  └─ Modern:        600 (25%)                 │
│ ├─ Admin:              40                      │
│ └─ Superadmin:         10                      │
│                                                 │
│ Recent Admin Actions (Last 10):                 │
│ ┌────────────────────────────────────────────┐ │
│ │ Time    │ Admin    │ Action          │ OK? │ │
│ ├────────────────────────────────────────────┤ │
│ │ 14:35   │ Admin#5  │ approve_deposit │  ✅ │ │
│ │ 14:30   │ Admin#3  │ adjust_poin     │  ✅ │ │
│ │ 14:25   │ Admin#7  │ reject_withdraw │  ✅ │ │
│ │ 14:20   │ Admin#2  │ approve_withdraw│  ❌ │ │
│ │ ...     │ ...      │ ...             │ ... │ │
│ └────────────────────────────────────────────┘ │
│                                                 │
│ System Health:                                  │
│ ├─ Database Status:    ✅ OK                   │
│ ├─ API Response Time:  125ms                   │
│ ├─ Failed Requests:    0.2%                    │
│ └─ Cache Hit Rate:     94%                     │
└─────────────────────────────────────────────────┘
```

---

## 8. Testing Checklist

```
✅ AUTHENTICATION & AUTHORIZATION
  □ Login returns correct role + permissions
  □ Invalid token returns 401
  □ Expired token returns 401
  □ Token refresh works

✅ NASABAH FEATURES
  □ Konvensional can deposit
  □ Modern can deposit
  □ Konvensional can withdraw (poin > 0)
  □ Modern CANNOT withdraw (poin = 0)
  □ Konvensional can redeem products
  □ Modern CANNOT redeem products
  □ Both can view badges
  □ Both can view leaderboard
  □ Both can view own profile

✅ ADMIN FEATURES
  □ Can approve deposits
  □ Can reject withdrawals
  □ Can manually adjust poin (with reason)
  □ Cannot manage other admins

✅ SUPERADMIN FEATURES
  □ Can create new admin
  □ Can edit admin data
  □ Can delete admin
  □ Can view all audit logs
  □ Can view financial reports

✅ DUAL-NASABAH POIN MODEL
  □ Konvensional: total_poin increases on deposit
  □ Modern: total_poin stays 0 on deposit
  □ Konvensional: displayed_poin = total_poin
  □ Modern: displayed_poin = 0
  □ Both: poin_tercatat increases (for badges)
  □ Both: badge progress calculated from poin_tercatat
  □ Konvensional: withdrawal deducts from total_poin
  □ Modern: withdrawal blocked with clear message

✅ AUDIT LOGGING
  □ All admin actions logged in audit_logs
  □ IP address captured
  □ User agent captured
  □ Old values recorded
  □ New values recorded
  □ Reason field populated
  □ Failed attempts logged
  □ Superadmin can view all audit logs
  □ Audit logs immutable (no updates)

✅ ERROR HANDLING
  □ Permission denied: 403 response
  □ Not authenticated: 401 response
  □ Invalid input: 422 response
  □ Server error: 500 response
  □ All with clear error messages
```

---

## 9. Migration & Deployment

### Step 1: Run Migration

```bash
php artisan migrate
```

### Step 2: Seed Initial Roles

```bash
php artisan db:seed --class=RolePermissionSeeder
```

### Step 3: Verify Setup

```bash
# Check roles created
php artisan tinker
> Role::all();

# Check permissions created
> RolePermission::count(); // Should be ~40+

# Check users have role_id
> User::first();
```

### Step 4: Update Existing Users

```php
// In artisan command or seeder:
User::where('role_id', null)->update(['role_id' => 1]); // nasabah
```

### Step 5: Register Middleware in routes

```php
// In routes/api.php
Route::middleware(['permission:deposit_sampah'])->post('/deposits', ...);
```

---

## 10. Quick Reference

**3 Roles:**
- `nasabah` (role_id=1): Regular user
- `admin` (role_id=2): Bank staff
- `superadmin` (role_id=3): System manager

**2 Nasabah Types:**
- `konvensional`: Poin usable, can withdraw/redeem
- `modern`: Poin recorded only, cannot withdraw/redeem, gets bank transfer

**Poin Fields:**
- `total_poin`: Usable balance (konvensional only)
- `poin_tercatat`: Audit balance (both, used for badges/leaderboard)
- `poin_usable`: Display balance (0 for modern, total_poin for konv)

**Audit Trail:**
- `audit_logs`: Track all admin actions
- `log_aktivitas`: Track all user activities with poin changes
- `poin_transaksis`: Point transaction ledger with usability flag

**40+ Permissions** organized by feature:
- Deposits, redemptions, withdrawals
- Admin approvals, poin adjustment
- User & admin management
- Reports & analytics
- System settings

---

## Files Created

1. `DATABASE_ERD_VISUAL_DETAILED.md` - Updated with ROLES + ROLE_PERMISSIONS + AUDIT_LOGS tables + permission matrix
2. `ROLE_BASED_ACCESS_IMPLEMENTATION.md` - Complete Laravel implementation guide
3. `DUAL_NASABAH_RBAC_INTEGRATION.md` - This file (integration overview)

Ready for implementation! 🚀
