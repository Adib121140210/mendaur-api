# 🚀 Quick Implementation Guide: Using Roles & Permissions

## 🎯 Quick Start

### 1️⃣ Check User Role in Controller

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Method 1: Check single role
    public function manageDashboard(Request $request)
    {
        if (!$request->user()->hasRole('admin')) {
            abort(403, 'Unauthorized: Admin access required');
        }
        
        return view('admin.dashboard');
    }

    // Method 2: Check multiple roles
    public function superadminPanel(Request $request)
    {
        if (!$request->user()->hasAnyRole('admin', 'superadmin')) {
            abort(403, 'Unauthorized: Admin or Superadmin required');
        }
        
        return view('admin.superadmin-panel');
    }

    // Method 3: Check specific permission
    public function manageRoles(Request $request)
    {
        if (!$request->user()->hasPermission('manage_roles')) {
            abort(403, 'Unauthorized: Role management permission required');
        }
        
        return view('admin.manage-roles');
    }
}
?>
```

---

### 2️⃣ Route Protection with Middleware

```php
<?php
// routes/web.php

Route::middleware('auth')->group(function () {
    // Regular user routes
    Route::get('/dashboard', [UserController::class, 'dashboard']);
    Route::post('/deposit-sampah', [DepositController::class, 'store']);
});

// Admin only routes
Route::middleware(['auth', 'role:admin,superadmin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::post('/admin/approve-withdrawal', [AdminController::class, 'approveWithdrawal']);
    Route::get('/admin/nasabah', [AdminController::class, 'managenasabah']);
});

// Superadmin only routes
Route::middleware(['auth', 'role:superadmin'])->group(function () {
    Route::post('/admin/manage-admin', [AdminController::class, 'manageAdmin']);
    Route::post('/admin/manage-roles', [AdminController::class, 'manageRoles']);
    Route::get('/admin/settings', [AdminController::class, 'settings']);
});
?>
```

---

### 3️⃣ Permission-Based Access

```php
<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    // Check permission from role inheritance
    public function viewReports(Request $request)
    {
        // Admin has 'view_reports' in inherited permissions
        if (!$request->user()->hasPermission('view_reports')) {
            abort(403, 'Permission denied');
        }
        
        return view('admin.reports');
    }

    // Check multiple permissions
    public function withdrawalManagement(Request $request)
    {
        $requiredPerms = [
            'view_withdrawal_requests',
            'approve_withdrawal',
            'reject_withdrawal'
        ];
        
        if (!$request->user()->hasAllPermissions(...$requiredPerms)) {
            abort(403, 'Missing required permissions');
        }
        
        return view('admin.withdrawal-management');
    }

    // Check any of multiple permissions
    public function userManagement(Request $request)
    {
        if (!$request->user()->hasAnyPermission('manage_nasabah', 'view_nasabah')) {
            abort(403, 'No user management access');
        }
        
        return view('admin.user-management');
    }
}
?>
```

---

### 4️⃣ API Routes Protection

```php
<?php
// routes/api.php

Route::middleware('auth:sanctum')->group(function () {
    // All authenticated users
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/deposit', [DepositController::class, 'store']);
});

// Admin API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/admin/approve-withdrawal', function (Request $request) {
        if (!$request->user()->hasRole('admin', 'superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Process withdrawal approval
    });
});

// Superadmin API routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/superadmin/audit-logs', function (Request $request) {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Get audit logs
    });
});
?>
```

---

### 5️⃣ Blade Template Protection

```blade
<!-- resources/views/admin/dashboard.blade.php -->

@auth
    {{-- Check role --}}
    @if(auth()->user()->hasRole('admin', 'superadmin'))
        <div class="admin-panel">
            <h1>Admin Dashboard</h1>
            
            {{-- Check permission --}}
            @if(auth()->user()->hasPermission('manage_nasabah'))
                <a href="/admin/nasabah">Manage Nasabah</a>
            @endif
            
            {{-- Check multiple permissions --}}
            @if(auth()->user()->hasAllPermissions('view_reports', 'view_analytics'))
                <a href="/admin/reports">Reports</a>
            @endif
            
            {{-- Superadmin only --}}
            @if(auth()->user()->hasRole('superadmin'))
                <a href="/admin/roles">Manage Roles</a>
                <a href="/admin/settings">System Settings</a>
            @endif
        </div>
    @else
        <p>You don't have admin access</p>
    @endif
@endauth
```

---

## 🔐 Middleware Implementation

### Create Admin Middleware

```php
<?php
// app/Http/Middleware/AdminMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is admin or superadmin
        if (!auth()->user()->hasAnyRole('admin', 'superadmin')) {
            abort(403, 'Admin access required');
        }

        return $next($request);
    }
}
?>
```

Register in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
    ]);
})
```

---

### Create Permission Middleware

```php
<?php
// app/Http/Middleware/PermissionMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, ...$permissions): Response
    {
        if (!auth()->user()->hasAllPermissions(...$permissions)) {
            abort(403, 'Permission denied');
        }

        return $next($request);
    }
}
?>
```

Use in routes:
```php
Route::middleware('permission:manage_nasabah,view_reports')->group(function () {
    Route::get('/admin/nasabah-reports', [AdminController::class, 'nasabahReports']);
});
```

---

## 📊 Model Usage

### Get User's Role

```php
<?php

$user = User::find(1);

// Get role object
$role = $user->role;  // Returns Role model
echo $role->nama_role;  // 'admin'

// Get role name
$roleName = $user->role->nama_role;  // 'admin'

// Get role level
$level = $user->role->level_akses;  // 2

?>
```

### Get User's Permissions

```php
<?php

$user = User::find(1);

// Get all inherited permissions
$permissions = $user->role->getInheritedPermissions();
// Returns: Collection of RolePermission objects

// Loop through permissions
foreach ($permissions as $perm) {
    echo $perm->permission_code;     // 'manage_nasabah'
    echo $perm->nama_permission;     // 'Manage Nasabah'
}

// Count permissions
$count = $permissions->count();  // 40

// Get specific permission
$hasPerm = $user->hasPermission('manage_nasabah');  // true/false

?>
```

---

### Assign Role to User (after creation)

```php
<?php

use App\Models\User;
use App\Models\Role;

// Find user
$user = User::find(1);

// Get role
$adminRole = Role::where('nama_role', 'admin')->first();

// Assign role
$user->update([
    'role_id' => $adminRole->id
]);

// Verify
echo $user->role->nama_role;  // 'admin'

?>
```

---

## 🧪 Testing Role Access

### Test Admin Access

```php
<?php

// In tests/Feature/AdminTest.php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminTest extends TestCase
{
    public function test_admin_can_access_dashboard()
    {
        // Create admin user
        $admin = User::factory()->create([
            'role_id' => 2  // admin role
        ]);

        // Login as admin
        $response = $this->actingAs($admin)
            ->get('/admin/dashboard');

        // Should be authorized
        $response->assertStatus(200);
    }

    public function test_nasabah_cannot_access_admin_dashboard()
    {
        // Create nasabah user
        $nasabah = User::factory()->create([
            'role_id' => 1  // nasabah role
        ]);

        // Try to access admin dashboard
        $response = $this->actingAs($nasabah)
            ->get('/admin/dashboard');

        // Should be forbidden
        $response->assertStatus(403);
    }
}

?>
```

### Test Permission Checking

```php
<?php

public function test_admin_has_manage_nasabah_permission()
{
    $admin = User::with('role')
        ->where('role_id', 2)
        ->first();

    // Should have permission
    $this->assertTrue(
        $admin->hasPermission('manage_nasabah')
    );
}

public function test_nasabah_does_not_have_manage_nasabah_permission()
{
    $nasabah = User::with('role')
        ->where('role_id', 1)
        ->first();

    // Should not have permission
    $this->assertFalse(
        $nasabah->hasPermission('manage_nasabah')
    );
}

?>
```

---

## 🔄 Complete Example: Withdrawal Approval

### Database Query
```php
<?php

// Get pending withdrawals that admin can approve
$withdrawals = Withdrawal::where('status', 'pending')
    ->with('user')
    ->get();

foreach ($withdrawals as $withdrawal) {
    echo "User: " . $withdrawal->user->nama;
    echo "Amount: " . $withdrawal->amount;
}

?>
```

### Controller
```php
<?php

namespace App\Http\Controllers;

class WithdrawalController extends Controller
{
    public function approve(Request $request, $withdrawalId)
    {
        // Check permission
        if (!$request->user()->hasPermission('approve_withdrawal')) {
            abort(403, 'Permission denied');
        }

        $withdrawal = Withdrawal::findOrFail($withdrawalId);
        $withdrawal->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now()
        ]);

        return response()->json(['message' => 'Withdrawal approved']);
    }
}

?>
```

### Route
```php
<?php

Route::middleware(['auth', 'role:admin,superadmin'])
    ->post('/admin/withdrawals/{id}/approve', 
           [WithdrawalController::class, 'approve']);

?>
```

### API Response
```json
{
    "message": "Withdrawal approved",
    "withdrawal": {
        "id": 123,
        "user_id": 5,
        "amount": 500000,
        "status": "approved",
        "approved_by": 1,
        "approved_at": "2025-12-01T15:38:52Z"
    }
}
```

---

## 📋 Common Patterns

### Pattern 1: Role-based View
```php
@if(auth()->user()->hasRole('admin'))
    {{-- Show admin view --}}
@elseif(auth()->user()->hasRole('superadmin'))
    {{-- Show superadmin view --}}
@else
    {{-- Show user view --}}
@endif
```

### Pattern 2: Permission-based Action
```php
@can('approve_withdrawal')
    <button class="btn-approve">Approve</button>
@else
    <span class="text-muted">Cannot approve</span>
@endcan
```

### Pattern 3: Cascading Checks
```php
// Must be authenticated
auth()->check()

// Must be admin or superadmin
auth()->user()->hasAnyRole('admin', 'superadmin')

// Must have specific permission
auth()->user()->hasPermission('manage_nasabah')
```

---

## ⚠️ Common Mistakes to Avoid

❌ **Don't:** Check role only on frontend
```php
// WRONG - User can bypass by modifying HTML/JS
@if(userRole === 'admin')
    // Allow action
@endif
```

✅ **Do:** Always check on backend
```php
// CORRECT - Server validates permission
if (!$request->user()->hasPermission('action')) {
    abort(403);
}
```

---

❌ **Don't:** Hardcode role names
```php
// WRONG - Not maintainable
if ($user->role->nama_role === 'admin') {
    // ...
}
```

✅ **Do:** Use built-in methods
```php
// CORRECT - Centralized, maintainable
if ($user->hasRole('admin')) {
    // ...
}
```

---

❌ **Don't:** Forget to include role relationship
```php
// WRONG - Will cause N+1 queries
$users = User::all();
foreach ($users as $user) {
    echo $user->role->nama_role;  // Query each time!
}
```

✅ **Do:** Eager load relationships
```php
// CORRECT - Single query with relationship
$users = User::with('role')->get();
foreach ($users as $user) {
    echo $user->role->nama_role;  // No additional queries
}
```

---

## 🎓 Summary

| Task | Code |
|------|------|
| Check role | `$user->hasRole('admin')` |
| Check any role | `$user->hasAnyRole('admin', 'superadmin')` |
| Check permission | `$user->hasPermission('manage_nasabah')` |
| Check all permissions | `$user->hasAllPermissions('perm1', 'perm2')` |
| Check any permission | `$user->hasAnyPermission('perm1', 'perm2')` |
| Get user role | `$user->role->nama_role` |
| Get user permissions | `$user->role->getInheritedPermissions()` |
| Protect route | `Route::middleware('role:admin')` |
| Protect view | `@if(auth()->user()->hasRole('admin'))` |

---

**Last Updated:** December 1, 2025  
**Documentation Status:** ✅ COMPLETE
