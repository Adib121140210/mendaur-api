# ⚠️ BACKEND FIXES REQUIRED - Critical Issues

**Date:** December 21, 2025  
**Status:** 3 Critical Issues Found  
**Priority:** HIGH - Must fix before production deployment  

---

## 🔴 Issues Found During Frontend Testing

### 1. **Waste Deposits Endpoint - 403 Forbidden Error**

**Error:**
```
GET http://127.0.0.1:8000/api/admin/penyetoran-sampah?page=1&limit=10 
Response: 403 Forbidden
```

**What's happening:**
- Frontend is authenticated (valid token, superadmin role)
- Frontend is sending correct Authorization header
- Backend is rejecting the request with 403

**Root Cause:**
The `/api/admin/penyetoran-sampah` endpoint is missing or has incorrect authorization checks.

**Solution Required:**
In your Laravel backend, check:

```php
// File: routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/penyetoran-sampah', [WasteDepositsController::class, 'index']);
    // Make sure this route exists and is protected by auth:sanctum
});
```

And in the controller:

```php
// File: app/Http/Controllers/Admin/WasteDepositsController.php
public function index(Request $request)
{
    // Make sure user is authenticated
    if (!$request->user()) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    // Allow superadmin and admin to access
    if (!in_array($request->user()->role->nama_role, ['admin', 'superadmin'])) {
        return response()->json(['message' => 'Forbidden'], 403);
    }
    
    // Rest of your code...
}
```

---

### 2. **Waste Statistics Endpoint - 403 Forbidden Error**

**Error:**
```
GET http://127.0.0.1:8000/api/admin/penyetoran-sampah/stats/overview
Response: 403 Forbidden
```

**What's happening:**
- Same as above - endpoint is protected but superadmin is being denied

**Solution Required:**
Check the stats endpoint authorization:

```php
// File: routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/admin/penyetoran-sampah/stats/overview', [WasteDepositsController::class, 'statsOverview']);
});
```

**Note:** Make sure role checks allow superadmin.

---

### 3. **Missing API Functions**

Several API functions are missing from the backend:

**Errors Found:**
```
TypeError: adminApi.getProductRedemptions is not a function
TypeError: adminApi.getCashWithdrawals is not a function
TypeError: adminApi.getAllSchedules is not a function
GET http://127.0.0.1:8000/api/admin/badges?page=1&limit=20 → 404 Not Found
```

**What's happening:**
- Frontend is trying to call API methods that don't exist in `adminApi.js`
- OR the backend endpoints don't exist

**Solution Required:**

#### A. Missing Schedule Endpoint
```
GET /api/admin/jadwal-penyetoran (getAllSchedules)
POST /api/admin/jadwal-penyetoran (createSchedule)
PUT /api/admin/jadwal-penyetoran/{id} (updateSchedule)
DELETE /api/admin/jadwal-penyetoran/{id} (deleteSchedule)
```

#### B. Missing Product Redemption Endpoint
```
GET /api/admin/product-redemptions (getProductRedemptions)
POST /api/admin/product-redemptions/{id}/approve (approveRedemption)
POST /api/admin/product-redemptions/{id}/reject (rejectRedemption)
```

#### C. Missing Cash Withdrawal Endpoint
```
GET /api/admin/cash-withdrawals (getCashWithdrawals)
POST /api/admin/cash-withdrawals/{id}/approve (approveWithdrawal)
POST /api/admin/cash-withdrawals/{id}/reject (rejectWithdrawal)
```

#### D. Badge Management Endpoints - 404 Not Found

**Error:**
```
GET http://127.0.0.1:8000/api/admin/badges?page=1&limit=20
Response: 404 Not Found
```

**Solution Required:**
You need to create the badge management endpoints. Add to your routes:

```php
// File: routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Badge Management (Admin only)
    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/badges', [BadgeController::class, 'index']);
        Route::post('/admin/badges', [BadgeController::class, 'store']);
        Route::get('/admin/badges/{id}', [BadgeController::class, 'show']);
        Route::put('/admin/badges/{id}', [BadgeController::class, 'update']);
        Route::delete('/admin/badges/{id}', [BadgeController::class, 'destroy']);
        Route::post('/admin/users/{userId}/badges', [BadgeController::class, 'assignToUser']);
    });
});
```

And create the controller:

```php
// File: app/Http/Controllers/Admin/BadgeController.php
namespace App\Http\Controllers\Admin;

use App\Models\Badge;
use Illuminate\Http\Request;

class BadgeController extends Controller
{
    public function index(Request $request)
    {
        $badges = Badge::paginate($request->query('limit', 20));
        return response()->json([
            'status' => 'success',
            'data' => $badges
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string',
            'deskripsi' => 'nullable|string',
            'icon' => 'nullable|string',
            'kategori' => 'nullable|string'
        ]);

        $badge = Badge::create($validated);
        return response()->json([
            'status' => 'success',
            'data' => $badge
        ], 201);
    }

    // Implement show(), update(), destroy(), assignToUser()...
}
```

---

## 📋 Required Fixes Checklist

### Priority 1 (Do TODAY)
- [ ] Fix authorization on `/api/admin/penyetoran-sampah` endpoint
- [ ] Fix authorization on `/api/admin/penyetoran-sampah/stats/overview` endpoint
- [ ] Verify superadmin can access these endpoints

### Priority 2 (Do THIS WEEK)
- [ ] Create Badge management endpoints (6 endpoints)
- [ ] Create Product Redemption endpoints if missing
- [ ] Add proper role checks to all admin endpoints

### Priority 3 (Do NEXT WEEK)
- [ ] Review all endpoints for authorization consistency
- [ ] Add audit logging for admin actions
- [ ] Create comprehensive API documentation

---

## 🧪 How to Test After Fixes

Once you fix the backend, test with these commands:

```bash
# Test as superadmin (should work)
curl -H "Authorization: Bearer YOUR_SUPERADMIN_TOKEN" \
  http://127.0.0.1:8000/api/admin/penyetoran-sampah

# Test as admin (should work)
curl -H "Authorization: Bearer YOUR_ADMIN_TOKEN" \
  http://127.0.0.1:8000/api/admin/penyetoran-sampah

# Test as nasabah (should fail with 403)
curl -H "Authorization: Bearer YOUR_NASABAH_TOKEN" \
  http://127.0.0.1:8000/api/admin/penyetoran-sampah
```

Or in the frontend browser console:

```javascript
// After login as superadmin
const token = localStorage.getItem('token');
const response = await fetch('http://127.0.0.1:8000/api/admin/penyetoran-sampah', {
  headers: {
    'Authorization': `Bearer ${token}`
  }
});
console.log(response.status); // Should be 200, not 403
```

---

## 🎯 Frontend is Ready

✅ **Good News:** Your frontend is properly implemented:
- Correct authorization headers being sent
- Token is valid (logged in as superadmin)
- Permission checks are working correctly

❌ **Backend Issues:** The backend needs to:
- Fix authorization checks on existing endpoints
- Implement missing badge endpoints
- Ensure superadmin has access to all admin endpoints

---

## 📞 Questions?

If you need help implementing these backend fixes, refer to:
- Your Laravel authorization middleware
- The role-based permission system you created
- The ROLE_PERMISSIONS_AUDIT_REPORT.md for complete permission architecture

