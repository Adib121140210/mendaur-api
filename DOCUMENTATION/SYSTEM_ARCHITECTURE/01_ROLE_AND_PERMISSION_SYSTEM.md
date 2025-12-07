# 🔐 Role & Permission System Setup Guide

## 📋 System Overview

Mendaur API menggunakan **Role-Based Access Control (RBAC)** untuk mengelola permissions dan access control. Sistem ini fully terintegrasi dengan database dan seeding mechanism.

---

## 🎭 Role Hierarchy

```
┌─────────────────────────────────────────────────────────┐
│                    ROLE HIERARCHY                       │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  SUPERADMIN (Level 3) ★★★                              │
│  ├─ 62 Permissions (includes Admin + 22 extra)         │
│  ├─ Can manage everything including admin accounts     │
│  └─ Full system access                                 │
│                                                         │
│  ADMIN (Level 2) ★★                                    │
│  ├─ 40 Permissions (includes Nasabah + 23 extra)       │
│  ├─ Can manage nasabah and deposits                    │
│  └─ Admin dashboard access                             │
│                                                         │
│  NASABAH (Level 1) ★                                   │
│  ├─ 17 Permissions (basic user operations)             │
│  ├─ Can deposit sampah, redeem poin, edit profile      │
│  └─ Regular user dashboard access                      │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 📊 Current Test Accounts Setup

### ✅ Admin Testing Account
```
Email:      admin@test.com
Password:   admin123
Name:       Admin Testing
Role ID:    2
Role:       admin
Tipe:       konvensional
Status:     ✅ ACTIVE
Permissions: 40
```

### ✅ Superadmin Testing Account
```
Email:          superadmin@test.com
Password:       superadmin123
Name:           Superadmin Testing
Role ID:        3
Role:           superadmin
Tipe:           konvensional
Status:         ✅ ACTIVE
Permissions:    62
```

### ✅ Regular Nasabah Accounts
```
1. Adib Surya (adib@example.com / password)
   - Role: nasabah
   - Level: Bronze
   - Total Poin: 150
   - Tipe: konvensional

2. Siti Aminah (siti@example.com / password)
   - Role: nasabah
   - Level: Silver
   - Total Poin: 2000
   - Tipe: konvensional

3. Budi Santoso (budi@example.com / password)
   - Role: nasabah
   - Level: Pemula
   - Total Poin: 50
   - Tipe: konvensional

4. Reno Wijaya (reno@example.com / password)
   - Role: nasabah
   - Level: Gold
   - Tipe: modern (with banking info)
   - Account: BNI 1234567890

5. Rina Kusuma (rina@example.com / password)
   - Role: nasabah
   - Level: Platinum
   - Tipe: modern (with banking info)
   - Account: MANDIRI 9876543210

6. test (test@test.com / test)
   - Role: nasabah
   - Level: Bronze
   - Total Poin: 1000
   - Tipe: konvensional
```

---

## 🔧 Implementation Details

### Database Structure

#### `roles` Table
```php
Columns:
- id (Primary Key)
- nama_role (varchar): 'nasabah', 'admin', 'superadmin'
- level_akses (int): 1, 2, 3 respectively
- deskripsi (text): Role description
- created_at, updated_at
```

#### `users` Table (Role Assignment)
```php
Columns:
- id (Primary Key)
- role_id (Foreign Key → roles.id)  // NEW - Associates user with role
- nama, email, password, etc.
- level (varchar): 'Pemula', 'Bronze', 'Silver', 'Gold', 'Platinum', 'Admin', 'Superadmin'
- tipe_nasabah: 'konvensional' or 'modern'
```

#### `role_permissions` Table (Permission Association)
```php
Columns:
- id (Primary Key)
- role_id (Foreign Key → roles.id)
- permission_id (Foreign Key → permissions.id)
- created_at, updated_at
```

### Permission System

#### Permission Inheritance
```
SUPERADMIN gets:
✅ All NASABAH permissions (17)
✅ All ADMIN permissions (40)
✅ 22 additional SUPERADMIN-only permissions
= Total: 62 permissions

ADMIN gets:
✅ All NASABAH permissions (17)
✅ 23 additional ADMIN-only permissions
= Total: 40 permissions

NASABAH gets:
✅ 17 core permissions
= Total: 17 permissions
```

#### Sample Permissions by Category

**NASABAH (17 total):**
- `deposit_sampah` - Can deposit waste
- `view_deposit_history` - Can view deposit history
- `view_balance` - Can check balance
- `edit_profile` - Can edit own profile
- `redeem_poin` - Can redeem points
- And 12 more...

**ADMIN (40 total - includes all nasabah):**
- All 17 nasabah permissions
- `manage_nasabah` - Can manage nasabah accounts
- `view_nasabah_activity` - Can view user activity
- `approve_withdrawal` - Can approve poin withdrawals
- `manage_deposits` - Can manage depositan
- `view_reports` - Can view system reports
- And 18 more...

**SUPERADMIN (62 total - includes all admin):**
- All 40 admin permissions
- All 17 nasabah permissions
- `manage_admin` - Can manage admin accounts
- `manage_roles` - Can manage roles & permissions
- `system_settings` - Can access system settings
- `audit_logs` - Can view audit logs
- `manage_badges` - Can manage badge system
- And 17 more...

---

## 🚀 How to Use Role System

### 1️⃣ **Check User Role**
```php
$user = User::find(1);

// Check if user has specific role
if ($user->hasRole('admin')) {
    // User is admin
}

// Check if user has any of given roles
if ($user->hasAnyRole('admin', 'superadmin')) {
    // User is admin or superadmin
}
```

### 2️⃣ **Check User Permission**
```php
$user = User::find(1);

// Check if user has specific permission
if ($user->hasPermission('manage_nasabah')) {
    // User can manage nasabah
}

// Check if user has all permissions
if ($user->hasAllPermissions('manage_nasabah', 'view_reports')) {
    // User can do both
}

// Check if user has any permission
if ($user->hasAnyPermission('manage_nasabah', 'manage_deposits')) {
    // User can do at least one
}
```

### 3️⃣ **In Controllers**
```php
// Check admin access
public function adminDashboard() {
    if (!auth()->user()->hasRole('admin', 'superadmin')) {
        abort(403, 'Unauthorized');
    }
    // Show admin dashboard
}

// Check specific permission
public function manageUsers() {
    if (!auth()->user()->hasPermission('manage_nasabah')) {
        abort(403, 'Permission Denied');
    }
    // Show user management
}
```

### 4️⃣ **In Routes (Middleware)**
```php
Route::middleware(['auth', 'admin'])->group(function () {
    Route::post('/admin/manage-users', [AdminController::class, 'manageUsers']);
    Route::post('/admin/approve-withdrawal', [AdminController::class, 'approveWithdrawal']);
});

Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::post('/admin/manage-admin', [AdminController::class, 'manageAdmin']);
    Route::post('/admin/manage-roles', [AdminController::class, 'manageRoles']);
});
```

---

## 📝 File Structure

### Key Files

**1. Models**
- `app/Models/User.php` - User model with role relationship & permission methods
- `app/Models/Role.php` - Role model with permission inheritance
- `app/Models/RolePermission.php` - Permission model

**2. Seeders**
- `database/seeders/RolePermissionSeeder.php` - Creates roles and assigns permissions
- `database/seeders/UserSeeder.php` - Seeds test users with roles

**3. Migrations**
- `2025_11_27_000001_create_roles_table.php` - Creates roles table
- `2025_11_27_000002_create_role_permissions_table.php` - Creates permission association
- `2025_11_27_000004_add_rbac_dual_nasabah_to_users_table.php` - Adds role_id to users

---

## 🔄 Setup & Initialization

### Fresh Database Setup
```bash
php artisan migrate:fresh --seed
```

This will:
1. ✅ Drop all existing tables
2. ✅ Run all migrations (including role tables)
3. ✅ Seed RolePermissionSeeder (creates 3 roles + 62 permissions)
4. ✅ Seed UserSeeder (creates 8 users with proper role_ids)
5. ✅ Initialize badge progress for all users
6. ✅ Seed all other data (products, articles, etc.)

### Verify Setup
```bash
php verify_roles.php
```

Output shows:
- ✅ Each user with their role
- ✅ Permission count per role
- ✅ Sample permissions
- ✅ Role summary

---

## 🔐 Security Notes

⚠️ **Important**
- Test passwords (`admin123`, `superadmin123`) should be changed in production
- Use proper password hashing (already using `Hash::make()`)
- Always validate permissions on backend, not just frontend
- Use middleware for route protection
- Audit all admin/superadmin actions in `audit_logs`

---

## 🧪 Testing Admin Functionality

### Test Admin Login
```php
// In routes/web.php or API
POST /api/login
{
    "email": "admin@test.com",
    "password": "admin123"
}

// Expected response:
{
    "user": {
        "id": 1,
        "nama": "Admin Testing",
        "email": "admin@test.com",
        "role_id": 2,
        "role": {
            "nama_role": "admin",
            "level_akses": 2
        }
    },
    "token": "..." // API Token
}
```

### Test Superadmin Login
```php
POST /api/login
{
    "email": "superadmin@test.com",
    "password": "superadmin123"
}

// Expected response:
{
    "user": {
        "id": 2,
        "nama": "Superadmin Testing",
        "email": "superadmin@test.com",
        "role_id": 3,
        "role": {
            "nama_role": "superadmin",
            "level_akses": 3
        }
    },
    "token": "..." // API Token
}
```

### Test Permission Checking
```php
// After login as admin
GET /api/admin/dashboard
// Should return 200 OK with admin data

GET /api/admin/manage-roles
// Should return 200 OK (admin has manage_roles via inheritance)

GET /api/superadmin/settings
// Should return 403 Forbidden (admin doesn't have superadmin-only perms)
```

---

## 📊 Database Status Summary

### Current State (After migrate:fresh --seed)

**Roles Created:**
- 1 × NASABAH (1 level, 17 permissions, 6 users)
- 1 × ADMIN (2 level, 40 permissions, 1 user)
- 1 × SUPERADMIN (3 level, 62 permissions, 1 user)

**Users Created:**
- 2 × Admin/Superadmin test accounts ✅
- 6 × Regular nasabah users ✅
- All assigned proper role_ids ✅
- All permissions inherited correctly ✅

**Permissions:**
- 62 total unique permissions ✅
- Properly inherited across roles ✅
- Role-permission associations created ✅

---

## 🎯 Next Steps

1. ✅ Test admin login with `admin@test.com / admin123`
2. ✅ Test superadmin login with `superadmin@test.com / superadmin123`
3. ✅ Verify admin can access admin dashboard
4. ✅ Verify superadmin can access all features
5. ⏳ Create admin dashboard UI
6. ⏳ Create superadmin management panel
7. ⏳ Add permission checks to all admin routes
8. ⏳ Document all admin features

---

## 📞 Support

For questions about:
- **Role System**: Check `app/Models/Role.php`
- **Permissions**: Check `database/seeders/RolePermissionSeeder.php`
- **User Roles**: Check `database/seeders/UserSeeder.php`
- **Role Methods**: Check `app/Models/User.php` (hasRole, hasPermission, etc.)

---

**Last Updated:** December 1, 2025  
**Status:** ✅ FULLY OPERATIONAL
