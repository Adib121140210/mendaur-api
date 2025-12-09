# 🎯 Admin RBAC Login Issue - ROOT CAUSE & FIX

## The Issue (Summary)

Admin login returns `role: 'user'` instead of `role: 'admin'` because the backend login endpoint is not returning the role object correctly.

---

## What's Happening

```
Admin logs in with: admin@test.com / admin123

Frontend Console Shows:
  ❌ role: 'user'
  ❌ permissions: 0
  ❌ isAdmin: false

Should Show:
  ✅ role: 'admin'
  ✅ permissions: 30+
  ✅ isAdmin: true

Result:
  ❌ Admin redirects to /dashboard (user dashboard)
  Should redirect to: /admin/dashboard (admin dashboard)
```

---

## Root Cause

The backend login endpoint is returning the user's role as a **string value** ("user", "admin") instead of a **role object** with `nama_role` and `permissions`.

### Backend Response (Current - WRONG)
```json
{
  "data": {
    "user": {
      "id": 2,
      "role": "admin",           ← String
      "permissions": 0           ← Number
    }
  }
}
```

### Backend Response (Expected - CORRECT)
```json
{
  "data": {
    "user": {
      "id": 2,
      "role": {                  ← Object
        "nama_role": "admin",
        "level_akses": 2,
        "permissions": ["approve_deposit", ...]
      }
    }
  }
}
```

### Frontend Code (Working Correctly)
```javascript
// This line in login.jsx extracts role correctly:
const userRole = userData.role?.nama_role || userData.role || 'nasabah';

// When backend returns string "admin":
userData.role = "admin"
userData.role?.nama_role = undefined (because string doesn't have this property)
userRole = "admin" ✅ Works

// BUT when backend returns role object:
userData.role = { nama_role: "admin", ... }
userData.role?.nama_role = "admin" 
userRole = "admin" ✅ Works better

// Problem: If admin user's role_id is 1 in database (nasabah):
// Backend returns role "user" or role_id 1
// Frontend gets userRole = "user"
// Admin redirects to /dashboard ❌
```

---

## The Real Problem

There are actually **TWO issues**:

### Issue 1: Backend Response Format
Backend is returning `role` as a string/number instead of an object.

### Issue 2: Admin User Has Wrong Role
Admin user (admin@test.com) is assigned to role_id = 1 (nasabah) instead of role_id = 2 (admin).

---

## Database Check (High Priority)

**Check if admin user has correct role assigned:**

```sql
SELECT u.id, u.email, u.role_id, r.nama_role 
FROM users u 
LEFT JOIN roles r ON u.role_id = r.id 
WHERE u.email = 'admin@test.com';
```

**Results:**
- If `role_id = 1` → ❌ Admin is set as "nasabah" (WRONG!)
- If `role_id = 2` → ✅ Admin is set as "admin" (CORRECT)
- If `role_id = 3` → ✅ Admin is set as "superadmin" (CORRECT)

**If admin has role_id = 1, this MUST be fixed:**
```sql
UPDATE users SET role_id = 2 WHERE email = 'admin@test.com';
```

---

## Backend Code Fix

### Location: AuthController.php (or LoginController.php)

**Find the login method:**

#### ❌ CURRENT CODE (WRONG)
```php
class AuthController extends Controller
{
    public function login(Request $request)
    {
        // ... validation ...
        
        // ❌ WRONG - Doesn't load role relationship
        $user = User::where('email', $validated['email'])->first();
        
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // ❌ WRONG - Returns user with role ID or string, not role object
        return response()->json([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'user' => $user  // ← Returns raw user model
            ]
        ]);
    }
}
```

#### ✅ FIXED CODE (CORRECT)
```php
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);
        
        // ✅ CORRECT - Load role and permissions
        $user = User::with('role.permissions')
            ->where('email', $validated['email'])
            ->first();
        
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Email atau password salah'
            ], 401);
        }
        
        $token = $user->createToken('auth_token')->plainTextToken;
        
        // ✅ CORRECT - Return full role object with permissions
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

## Key Changes Explained

### Change 1: Load Role with Query
```php
// BEFORE
$user = User::where('email', $validated['email'])->first();

// AFTER
$user = User::with('role.permissions')->where('email', $validated['email'])->first();
//       ↑ This loads user.role and user.role.permissions
```

**Why:** Without this, the role relationship is not loaded, so you can't access `$user->role->nama_role` or `$user->role->permissions`.

### Change 2: Format Response
```php
// BEFORE
'user' => $user

// AFTER
'user' => [
    'id' => $user->id,
    'email' => $user->email,
    // ... other fields ...
    'role' => [
        'id' => $user->role->id,
        'nama_role' => $user->role->nama_role,
        'level_akses' => $user->role->level_akses,
        'permissions' => $user->role->permissions->pluck('permission')->toArray()
    ]
]
```

**Why:** 
- Returns role as an object (not a string)
- Returns permissions as an array (not a count)
- Frontend expects this exact structure

### Change 3: Extract Permission Strings
```php
$user->role->permissions->pluck('permission')->toArray()
```

**Why:** Permissions table might have multiple columns (id, permission, description). We only want the `permission` column as a simple array of strings.

---

## Complete Fix Checklist

### ✅ Backend Fixes Needed

- [ ] **Step 1:** Find `app/Http/Controllers/Auth/AuthController.php` (or LoginController)
- [ ] **Step 2:** Change query to include `.with('role.permissions')`
- [ ] **Step 3:** Update response to return role as object
- [ ] **Step 4:** Update response to return permissions as array
- [ ] **Step 5:** Restart Laravel server
- [ ] **Step 6:** Test login in browser

### ✅ Database Fixes Needed

- [ ] **Step 1:** Check admin user's role_id
- [ ] **Step 2:** If role_id ≠ 2, update it: `UPDATE users SET role_id = 2 WHERE email = 'admin@test.com'`
- [ ] **Step 3:** Verify admin role has permissions assigned
- [ ] **Step 4:** If not, seed permissions

### ✅ Frontend (No Changes Needed)

Code is already correct! Just needs backend fix.

---

## Testing After Fix

### Test 1: Admin Login
```
1. Go to http://localhost:5174/login
2. Email: admin@test.com
3. Password: admin123
4. Click Login
5. Check console output

Expected:
✅ "🔐 Navigating to admin dashboard"
✅ Redirect to http://localhost:5174/admin/dashboard
✅ Admin dashboard loads
```

### Test 2: Check Network Response
```
1. Open DevTools (F12)
2. Network tab
3. Login
4. Find "login" request
5. Click → Response tab
6. Check if role is object with permissions

Expected:
{
  "user": {
    "role": {
      "nama_role": "admin",
      "permissions": ["approve_deposit", ...]
    }
  }
}
```

### Test 3: User Login
```
1. Go to http://localhost:5174/login
2. Email: user@test.com
3. Password: user123
4. Click Login
5. Check console output

Expected:
✅ "👤 Navigating to user dashboard"
✅ Redirect to http://localhost:5174/dashboard
✅ User dashboard loads
```

---

## Why Frontend Code is Correct

Frontend handles both response formats gracefully:

```javascript
// In login.jsx (line 48-49)
const userRole = userData.role?.nama_role || userData.role || 'nasabah';
//                             ↑ Try object property
//                                           ↑ Fall back to string
//                                                        ↑ Default value

// This works with:
// ✅ { role: { nama_role: 'admin' } }
// ✅ { role: 'admin' }
// ✅ { role: 2 } → falls back to 'nasabah'

// BUT the problem is:
// If backend returns { role: 'user' } for admin user
// Frontend gets userRole = 'user'
// Then admin redirects to /dashboard (user dashboard)
```

---

## Summary

| Component | Status | Issue |
|-----------|--------|-------|
| Frontend Code | ✅ Correct | None - works as designed |
| Login Logic | ✅ Correct | None - handles both formats |
| Backend Response Format | ❌ Wrong | Returns role as string instead of object |
| Admin User Role Assignment | ❌ Likely Wrong | May have role_id = 1 instead of 2 |
| Database Permissions | ❌ Unknown | Need to verify admin has permissions |

---

## Next Action

**For Backend Developer:**

1. Open `app/Http/Controllers/Auth/AuthController.php`
2. Add `.with('role.permissions')` to User query
3. Update response to return role as object
4. Check admin user has role_id = 2 in database
5. Test login

**That's it!** ✅

After this fix, admin will:
- Login successfully
- Redirect to /admin/dashboard (not /dashboard)
- Have permissions loaded
- Everything will work! 🎉
