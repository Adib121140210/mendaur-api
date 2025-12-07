# 🧪 Backend Login Fix - Testing Guide

## Quick Test Without Changing Code

Before making backend changes, verify the current response format.

### Test 1: Check Current Login Response

**Open Browser DevTools:**
1. Press F12 → Network tab
2. Go to http://localhost:5174/login
3. Enter credentials: admin@test.com / admin123
4. Click Login
5. Find the login request in Network tab
6. Click on it → Response tab
7. Look at what's returned

**You'll see one of these:**

#### ❌ WRONG (Current - Fix Needed)
```json
{
  "status": "success",
  "data": {
    "token": "...",
    "user": {
      "id": 2,
      "email": "admin@test.com",
      "role": "user",           ← STRING (wrong!)
      "permissions": 0          ← NUMBER (wrong!)
    }
  }
}
```

#### ✅ CORRECT (After Fix)
```json
{
  "status": "success",
  "data": {
    "token": "...",
    "user": {
      "id": 2,
      "email": "admin@test.com",
      "role": {                 ← OBJECT (correct!)
        "id": 2,
        "nama_role": "admin",
        "level_akses": 2,
        "permissions": [        ← ARRAY (correct!)
          "approve_deposit",
          "approve_withdrawal",
          ...
        ]
      }
    }
  }
}
```

---

### Test 2: Direct API Test (with curl or Postman)

**Option A: Using PowerShell**
```powershell
# Test admin login
$response = Invoke-WebRequest -Uri "http://127.0.0.1:8000/api/login" `
  -Method POST `
  -Headers @{"Content-Type"="application/json"} `
  -Body (@{email="admin@test.com"; password="admin123"} | ConvertTo-Json)

$response.Content | ConvertFrom-Json | ConvertTo-Json -Depth 10
```

**Option B: Using Postman**
1. Open Postman
2. Create POST request to `http://127.0.0.1:8000/api/login`
3. Headers: `Content-Type: application/json`
4. Body (raw JSON):
```json
{
  "email": "admin@test.com",
  "password": "admin123"
}
```
5. Send
6. Check Response body

---

## Backend Fix Verification

### Step 1: Apply Backend Fix

**File:** `app/Http/Controllers/Auth/AuthController.php` (or your auth controller)

**Line to change:**
```php
// BEFORE
$user = User::where('email', $validated['email'])->first();

// AFTER
$user = User::with('role.permissions')->where('email', $validated['email'])->first();
```

**Response to update:**
```php
// Make sure response returns:
'role' => [
    'id' => $user->role->id,
    'nama_role' => $user->role->nama_role,
    'level_akses' => $user->role->level_akses,
    'permissions' => $user->role->permissions->pluck('permission')->toArray()
]
```

### Step 2: Verify Database

```sql
-- Check admin user
SELECT id, email, role_id FROM users WHERE email = 'admin@test.com';
-- Result should show: role_id = 2 (not 1)

-- Check admin role
SELECT id, nama_role FROM roles WHERE id = 2;
-- Result should show: nama_role = 'admin'

-- Check admin permissions count
SELECT COUNT(*) as permission_count 
FROM role_permissions 
WHERE role_id = 2;
-- Result should show: 30+
```

---

## Test After Backend Fix

### Test 1: Admin Login
```
Input:
  Email: admin@test.com
  Password: admin123

Check:
✅ Login successful message
✅ Browser Network tab shows role.nama_role = 'admin'
✅ Browser Console shows: 🔐 Navigating to admin dashboard
✅ Redirected to /admin/dashboard (not /dashboard)
✅ Admin dashboard loads
```

### Test 2: User Login
```
Input:
  Email: user@test.com
  Password: user123

Check:
✅ Login successful message
✅ Browser Network tab shows role.nama_role = 'nasabah'
✅ Browser Console shows: 👤 Navigating to user dashboard
✅ Redirected to /dashboard (not /admin/dashboard)
✅ User dashboard loads
```

### Test 3: Superadmin Login (if available)
```
Input:
  Email: superadmin@test.com
  Password: superadmin123

Check:
✅ Login successful message
✅ Browser Network tab shows role.nama_role = 'superadmin'
✅ Browser Console shows: 🔐 Navigating to admin dashboard
✅ Redirected to /admin/dashboard
✅ Superadmin dashboard loads
```

---

## Verification Checklist

### Frontend Console Should Show (After Fix):

**Admin Login:**
```
✅ Login successful! {userId: 2, email: 'admin@test.com', role: 'admin', permissions: 30+, isAdmin: true}
✅ AuthContext.jsx:66 ✅ Login successful: {userId: 2, role: 'admin', permissions: 30+}
🔐 login.jsx:65 🔐 Navigating to admin dashboard
```

**User Login:**
```
✅ Login successful! {userId: 3, email: 'user@test.com', role: 'nasabah', permissions: 17+, isAdmin: false}
✅ AuthContext.jsx:66 ✅ Login successful: {userId: 3, role: 'nasabah', permissions: 17+}
👤 login.jsx:65 👤 Navigating to user dashboard
```

### Network Tab Should Show:

**Login Response (POST /api/login):**
```json
{
  "status": "success",
  "data": {
    "token": "...",
    "user": {
      "id": 2,
      "email": "admin@test.com",
      "role": {
        "id": 2,
        "nama_role": "admin",
        "level_akses": 2,
        "permissions": [
          "approve_deposit",
          "approve_withdrawal",
          "view_all_users",
          // ... more
        ]
      }
    }
  }
}
```

---

## Troubleshooting

### Issue: Still Showing role: "user" After Backend Fix

**Cause:** Backend not reloaded or changes not applied

**Fix:**
1. Make sure file is saved
2. Restart Laravel: `php artisan serve` (or your server)
3. Clear browser cache: Ctrl+Shift+Delete
4. Try login again

---

### Issue: API 500 Error Still Happening

**Cause:** Middleware issue or role validation failing

**Fix:**
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Look for actual error message
3. Common issues:
   - Role relationship not existing
   - Permissions table structure wrong
   - Middleware trying to validate something

---

### Issue: Permissions Array is Empty

**Cause:** Admin role has no permissions assigned

**Fix:**
```sql
-- Check current permissions
SELECT COUNT(*) FROM role_permissions WHERE role_id = 2;

-- If count is 0, need to seed permissions
-- Or use migrations to assign permissions

-- Quick fix - add all permissions to admin (if permissions exist)
INSERT INTO role_permissions (role_id, permission_id)
SELECT 2, id FROM permissions;
```

---

### Issue: Redirect Still Going to /dashboard for Admin

**Cause:** Frontend not receiving 'admin' role name correctly

**Fix:**
1. Check Network tab → Login response
2. Verify `role.nama_role` = 'admin' (exact match)
3. Check browser console for actual role received
4. Make sure backend returns lowercase role names

---

## Success Criteria

After backend fix is applied, all of these should be true:

- [ ] Admin login redirects to /admin/dashboard
- [ ] User login redirects to /dashboard
- [ ] Console shows correct role (admin/nasabah/superadmin)
- [ ] Network tab shows role as object, not string
- [ ] Network tab shows permissions as array, not number
- [ ] Admin can perform admin actions
- [ ] User cannot access admin features
- [ ] No 500 errors on API calls
- [ ] No React warnings in console

---

## Next Steps

1. **Check current response** → Use Network tab test
2. **Apply backend fix** → Update AuthController
3. **Test admin login** → Should redirect to /admin/dashboard
4. **Verify role in console** → Should show admin role
5. **Check Network response** → Should show role object
6. **Test user login** → Should redirect to /dashboard
7. **Verify permissions work** → Admin can do admin actions

---

## Questions to Answer

Before proceeding, answer these:

1. **Current response format:** What does Network tab show?
   - [ ] role as string?
   - [ ] role as object?

2. **Admin user setup:** What's the admin user's role_id in database?
   - [ ] Is it 1 (nasabah)?
   - [ ] Is it 2 (admin)?
   - [ ] Is it 3 (superadmin)?

3. **Admin permissions:** How many permissions does admin role have?
   - [ ] 0?
   - [ ] 30+?

4. **Backend config:** Which file has the login code?
   - [ ] `app/Http/Controllers/Auth/AuthController.php`?
   - [ ] `app/Http/Controllers/Auth/LoginController.php`?
   - [ ] Other?

Once these are answered, the fix can proceed! ✅
