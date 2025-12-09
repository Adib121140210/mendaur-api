# 🔍 Backend Login Response Diagnostic

## Issue Analysis

Based on your console logs, here's what's happening:

### Problem 1: Wrong Role on Login
```
❌ Login with admin@test.com:
   role: 'user'           ← WRONG! Should be 'admin' or 'superadmin'
   permissions: 0         ← WRONG! Should be 30+ or 62+
   
✅ Should be:
   role: 'admin'          ← Correct role
   permissions: 30+       ← Correct permissions count
```

### Problem 2: API 500 Error
```
❌ GET /api/dashboard/stats/1
   Status: 500 (Internal Server Error)
   
This suggests backend user endpoint isn't configured for role-based requests
```

### Problem 3: Admin Redirects to User Dashboard
```
❌ Admin login flow:
   1. Logs in with admin@test.com
   2. Backend returns role='user' (WRONG)
   3. Frontend sees role='user'
   4. Redirects to /dashboard (user dashboard)
   5. Should redirect to /admin/dashboard instead
```

---

## Root Cause

The backend's **login endpoint** is not returning the correct role structure for admin/superadmin accounts.

### What's Being Returned (Current - WRONG):
```json
{
  "status": "success",
  "data": {
    "token": "8|jJSc6QTXTaq7x3t93F...",
    "user": {
      "id": 2,
      "email": "admin@test.com",
      "role": "user",              ← WRONG! String instead of object
      "permissions": 0             ← WRONG! Number instead of array
    }
  }
}
```

### What Should Be Returned (Correct):
```json
{
  "status": "success",
  "data": {
    "token": "8|jJSc6QTXTaq7x3t93F...",
    "user": {
      "id": 2,
      "email": "admin@test.com",
      "role": {                    ← Should be object
        "id": 2,
        "nama_role": "admin",
        "level_akses": 2,
        "permissions": [
          "approve_deposit",
          "approve_withdrawal",
          // ... 28+ more permissions
        ]
      }
    }
  }
}
```

---

## What Needs to Be Fixed on Backend

The backend's **login controller** needs to:

1. **Load user with role relationship** - Not just return role ID
2. **Load role permissions** - Include permissions array
3. **Return role as object** - Not as string
4. **Include all permissions in response** - For frontend to use

### Backend Fix Required

In `app/Http/Controllers/Auth/LoginController.php`:

```php
<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // ✅ LOAD WITH ROLE AND PERMISSIONS
        $user = User::with('role.permissions')  // ← IMPORTANT!
            ->where('email', $validated['email'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah'
            ], 401);
        }

        // ✅ GENERATE TOKEN
        $token = $user->createToken('auth_token')->plainTextToken;

        // ✅ RETURN WITH FULL ROLE + PERMISSIONS
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'nama' => $user->nama,
                    'email' => $user->email,
                    'no_hp' => $user->no_hp,
                    'total_poin' => $user->total_poin,
                    'level' => $user->level,
                    'role_id' => $user->role_id,
                    'role' => [                           // ← OBJECT, not string
                        'id' => $user->role->id,
                        'nama_role' => $user->role->nama_role,
                        'level_akses' => $user->role->level_akses,
                        'permissions' => $user->role->permissions
                            ->pluck('permission')         // ← Array of permission strings
                            ->toArray()
                    ],
                    'tipe_nasabah' => $user->tipe_nasabah,
                    'created_at' => $user->created_at,
                    'updated_at' => $user->updated_at,
                ]
            ]
        ]);
    }
}
```

---

## What's Wrong in Current Backend Response

Your backend is returning:

```json
{
  "user": {
    "role": "user",         ← String value (WRONG)
    "permissions": 0        ← Number, should be array (WRONG)
  }
}
```

It should return:

```json
{
  "user": {
    "role": {               ← Object (CORRECT)
      "id": 1,
      "nama_role": "nasabah",
      "level_akses": 1,
      "permissions": ["deposit_sampah", "redeem_poin", ...]
    }
  }
}
```

---

## Why This Matters

### Frontend Impact

When login returns `role: "user"` (string):
```javascript
// ❌ Frontend tries to extract:
const userRole = userData.role?.nama_role || userData.role || 'nasabah';
// Result: "user" (doesn't match 'admin' check)
// So admin redirects to /dashboard instead of /admin/dashboard
```

When login returns `role: { nama_role: "admin", ... }` (object):
```javascript
// ✅ Frontend correctly extracts:
const userRole = userData.role?.nama_role || userData.role || 'nasabah';
// Result: "admin" (matches admin check)
// So admin correctly redirects to /admin/dashboard
```

---

## Quick Diagnosis Steps

### Step 1: Check Backend Login Response
Open DevTools → Network tab → Click login request → Response tab

You'll see one of:

**❌ WRONG (Current):**
```json
{
  "user": {
    "id": 2,
    "email": "admin@test.com",
    "role": "user",
    "permissions": 0
  }
}
```

**✅ CORRECT (Should be):**
```json
{
  "user": {
    "id": 2,
    "email": "admin@test.com",
    "role": {
      "id": 2,
      "nama_role": "admin",
      "level_akses": 2,
      "permissions": ["approve_deposit", "approve_withdrawal", ...]
    }
  }
}
```

### Step 2: Check Database
```sql
-- Check if admin user has correct role_id
SELECT id, email, role_id FROM users WHERE email = 'admin@test.com';
-- Should show: id=2, role_id=2 (not 1)

-- Check if role has permissions
SELECT * FROM role_permissions WHERE role_id = 2;
-- Should return 30+ rows (admin permissions)
```

### Step 3: Check Backend Controller
Make sure LoginController:
- [ ] Uses `User::with('role.permissions')`
- [ ] Returns role as object (not string)
- [ ] Returns permissions as array (not number)
- [ ] Includes all fields in response

---

## Solution Summary

### What Needs to Happen

| Step | What | Where |
|------|------|-------|
| 1 | Update LoginController | Backend |
| 2 | Add `with('role.permissions')` | Backend |
| 3 | Return role as object | Backend |
| 4 | Return permissions array | Backend |
| 5 | Test login with admin account | Frontend |
| 6 | Verify correct redirect | Frontend |

### Expected After Fix

```
Login: admin@test.com / admin123

✅ Backend returns:
   role: { nama_role: "admin", permissions: [...30+] }

✅ Frontend receives:
   userRole = "admin"
   
✅ Frontend redirects to:
   /admin/dashboard (NOT /dashboard)

✅ Admin dashboard loads
```

---

## Next Steps

### For Backend (URGENT)

1. Open `app/Http/Controllers/Auth/LoginController.php`
2. Change query from:
   ```php
   $user = User::where('email', $validated['email'])->first();
   ```
   To:
   ```php
   $user = User::with('role.permissions')->where('email', $validated['email'])->first();
   ```
3. Change response to return role as object (not string)
4. Test login with admin@test.com
5. Check Network tab → Login response
6. Verify role and permissions in response

### For Frontend

No changes needed! Frontend code is correct.
It's just waiting for backend to return proper response.

---

## Summary

**Problem:** Backend login returns `role: "user"` for admin accounts
**Expected:** Backend should return `role: { nama_role: "admin", permissions: [...] }`
**Fix Location:** `app/Http/Controllers/Auth/LoginController.php`
**Fix Action:** Use `with('role.permissions')` and return role as object

Once backend is fixed, admin login will work correctly! ✅
