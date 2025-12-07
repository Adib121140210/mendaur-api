# 🚀 Quick Reference: Dual-Nasabah + RBAC System

## At a Glance

### 3 Roles (Hierarchy)

```
SUPERADMIN (3) ← All permissions
    ↑ includes
  ADMIN (2) ← Manage users, approve transactions
    ↑ includes
NASABAH (1) ← Deposit, redeem, view badges
```

### 2 Nasabah Types (Conditional)

```
KONVENSIONAL          MODERN
├─ total_poin > 0     ├─ total_poin = 0 (always)
├─ poin usable ✅     ├─ poin NOT usable ❌
├─ can withdraw ✅    ├─ NO withdraw ❌
├─ can redeem ✅      ├─ NO redeem ❌
├─ badges work ✅     ├─ badges work ✅
└─ leaderboard ✅     └─ leaderboard ✅
```

### Dual-Poin Tracking

```
users.total_poin          users.poin_tercatat
├─ Updated for KONV only  ├─ Updated for BOTH
├─ = Usable balance       ├─ = Audit balance
├─ Displayed to user      ├─ For badges/leaderboard
├─ Deducted on redeem     └─ Never decreases (audit)
└─ 0 for MODERN
```

---

## Implementation Quick Links

| Task | File | Key Section |
|------|------|-------------|
| Database schema | `DATABASE_ERD_VISUAL_DETAILED.md` | ROLE-BASED ACCESS CONTROL LAYER |
| Laravel code | `ROLE_BASED_ACCESS_IMPLEMENTATION.md` | Section 1-9 |
| Integration | `DUAL_NASABAH_RBAC_INTEGRATION.md` | Architecture Overview |
| Roadmap | `IMPLEMENTATION_SUMMARY.md` | Implementation Roadmap |
| **YOU ARE HERE** | `QUICK_REFERENCE.md` | - |

---

## Checklist: What to Implement

### Database Layer

```sql
-- CREATE 3 new tables
CREATE TABLE roles (id, nama_role, level_akses)
CREATE TABLE role_permissions (id, role_id, permission)
CREATE TABLE audit_logs (id, admin_id, action_type, resource_type, ...)

-- MODIFY 3 tables
ALTER TABLE users ADD COLUMN (role_id, tipe_nasabah, poin_tercatat, ...)
ALTER TABLE log_aktivitas ADD COLUMN (poin_tercatat, poin_usable, source_tipe)
ALTER TABLE poin_transaksis ADD COLUMN (is_usable, reason_not_usable)

-- SEED initial data
INSERT INTO roles VALUES (1, 'nasabah', 1), (2, 'admin', 2), (3, 'superadmin', 3)
INSERT INTO role_permissions VALUES (...) -- 40+ rows
```

### Laravel Models

```php
// 4 models
class Role extends Model { ... }
class RolePermission extends Model { ... }
class AuditLog extends Model { ... }
class User extends Model { 
  // Add: role(), hasRole(), hasPermission(), isNasabahModern(), 
  //      getDisplayedPoin(), canUsePoinFeature(), etc
}
```

### Middleware

```php
// 2 middleware classes
class CheckPermission { public function handle(..., $permission) { ... } }
class CheckRole { public function handle(..., ...$roles) { ... } }

// Register in app/Http/Kernel.php
protected $routeMiddleware = [
  'permission' => CheckPermission::class,
  'role' => CheckRole::class,
];
```

### Routes

```php
// Wrap with middleware
Route::middleware(['permission:deposit_sampah'])->post('/deposits', ...)
Route::middleware(['role:admin,superadmin'])->post('/admin/...')
Route::middleware(['role:superadmin'])->post('/superadmin/...')
```

### Controllers

```php
// Update deposit approval:
public function approveDeposit($id) {
  $admin = Auth::user();
  $deposit = TabungSampah::find($id);
  
  // Get old values for audit
  $oldValues = ['status' => $deposit->status];
  
  // Update
  $deposit->status = 'approved';
  
  // Update user poin based on type
  if ($deposit->user->isNasabahKonvensional()) {
    $deposit->user->total_poin += $deposit->poin_didapat; // Usable
    $deposit->user->poin_tercatat += $deposit->poin_didapat;
  } else {
    $deposit->user->poin_tercatat += $deposit->poin_didapat; // Audit only
  }
  $deposit->user->save();
  
  // Create poin transaction
  PoinTransaksis::create([...]);
  
  // Log audit
  AuditLog::logAction($admin->id, 'approve_deposit', 'tabung_sampah', 
    $id, $oldValues, ['status' => 'approved'], $reason);
}
```

---

## Decision Points in Code

### When to check `tipe_nasabah`?

1. **On deposit approval** → Update poin accordingly
2. **On withdrawal request** → Deny if modern
3. **On product redemption** → Deny if modern
4. **On poin deduction** → From `total_poin` (konv only)
5. **On badge calculation** → Use `poin_tercatat` (both)

### When to use `total_poin` vs `poin_tercatat`?

```php
// USE total_poin FOR:
if ($user->total_poin >= $amount) { ... }  // Withdrawal validation
$user->total_poin -= $amount;               // Deduction

// USE poin_tercatat FOR:
$badge->current_value = $user->poin_tercatat;  // Badge progress
leaderboard.orderBy('poin_tercatat');          // Fair ranking

// DISPLAY TO USER:
if ($user->isNasabahModern()) {
  $displayedPoin = 0;  // Always 0
} else {
  $displayedPoin = $user->total_poin;  // Real balance
}
```

### When to create AUDIT_LOGS?

```php
// EVERY admin action:
AuditLog::create([
  'admin_id' => Auth::id(),
  'action_type' => 'approve_deposit|adjust_poin|create_admin|...',
  'resource_type' => 'tabung_sampah|user|...',
  'resource_id' => $id,
  'old_values' => ['field' => $oldValue],
  'new_values' => ['field' => $newValue],
  'reason' => $request->reason,  // Optional but recommended
  'ip_address' => request()->ip(),
  'user_agent' => request()->userAgent(),
  'status' => 'success',  // or 'failed'
  'error_message' => $error  // if failed
]);
```

---

## API Response Patterns

### Login (Show role + nasabah type)

```json
{
  "user": {
    "id": 5,
    "role": { "id": 1, "nama_role": "nasabah" },
    "tipe_nasabah": "konvensional",
    "total_poin": 250,
    "poin_tercatat": 250,
    "permissions": ["deposit_sampah", "redeem_poin", ...]
  }
}
```

### Deposit Approval (With audit)

```json
{
  "deposit": { "status": "approved" },
  "user_poin": {
    "total_poin": 150,
    "poin_tercatat": 150,
    "displayed_poin": 150
  },
  "audit_log": {
    "action": "approve_deposit",
    "admin_id": 10,
    "old": { "status": "pending" },
    "new": { "status": "approved" }
  }
}
```

### Withdrawal Denied (Modern nasabah)

```json
{
  "success": false,
  "error": "MODERN_NASABAH_CANNOT_WITHDRAW",
  "message": "Nasabah modern menerima transfer langsung. Poin hanya untuk badge."
}
```

---

## Testing Matrix

| Test | Nasabah Konv | Nasabah Modern | Admin | Superadmin |
|------|:---:|:---:|:---:|:---:|
| Deposit | ✅ | ✅ | ✅ | ✅ |
| Withdraw | ✅ | ❌ | approve | approve |
| Redeem | ✅ | ❌ | approve | approve |
| Badge progress | ✅ | ✅ | view | view |
| Approve deposit | ❌ | ❌ | ✅ | ✅ |
| Adjust poin | ❌ | ❌ | ✅ + reason | ✅ |
| Manage admin | ❌ | ❌ | ❌ | ✅ |
| View audit | ❌ | ❌ | ❌ | ✅ |

---

## SQL Queries (For Testing)

```sql
-- Check user role & type
SELECT id, nama, role_id, tipe_nasabah, total_poin, poin_tercatat 
FROM users WHERE id = 5;

-- Check if modern nasabah tried to withdraw
SELECT COUNT(*) FROM audit_logs 
WHERE admin_id IN (SELECT id FROM users WHERE tipe_nasabah='modern')
AND action_type='request_withdrawal';
-- Result should be 0 (blocked by middleware)

-- List all admin actions
SELECT admin_id, action_type, resource_type, created_at 
FROM audit_logs 
ORDER BY created_at DESC LIMIT 20;

-- Check role permissions
SELECT p.permission FROM role_permissions p
JOIN roles r ON p.role_id = r.id
WHERE r.nama_role = 'admin'
ORDER BY p.permission;
-- Result: ~23 permissions

-- Verify poin tracking
SELECT u.id, u.tipe_nasabah, u.total_poin, u.poin_tercatat,
  COUNT(pt.id) as transaksi_count,
  SUM(pt.poin_didapat) as sum_poin
FROM users u
LEFT JOIN poin_transaksis pt ON u.id = pt.user_id
GROUP BY u.id
LIMIT 10;
```

---

## Common Pitfalls to Avoid

❌ **DON'T:**
- Use `total_poin` for modern nasabah validation (will be 0)
- Update `total_poin` for modern nasabah deposits (should stay 0)
- Forget to check `tipe_nasabah` before redeem/withdraw
- Create audit logs without `admin_id` (must have actor)
- Forget `is_usable=false` for modern nasabah poin
- Use `total_poin` for leaderboard (use `poin_tercatat`)

✅ **DO:**
- Always use `poin_tercatat` for badge progress (both types)
- Always use `poin_tercatat` for leaderboard (fair ranking)
- Always check `tipe_nasabah` before feature access
- Always log admin actions with old/new values
- Always set `is_usable` based on nasabah type
- Always set `reason` field in audit logs

---

## Deployment Checklist

- [ ] Create & run migration
- [ ] Seed initial roles (3) + permissions (40+)
- [ ] Create middleware classes
- [ ] Register middleware in Kernel
- [ ] Update all routes with middleware
- [ ] Update all controllers with audit logging
- [ ] Test login → verify role + permissions in response
- [ ] Test deposit approval → verify poin update + audit log
- [ ] Test withdrawal (konv) → verify deduction
- [ ] Test withdrawal (modern) → verify 403 error
- [ ] Test badge progress → verify uses poin_tercatat
- [ ] Test leaderboard → verify fair ranking (both types)
- [ ] Test superadmin → verify audit logs visible
- [ ] Load test (1000+ users)
- [ ] Security audit (permission matrix)
- [ ] Deploy to production

---

## File Locations (Where to Code)

```
app/
├── Models/
│   ├── Role.php (NEW)
│   ├── RolePermission.php (NEW)
│   ├── AuditLog.php (NEW)
│   └── User.php (MODIFY - add role methods)
├── Http/
│   ├── Middleware/
│   │   ├── CheckPermission.php (NEW)
│   │   └── CheckRole.php (NEW)
│   └── Controllers/
│       ├── Api/
│       │   ├── DepositController.php (MODIFY - add audit)
│       │   ├── AdminController.php (NEW)
│       │   └── SuperAdminController.php (NEW)
│       └── Kernel.php (MODIFY - register middleware)
├── Services/
│   └── FeatureAccessService.php (NEW)
└── Listeners/
    ├── UpdateBadgeProgressOnTabungSampah.php (MODIFY)
    └── UpdateBadgeProgressOnPoinChange.php (MODIFY)

database/
├── migrations/
│   └── YYYY_MM_DD_create_rbac_tables.php (NEW)
└── seeders/
    └── RolePermissionSeeder.php (NEW)

routes/
└── api.php (MODIFY - add middleware)
```

---

## Performance Considerations

```php
// ✅ DO: Use eager loading
User::with('role.permissions')->find($id);

// ❌ DON'T: N+1 queries
User::find($id);  // Then access $user->role
              // Then access $role->permissions

// ✅ DO: Cache permissions
Cache::remember("user:{$id}:permissions", 3600, 
  fn() => User::find($id)->role->permissions);

// ❌ DON'T: Query permissions on every request
// Query 40+ permissions from DB for every API call

// ✅ DO: Index audit_logs queries
Index on (admin_id, created_at)
Index on (action_type, created_at)
Index on (resource_type, resource_id)

// ❌ DON'T: SELECT * FROM audit_logs
// 100,000+ rows will be slow without proper pagination
```

---

## Quick Troubleshooting

| Problem | Solution |
|---------|----------|
| 403 Forbidden | Check permission + role + role_id column exists |
| 401 Unauthorized | Check token valid + auth middleware applied |
| Modern can withdraw | Check `tipe_nasabah` in controller + middleware |
| Poin not updating | Check `isNasabahKonvensional()` logic in approval |
| Audit log missing | Check `AuditLog::create()` called after action |
| Badge not progressing | Check using `poin_tercatat` not `total_poin` |
| Leaderboard incorrect | Check sorting by `poin_tercatat` DESC |
| Permission denied | Verify permission exists + seeded in DB |

---

## Support

**Questions about:**
- Database schema → See `DATABASE_ERD_VISUAL_DETAILED.md`
- Code implementation → See `ROLE_BASED_ACCESS_IMPLEMENTATION.md`
- Business logic → See `DUAL_NASABAH_RBAC_INTEGRATION.md`
- Timeline → See `IMPLEMENTATION_SUMMARY.md`
- This page → You're reading it! 📖

---

**Status:** 🟢 **READY TO BUILD**

Semua detail ada. Tim development dapat langsung mulai coding berdasarkan checklist & files ini.

Total setup time: **~2-3 weeks** (migration → models → middleware → controllers → testing → deploy)

Good luck! 🚀
