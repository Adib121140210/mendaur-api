# 🧪 Test Accounts & Credentials

## 📋 Quick Reference

| Account Type | Email | Password | Role | Level | Status |
|---|---|---|---|---|---|
| **Admin** | admin@test.com | admin123 | admin | Admin | ✅ Active |
| **Superadmin** | superadmin@test.com | superadmin123 | superadmin | Superadmin | ✅ Active |
| **Nasabah 1** | adib@example.com | password | nasabah | Bronze | ✅ Active |
| **Nasabah 2** | siti@example.com | password | nasabah | Silver | ✅ Active |
| **Nasabah 3** | budi@example.com | password | nasabah | Pemula | ✅ Active |
| **Nasabah 4** | reno@example.com | password | nasabah | Gold | ✅ Active |
| **Nasabah 5** | rina@example.com | password | nasabah | Platinum | ✅ Active |
| **Test Account** | test@test.com | test | nasabah | Bronze | ✅ Active |

---

## 👨‍💼 ADMIN TESTING ACCOUNT

```
Name:        Admin Testing
Email:       admin@test.com
Password:    admin123
Role:        admin
Role ID:     2
Level:       Admin
Type:        konvensional
Permissions: 40 (includes all nasabah + 23 admin-specific)
```

### Admin Capabilities
- ✅ Manage nasabah accounts
- ✅ View nasabah activity
- ✅ Approve point withdrawals
- ✅ Manage deposits (penyetoran)
- ✅ View system reports
- ✅ Access admin dashboard
- ✅ Perform all nasabah operations
- ❌ Cannot manage admin accounts
- ❌ Cannot manage roles & permissions
- ❌ Cannot access superadmin features

### Testing Workflow
1. Login with `admin@test.com` / `admin123`
2. Access admin dashboard
3. Test nasabah management features
4. Test deposit approval
5. Test withdrawal approval
6. Verify cannot access superadmin area

---

## 👑 SUPERADMIN TESTING ACCOUNT

```
Name:        Superadmin Testing
Email:       superadmin@test.com
Password:    superadmin123
Role:        superadmin
Role ID:     3
Level:       Superadmin
Type:        konvensional
Permissions: 62 (all admin + nasabah + 22 superadmin-specific)
```

### Superadmin Capabilities
- ✅ All admin capabilities
- ✅ Manage admin accounts
- ✅ Manage roles & permissions
- ✅ System settings access
- ✅ View audit logs
- ✅ Manage badge system
- ✅ Full system control
- ✅ Access all features

### Testing Workflow
1. Login with `superadmin@test.com` / `superadmin123`
2. Verify access to all admin features
3. Test role management
4. Test permission assignment
5. View audit logs
6. Access system settings
7. Verify can manage badges

---

## 🧑‍🤝‍🧑 NASABAH (REGULAR USER) ACCOUNTS

### Account 1: Adib Surya (Konvensional)
```
Email:           adib@example.com
Password:        password
Role:            nasabah
Role ID:         1
Level:           Bronze
Type:            konvensional
Poin Saat Ini:   150
Total Deposits:  5
Status:          ✅ Active
```

### Account 2: Siti Aminah (Konvensional)
```
Email:           siti@example.com
Password:        password
Role:            nasabah
Role ID:         1
Level:           Silver
Type:            konvensional
Poin Saat Ini:   2000
Total Deposits:  12
Status:          ✅ Active
```

### Account 3: Budi Santoso (Konvensional)
```
Email:           budi@example.com
Password:        password
Role:            nasabah
Role ID:         1
Level:           Pemula
Type:            konvensional
Poin Saat Ini:   50
Total Deposits:  2
Status:          ✅ Active
```

### Account 4: Reno Wijaya (Modern)
```
Email:              reno@example.com
Password:           password
Role:               nasabah
Role ID:            1
Level:              Gold
Type:               modern
Poin Tercatat:      500
Total Deposits:     8
Bank:               BNI
Account Number:     1234567890
Account Name:       Reno Wijaya
Status:             ✅ Active
```

### Account 5: Rina Kusuma (Modern)
```
Email:              rina@example.com
Password:           password
Role:               nasabah
Role ID:            1
Level:              Platinum
Type:               modern
Poin Tercatat:      1200
Total Deposits:     15
Bank:               MANDIRI
Account Number:     9876543210
Account Name:       Rina Kusuma
Status:             ✅ Active
```

### Account 6: Test Account
```
Email:           test@test.com
Password:        test
Role:            nasabah
Role ID:         1
Level:           Bronze
Type:            konvensional
Poin Saat Ini:   1000
Total Deposits:  2000
Status:          ✅ Active
```

### Nasabah Capabilities
- ✅ Deposit waste (sampah)
- ✅ View deposit history
- ✅ Check point balance
- ✅ Redeem points for products
- ✅ Edit own profile
- ✅ View own activity
- ✅ Withdraw funds (for modern accounts)
- ✅ View badges earned
- ❌ Cannot access admin features
- ❌ Cannot manage other users

---

## 🔑 Login Endpoints

### Web Login
```
POST /login
Content-Type: application/x-www-form-urlencoded

email=admin@test.com&password=admin123
```

### API Login
```
POST /api/login
Content-Type: application/json

{
    "email": "admin@test.com",
    "password": "admin123"
}
```

### Expected Response (Success)
```json
{
    "success": true,
    "message": "Login successful",
    "user": {
        "id": 1,
        "nama": "Admin Testing",
        "email": "admin@test.com",
        "level": "Admin",
        "role_id": 2,
        "role": {
            "id": 2,
            "nama_role": "admin",
            "level_akses": 2
        }
    },
    "token": "1|laravel_sanctum_token_here"
}
```

---

## 🧪 Testing Scenarios

### Scenario 1: Admin User Testing
**Goal:** Verify admin can manage nasabah accounts

**Steps:**
1. Login as admin@test.com / admin123
2. Navigate to admin dashboard
3. Go to "Manage Nasabah"
4. Verify can view list of nasabah
5. Verify can view nasabah details
6. Try to edit nasabah profile
7. Try to approve/reject withdrawal
8. Verify cannot access "Manage Admin" section

**Expected:** All nasabah features work, admin features blocked ✅

---

### Scenario 2: Superadmin User Testing
**Goal:** Verify superadmin can access all features

**Steps:**
1. Login as superadmin@test.com / superadmin123
2. Access admin dashboard
3. Verify can access "Manage Nasabah"
4. Verify can access "Manage Admin"
5. Verify can access "Manage Roles"
6. Verify can access "System Settings"
7. Verify can view audit logs
8. Verify can manage badges

**Expected:** All features accessible ✅

---

### Scenario 3: Nasabah User Testing
**Goal:** Verify regular user can only access their own features

**Steps:**
1. Login as adib@example.com / password
2. Verify dashboard shows only personal info
3. Try to access admin panel
4. Try to access other user's profile
5. Verify can only deposit sampah
6. Verify can only see own activity
7. Verify cannot see other nasabah data

**Expected:** Only personal features accessible ❌ Admin features ✅

---

### Scenario 4: Konvensional vs Modern Account
**Goal:** Verify different treatment for account types

**Steps:**
1. Login as adib@example.com (konvensional)
   - Should not have bank fields
   - Can use poin directly

2. Login as reno@example.com (modern)
   - Should have bank information
   - Cannot use poin directly
   - Must use withdrawal system

**Expected:** Different behavior based on type ✅

---

## ⚙️ Environment Setup

### Database Reset
```bash
php artisan migrate:fresh --seed
```

This will:
- Drop all tables
- Run migrations (including RBAC)
- Seed all test accounts
- Initialize badge progress

### Verify Setup
```bash
php verify_roles.php
```

Output:
```
╔════════════════════════════════════════════════════════════════╗
║            ROLE & PERMISSION VERIFICATION REPORT             ║
╚════════════════════════════════════════════════════════════════╝

👤 Admin Testing
📧 Email: admin@test.com
🔐 Role: admin
✅ Permissions Count: 40

👤 Superadmin Testing
📧 Email: superadmin@test.com
🔐 Role: superadmin
✅ Permissions Count: 62

✨ ROLE SUMMARY:
   • nasabah: 6 user(s), 17 permission(s)
   • admin: 1 user(s), 40 permission(s)
   • superadmin: 1 user(s), 62 permission(s)
```

---

## ⚠️ Security Notes

### In Production
- ⚠️ Change all test passwords to strong passwords
- ⚠️ Do NOT use these test accounts in production
- ⚠️ Create separate admin accounts for each team member
- ⚠️ Enable 2FA for admin/superadmin accounts
- ⚠️ Regularly audit access logs

### Best Practices
- ✅ Always hash passwords (using `Hash::make()`)
- ✅ Validate permissions on backend
- ✅ Use middleware for route protection
- ✅ Log all admin actions in audit_logs
- ✅ Rotate passwords regularly
- ✅ Monitor failed login attempts

---

## 📝 Password Policy

**Current (Testing):**
- Admin: `admin123` (8 chars)
- Superadmin: `superadmin123` (12 chars)
- Nasabah: `password` (8 chars)

**Recommended (Production):**
```
Minimum: 12 characters
Must include:
- Uppercase letters (A-Z)
- Lowercase letters (a-z)
- Numbers (0-9)
- Special characters (!@#$%^&*)

Example: Adm1n@Test2024#Secure
```

---

## 🔄 How to Change Passwords

### Via Database
```sql
UPDATE users 
SET password = '$2y$12$hash_here'
WHERE email = 'admin@test.com';
```

### Via Artisan Command
```php
php artisan tinker
>>> $user = User::where('email', 'admin@test.com')->first();
>>> $user->password = Hash::make('new_password_here');
>>> $user->save();
>>> exit();
```

### Via Controller
```php
$user = User::find($userId);
$user->password = Hash::make('new_password');
$user->save();
```

---

## 📊 Account Statistics

**Total Accounts:** 8
- Admin: 1 (admin@test.com)
- Superadmin: 1 (superadmin@test.com)
- Nasabah: 6 (mix of konvensional & modern)

**Total Points:** 5,650
- Adib: 150
- Siti: 2,000
- Budi: 50
- Reno: 0 (modern)
- Rina: 0 (modern)
- Test: 1,200
- Admin: 0
- Superadmin: 0

**Account Types:**
- Konvensional: 6
- Modern: 2

---

## 📞 Support

If you need to:
- **Reset a password:** Use artisan tinker command above
- **Change account type:** Modify `tipe_nasabah` in database
- **Add new test account:** Edit `database/seeders/UserSeeder.php` and re-run migration
- **Check permissions:** Run `php verify_roles.php`

---

**Last Updated:** December 1, 2025  
**Status:** ✅ FULLY CONFIGURED AND TESTED
