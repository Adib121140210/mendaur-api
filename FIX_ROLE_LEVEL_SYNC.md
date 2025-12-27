# Fix: Role-Level Synchronization

## 🐛 Problem

Kolom `level` pada tabel `users` menyimpan nilai string yang berbeda tergantung konteks:
- **Admin/Superadmin**: `'admin'`, `'superadmin'` (role-based)
- **Nasabah**: `'bronze'`, `'silver'`, `'gold'` (achievement-based)

Ketika superadmin mengubah `role_id` user melalui admin dashboard, kolom `level` **TIDAK ikut berubah**, menyebabkan **inkonsistensi data**.

### Contoh Masalah:
```
User A (Nasabah dengan level='gold')
↓ Admin change role to Admin
User A (role_id=Admin, level='gold') ❌ SALAH!
Seharusnya: level='admin'
```

## ✅ Solution

Tambahkan **auto-sync logic** di method `updateRole()` pada kedua controller:
- `AdminUserController.php`
- `AdminUserController_RBAC.php`

### Logic Mapping:

| Role (level_akses) | Action | New Level Value |
|-------------------|--------|----------------|
| **Superadmin (3)** | Always set | `'superadmin'` |
| **Admin (2)** | Always set | `'admin'` |
| **Nasabah (1)** | Preserve achievement | Keep `'bronze'/'silver'/'gold'` if valid, else `'bronze'` |

### Key Changes:

**AdminUserController.php**:
```php
// Determine new level based on role's level_akses
$updateData = ['role_id' => $validated['role_id']];

switch ($newRole->level_akses) {
    case 3: // Superadmin
        $updateData['level'] = 'superadmin';
        break;
    case 2: // Admin
        $updateData['level'] = 'admin';
        break;
    case 1: // Nasabah
    default:
        // Preserve achievement level (bronze/silver/gold)
        $validNasabahLevels = ['bronze', 'silver', 'gold'];
        if (!in_array($user->level, $validNasabahLevels)) {
            $updateData['level'] = 'bronze'; // Default
        }
        break;
}

$user->update($updateData);
```

**AdminUserController_RBAC.php**:
```php
// Added helper method
private function getLevelFromRole($role) {
    switch ($role->level_akses) {
        case 3: return 'superadmin';
        case 2: return 'admin';
        case 1: return null; // Preserve nasabah achievement level
    }
}

// In updateRole():
$newLevel = $this->getLevelFromRole($newRole);

if ($newLevel !== null) {
    $updateData['level'] = $newLevel;
} elseif ($newRole->level_akses === 1) {
    // Preserve nasabah achievement level
    if (!in_array($targetUser->level, ['bronze', 'silver', 'gold'])) {
        $updateData['level'] = 'bronze';
    }
}
```

## 📊 API Response Changes

**Before**:
```json
{
  "user_id": 123,
  "role_id": 2,
  "role_name": "Admin"
}
```

**After**:
```json
{
  "user_id": 123,
  "role_id": 2,
  "role_name": "Admin",
  "level": "admin",
  "level_updated": true
}
```

## 🧪 Testing

Run test script:
```bash
php test_role_level_sync.php
```

Test scenarios:
1. ✅ Nasabah (achievement level) → Admin (level='admin')
2. ✅ Admin → Superadmin (level='superadmin')
3. ✅ Superadmin → Nasabah (preserve achievement level)

## 📝 Database Schema Reference

**users table**:
```sql
role_id INT -- FK to roles.role_id
level VARCHAR(20) -- 'admin'|'superadmin'|'bronze'|'silver'|'gold'
```

**roles table**:
```sql
role_id INT PRIMARY KEY
nama_role VARCHAR
level_akses INT -- 1=nasabah, 2=admin, 3=superadmin
```

## 🚨 Important Notes

1. **Nasabah Achievement Levels**: Bronze/Silver/Gold are preserved when switching between nasabah roles
2. **Admin Roles**: Always override level to 'admin' or 'superadmin'
3. **Audit Log**: Records both role_id AND level changes
4. **No Breaking Changes**: Existing API contracts maintained

## 🔄 Migration Considerations

If database has existing inconsistent data, run cleanup:
```sql
-- Fix admin users with wrong level
UPDATE users 
SET level = 'admin' 
WHERE role_id IN (SELECT role_id FROM roles WHERE level_akses = 2)
  AND level NOT IN ('admin');

-- Fix superadmin users
UPDATE users 
SET level = 'superadmin' 
WHERE role_id IN (SELECT role_id FROM roles WHERE level_akses = 3)
  AND level NOT IN ('superadmin');

-- Fix nasabah users with admin/superadmin level
UPDATE users 
SET level = 'bronze' 
WHERE role_id IN (SELECT role_id FROM roles WHERE level_akses = 1)
  AND level IN ('admin', 'superadmin');
```
