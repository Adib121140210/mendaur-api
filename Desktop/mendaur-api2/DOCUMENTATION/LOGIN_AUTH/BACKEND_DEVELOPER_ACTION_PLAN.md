# 🔧 BACKEND DEVELOPER ACTION PLAN - Admin RBAC Fix

**Priority:** 🔴 CRITICAL - Admin login broken
**Estimated Time:** 15-30 minutes
**Complexity:** Low (2-3 line change)

---

## Problem Statement

Admin account logs in but shows `role: "user"` instead of `role: "admin"`. This causes admin to be redirected to `/dashboard` (user dashboard) instead of `/admin/dashboard` (admin dashboard).

---

## Root Cause

Your login endpoint returns:
```json
{ "user": { "role": "user", "permissions": 0 } }
```

But frontend expects:
```json
{ "user": { "role": { "nama_role": "admin", "permissions": [...] } } }
```

---

## Step-by-Step Fix

### STEP 1: Locate Your Login Controller

Find the file that handles login. Usually one of:
- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Auth/LoginController.php`
- `app/Http/Controllers/AuthController.php`

Search for: `public function login(`

---

### STEP 2: Find This Code

Look for something like:
```php
public function login(Request $request)
{
    // ... validation code ...
    
    $user = User::where('email', $validated['email'])->first();
    
    // ... password check ...
    
    $token = $user->createToken('auth_token')->plainTextToken;
    
    return response()->json([
        'status' => 'success',
        'data' => [
            'token' => $token,
            'user' => $user
        ]
    ]);
}
```

---

### STEP 3: Make Changes

#### Change 3A: Update the Query

**FIND THIS:**
```php
$user = User::where('email', $validated['email'])->first();
```

**REPLACE WITH THIS:**
```php
$user = User::with('role.permissions')->where('email', $validated['email'])->first();
```

This loads the role and its permissions from the database.

---

#### Change 3B: Update the Response

**FIND THIS:**
```php
return response()->json([
    'status' => 'success',
    'data' => [
        'token' => $token,
        'user' => $user
    ]
]);
```

**REPLACE WITH THIS:**
```php
return response()->json([
    'status' => 'success',
    'message' => 'Login berhasil',
    'data' => [
        'token' => $token,
        'user' => [
            'id' => $user->id,
            'nama' => $user->nama ?? null,
            'email' => $user->email,
            'no_hp' => $user->no_hp ?? null,
            'total_poin' => $user->total_poin ?? 0,
            'level' => $user->level ?? 1,
            'role_id' => $user->role_id,
            'tipe_nasabah' => $user->tipe_nasabah ?? null,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
            'role' => [
                'id' => $user->role->id,
                'nama_role' => $user->role->nama_role,
                'level_akses' => $user->role->level_akses,
                'permissions' => $user->role->permissions
                    ->pluck('permission')
                    ->toArray()
            ]
        ]
    ]
]);
```

---

### STEP 4: Complete Fixed Controller

Here's what your complete login method should look like:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // ✅ KEY CHANGE: Load role and permissions
        $user = User::with('role.permissions')
            ->where('email', $validated['email'])
            ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah'
            ], 401);
        }

        // Generate token
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ KEY CHANGE: Return role as object with permissions array
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama ?? null,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp ?? null,
                    'total_poin' => $user->total_poin ?? 0,
                    'level' => $user->level ?? 1,
                    'role_id' => $user->role_id,
                    'tipe_nasabah' => $user->tipe_nasabah ?? null,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                    'role' => [
                        'id' => $user->role->id,
                        'nama_role' => $user->role->nama_role,
                        'level_akses' => $user->role->level_akses,
                        'permissions' => $user->role->permissions
                            ->pluck('permission')
                            ->toArray()
                    ]
                ]
            ]
        ]);
    }
}
```

---

## Step 5: Check Database Before Testing

Before testing, make sure admin account is properly set up:

```sql
-- Check admin account
SELECT u.id, u.email, u.role_id, r.nama_role 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
WHERE u.email = 'admin@test.com';
```

**Expected result:**
```
id: 2 (or any non-1 value)
email: admin@test.com
role_id: 2
nama_role: admin
```

**If role_id = 1:**
```sql
-- Fix it:
UPDATE users SET role_id = 2 WHERE email = 'admin@test.com';
```

---

## Step 6: Restart Backend

After making changes:

```bash
# If using `php artisan serve`:
# 1. Stop server (Ctrl+C)
# 2. Run: php artisan serve
# 3. Server restarts

# If using Valet:
# Usually auto-reloads

# If using Docker:
# Restart container: docker-compose restart
```

---

## Step 7: Test

1. Open browser: http://localhost:5174/login
2. Login with: admin@test.com / admin123
3. Open DevTools (F12)
4. Go to Network tab
5. Find "login" request
6. Check Response tab
7. Verify you see:
```json
{
  "data": {
    "user": {
      "role": {
        "nama_role": "admin",
        "permissions": [...]
      }
    }
  }
}
```

---

## Step 8: Verify Frontend Works

After backend returns correct format:

1. Check browser console - should show:
```
✅ Login successful! {userId: 2, email: 'admin@test.com', role: 'admin', permissions: 30+, isAdmin: true}
🔐 login.jsx:65 🔐 Navigating to admin dashboard
```

2. Check redirect - should go to:
```
http://localhost:5174/admin/dashboard
```

3. Admin dashboard should load with admin features visible

---

## Troubleshooting

### Issue: Still returning role as string
**Solution:** 
- Make sure you saved the file
- Restart backend server
- Clear browser cache (Ctrl+Shift+Delete)
- Try login again

### Issue: Error about role relationship
**Solution:**
- Check if User model has `role()` method defined
- Check if role table exists: `php artisan migrate:status`
- Verify role_id column exists on users table

### Issue: Error about permissions relationship
**Solution:**
- Check if Role model has `permissions()` method defined
- Check if permissions table exists
- Check if role_permissions table exists (junction table)

### Issue: permissions array is empty
**Solution:**
- Admin role might not have permissions assigned
- Check: `SELECT * FROM role_permissions WHERE role_id = 2;`
- If empty, need to seed permissions for admin role

---

## Additional Fixes Needed (After This)

After this fix, you also need to:

### Fix 1: Investigate 500 Error on /api/dashboard/stats/{id}
```
Endpoint: GET /api/dashboard/stats/1
Status: 500 Internal Server Error

Debug steps:
1. Check Laravel logs: tail -f storage/logs/laravel.log
2. Look for error in dashboard stats endpoint
3. Fix the error
4. Restart backend
```

### Fix 2: Verify Other Endpoints Work
Test these endpoints:
- POST /api/login → Should return correct format
- GET /api/dashboard/stats/1 → Should return 200 OK
- Any other endpoints used by frontend

---

## Quick Verification

**Before Fix:**
```
Admin login shows: role: "user"
Redirects to: /dashboard (wrong)
```

**After Fix:**
```
Admin login shows: role: "admin"
Redirects to: /admin/dashboard (correct)
```

---

## Files for Reference

If you need more details:
- `ADMIN_RBAC_LOGIN_ISSUE_ROOT_CAUSE.md` - Complete explanation
- `LOGIN_RESPONSE_DIAGNOSTIC.md` - Issue breakdown
- `BACKEND_LOGIN_FIX_COMPLETE.md` - Detailed guide
- `BACKEND_LOGIN_FIX_TESTING.md` - Testing procedures

---

## Summary

**What to change:**
1. Query: `User::where(...)` → `User::with('role.permissions')->where(...)`
2. Response: Return role as object with permissions array

**Database:**
- Verify admin user has role_id = 2 (not 1)
- Verify admin role has permissions assigned

**Time estimate:** 5-10 minutes

**Result:** Admin will redirect to /admin/dashboard and get admin features ✅

---

**Status:** Ready to implement
**Next:** Apply changes, test, verify admin dashboard works
**Questions?** Check the reference files above

Let's go! 🚀
