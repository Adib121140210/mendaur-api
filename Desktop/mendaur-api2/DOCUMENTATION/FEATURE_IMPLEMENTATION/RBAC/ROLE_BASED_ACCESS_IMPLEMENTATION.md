# 🔐 Role-Based Access Control (RBAC) Implementation Guide

## Overview
Sistem RBAC dengan 3 roles: NASABAH (user), ADMIN (staff), dan SUPERADMIN (manager), terintegrasi dengan dual-nasabah model (konvensional vs modern).

---

## 1. Database Schema Changes

### 1.1 Create ROLES Table

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    nama_role VARCHAR(50) UNIQUE NOT NULL,
    deskripsi TEXT NULL,
    level_akses INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed initial roles
INSERT INTO roles (nama_role, level_akses, deskripsi) VALUES
    ('nasabah', 1, 'Regular user - dapat deposit sampah, tukar poin, lihat badge'),
    ('admin', 2, 'Bank staff - dapat approve transaksi, manage users'),
    ('superadmin', 3, 'System manager - dapat manage admins dan system settings');
```

### 1.2 Create ROLE_PERMISSIONS Table

```sql
CREATE TABLE role_permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    role_id BIGINT UNSIGNED NOT NULL,
    permission VARCHAR(100) NOT NULL,
    deskripsi TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_permission (role_id, permission)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create indexes
CREATE INDEX idx_role_permissions_role_id ON role_permissions(role_id);
CREATE INDEX idx_role_permissions_permission ON role_permissions(permission);
```

### 1.3 Create AUDIT_LOGS Table

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    admin_id BIGINT UNSIGNED NOT NULL,
    action_type VARCHAR(100) NOT NULL,
    resource_type VARCHAR(100) NULL,
    resource_id BIGINT UNSIGNED NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    reason TEXT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    status ENUM('success', 'failed') DEFAULT 'success',
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_admin_id (admin_id),
    INDEX idx_created_at (created_at),
    INDEX idx_action_type (action_type),
    INDEX idx_resource_type_id (resource_type, resource_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 1.4 Modify USERS Table (Add role_id FK)

```sql
ALTER TABLE users ADD COLUMN (
    role_id BIGINT UNSIGNED DEFAULT 1 AFTER updated_at,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
);

-- Add index
CREATE INDEX idx_users_role_id ON users(role_id);
CREATE INDEX idx_users_role_id_created_at ON users(role_id, created_at);

-- Update existing users to role_id=1 (nasabah)
UPDATE users SET role_id = 1 WHERE role_id IS NULL;

-- Add NOT NULL constraint after migration
ALTER TABLE users MODIFY role_id BIGINT UNSIGNED NOT NULL;
```

### 1.5 Enhance LOG_AKTIVITAS Table (Add poin tracking for dual-model)

```sql
ALTER TABLE log_aktivitas ADD COLUMN (
    poin_tercatat INT DEFAULT 0 AFTER poin_perubahan,
    poin_usable INT DEFAULT 0 AFTER poin_tercatat,
    source_tipe VARCHAR(255) NULL AFTER poin_usable
);

-- For dual-nasabah model:
-- - poin_tercatat: poin yang dicatat di sistem (untuk badge/leaderboard)
-- - poin_usable: poin yang benar-benar bisa dipakai (hanya untuk konvensional)
-- - source_tipe: tracking source (setor_sampah, badge, etc)
```

### 1.6 Enhance USERS Table (Add dual-nasabah columns + banking info)

```sql
ALTER TABLE users ADD COLUMN (
    tipe_nasabah ENUM('konvensional', 'modern') DEFAULT 'konvensional' AFTER total_poin,
    poin_tercatat INT DEFAULT 0 AFTER tipe_nasabah,
    nama_bank VARCHAR(100) NULL AFTER poin_tercatat,
    nomor_rekening VARCHAR(50) NULL AFTER nama_bank,
    atas_nama_rekening VARCHAR(255) NULL AFTER nomor_rekening
);

-- Add indexes for dual-nasabah queries
CREATE INDEX idx_users_tipe_nasabah ON users(tipe_nasabah);
CREATE INDEX idx_users_total_poin_tipe ON users(total_poin, tipe_nasabah);
```

### 1.7 Enhance POIN_TRANSAKSIS Table (Add usability tracking)

```sql
ALTER TABLE poin_transaksis ADD COLUMN (
    is_usable BOOLEAN DEFAULT TRUE AFTER updated_at,
    reason_not_usable VARCHAR(255) NULL AFTER is_usable
);

-- For modern nasabah: is_usable = FALSE, reason_not_usable = 'nasabah_modern_restricted'
-- For konvensional: is_usable = TRUE

CREATE INDEX idx_poin_transaksis_is_usable ON poin_transaksis(user_id, is_usable);
```

---

## 2. Laravel Models

### 2.1 Role Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['nama_role', 'deskripsi', 'level_akses'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions()
    {
        return $this->hasMany(RolePermission::class, 'role_id');
    }

    public function hasPermission($permission)
    {
        return $this->permissions()
            ->where('permission', $permission)
            ->exists();
    }
}
```

### 2.2 RolePermission Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    protected $table = 'role_permissions';
    protected $fillable = ['role_id', 'permission', 'deskripsi'];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
```

### 2.3 AuditLog Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $table = 'audit_logs';
    protected $fillable = [
        'admin_id', 'action_type', 'resource_type', 'resource_id',
        'old_values', 'new_values', 'reason', 'ip_address',
        'user_agent', 'status', 'error_message'
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public static function logAction($adminId, $action, $resource, $resourceId, $old, $new, $reason = null)
    {
        return self::create([
            'admin_id' => $adminId,
            'action_type' => $action,
            'resource_type' => $resource,
            'resource_id' => $resourceId,
            'old_values' => $old,
            'new_values' => $new,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'status' => 'success'
        ]);
    }
}
```

### 2.4 Update User Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    // ... existing code ...

    protected $fillable = [
        'no_hp', 'nama', 'email', 'password', 'alamat', 'foto_profil',
        'total_poin', 'total_setor_sampah', 'level',
        // NEW: role and nasabah type
        'role_id', 'tipe_nasabah', 'poin_tercatat',
        // NEW: banking info for modern nasabah
        'nama_bank', 'nomor_rekening', 'atas_nama_rekening'
    ];

    // ===== ROLE RELATIONSHIPS =====
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function hasRole($roleName)
    {
        return $this->role->nama_role === $roleName;
    }

    public function hasAnyRole(...$roleNames)
    {
        return in_array($this->role->nama_role, $roleNames);
    }

    public function hasPermission($permission)
    {
        return $this->role->hasPermission($permission);
    }

    // ===== NASABAH TYPE HELPERS =====
    public function isNasabahKonvensional()
    {
        return $this->tipe_nasabah === 'konvensional';
    }

    public function isNasabahModern()
    {
        return $this->tipe_nasabah === 'modern';
    }

    public function isAdmin()
    {
        return $this->role_id >= 2; // admin or superadmin
    }

    public function isSuperAdmin()
    {
        return $this->role_id === 3;
    }

    // ===== POIN DISPLAY LOGIC =====
    public function getDisplayedPoin()
    {
        // Konvensional: show actual usable poin
        if ($this->isNasabahKonvensional()) {
            return $this->total_poin;
        }
        // Modern: always show 0 (poin only for badges/leaderboard)
        return 0;
    }

    public function getActualPoinBalance()
    {
        // Konvensional: total_poin == actual balance
        if ($this->isNasabahKonvensional()) {
            return $this->total_poin;
        }
        // Modern: 0 (cannot use poin for features)
        return 0;
    }

    // ===== FEATURE ACCESS CONTROL =====
    public function canUsePoinFeature($feature)
    {
        // Konvensional can use all poin features
        if ($this->isNasabahKonvensional()) {
            return true;
        }
        
        // Modern cannot use penarikan_tunai or penukaran_produk
        if ($this->isNasabahModern()) {
            return !in_array($feature, ['penarikan_tunai', 'penukaran_produk']);
        }
        
        return false;
    }

    // ... existing relationships ...
}
```

---

## 3. Middleware & Authorization

### 3.1 CheckPermission Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        if (!$user->hasPermission($permission)) {
            AuditLog::create([
                'admin_id' => null,
                'action_type' => 'unauthorized_access_attempt',
                'resource_type' => 'permission',
                'resource_id' => null,
                'reason' => "User {$user->id} attempted to access permission: {$permission}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed'
            ]);
            
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        return $next($request);
    }
}
```

### 3.2 CheckRole Middleware

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        if (!$user->hasAnyRole(...$roles)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        
        return $next($request);
    }
}
```

### 3.3 Register Middleware in HTTP Kernel

```php
<?php

// In app/Http/Kernel.php

protected $routeMiddleware = [
    // ... existing middleware ...
    'permission' => \App\Http\Middleware\CheckPermission::class,
    'role' => \App\Http\Middleware\CheckRole::class,
];
```

---

## 4. Routes Configuration

```php
<?php

use App\Http\Controllers\Api\{
    DepositController,
    PoinController,
    ProductController,
    WithdrawalController,
    BadgeController,
    AdminController,
    SuperAdminController
};

// ===== PUBLIC ROUTES (No auth) =====
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// ===== NASABAH ROUTES (Authenticated) =====
Route::middleware(['auth:sanctum'])->group(function () {
    // Deposits
    Route::post('/deposits', [DepositController::class, 'store'])
        ->middleware('permission:deposit_sampah');
    Route::get('/deposits', [DepositController::class, 'index'])
        ->middleware('permission:view_own_deposits');
    
    // Poin Management
    Route::get('/poin/balance', [PoinController::class, 'balance'])
        ->middleware('permission:view_own_poin');
    Route::get('/poin/history', [PoinController::class, 'history'])
        ->middleware('permission:view_poin_history');
    
    // Product Redemption
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:view_products');
    Route::post('/redemptions', [ProductController::class, 'redeem'])
        ->middleware('permission:redeem_product');
    
    // Withdrawals
    Route::post('/withdrawals', [WithdrawalController::class, 'request'])
        ->middleware('permission:request_withdrawal');
    Route::get('/withdrawals', [WithdrawalController::class, 'index'])
        ->middleware('permission:view_own_poin');
    
    // Badges & Leaderboard
    Route::get('/badges/progress', [BadgeController::class, 'progress'])
        ->middleware('permission:view_own_badges');
    Route::get('/leaderboard', [BadgeController::class, 'leaderboard'])
        ->middleware('permission:view_all_leaderboard');
});

// ===== ADMIN ROUTES (Role: admin or superadmin) =====
Route::middleware(['auth:sanctum', 'role:admin,superadmin'])->group(function () {
    // Deposit Approvals
    Route::post('/admin/deposits/{id}/approve', [AdminController::class, 'approveDeposit'])
        ->middleware('permission:approve_deposit');
    Route::post('/admin/deposits/{id}/reject', [AdminController::class, 'rejectDeposit'])
        ->middleware('permission:reject_deposit');
    Route::get('/admin/deposits', [AdminController::class, 'deposits'])
        ->middleware('permission:view_all_deposits');
    
    // Withdrawal Approvals
    Route::post('/admin/withdrawals/{id}/approve', [AdminController::class, 'approveWithdrawal'])
        ->middleware('permission:approve_withdrawal');
    Route::post('/admin/withdrawals/{id}/reject', [AdminController::class, 'rejectWithdrawal'])
        ->middleware('permission:reject_withdrawal');
    Route::get('/admin/withdrawals', [AdminController::class, 'withdrawals'])
        ->middleware('permission:view_all_withdrawals');
    
    // Poin Management
    Route::post('/admin/poin/adjust', [AdminController::class, 'adjustPoin'])
        ->middleware('permission:manual_poin_adjust');
    
    // Dashboard
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->middleware('permission:view_admin_dashboard');
});

// ===== SUPERADMIN ROUTES (Role: superadmin only) =====
Route::middleware(['auth:sanctum', 'role:superadmin'])->group(function () {
    // Admin Management
    Route::post('/superadmin/admins', [SuperAdminController::class, 'createAdmin'])
        ->middleware('permission:create_admin');
    Route::put('/superadmin/admins/{id}', [SuperAdminController::class, 'updateAdmin'])
        ->middleware('permission:edit_admin');
    Route::delete('/superadmin/admins/{id}', [SuperAdminController::class, 'deleteAdmin'])
        ->middleware('permission:delete_admin');
    Route::get('/superadmin/admins', [SuperAdminController::class, 'listAdmins'])
        ->middleware('permission:view_all_admins');
    
    // Audit Logs
    Route::get('/superadmin/audit-logs', [SuperAdminController::class, 'auditLogs'])
        ->middleware('permission:audit_admin_actions');
    
    // Role Management
    Route::post('/superadmin/roles', [SuperAdminController::class, 'createRole'])
        ->middleware('permission:manage_roles_permissions');
    Route::put('/superadmin/roles/{id}', [SuperAdminController::class, 'updateRole'])
        ->middleware('permission:manage_roles_permissions');
    
    // System Settings
    Route::get('/superadmin/settings', [SuperAdminController::class, 'settings'])
        ->middleware('permission:view_system_settings');
    Route::put('/superadmin/settings', [SuperAdminController::class, 'updateSettings'])
        ->middleware('permission:update_system_settings');
});
```

---

## 5. Permission Seeding

### 5.1 Create Seeder

```php
<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\RolePermission;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $nasabahPermissions = [
            'deposit_sampah',
            'view_own_deposits',
            'cancel_own_deposit',
            'redeem_poin',
            'request_withdrawal',
            'view_own_poin',
            'view_poin_history',
            'view_products',
            'redeem_product',
            'cancel_redemption',
            'view_own_badges',
            'view_own_leaderboard',
            'view_all_leaderboard',
            'view_own_profile',
            'update_own_profile',
            'view_own_notifications',
            'view_own_analytics',
            'export_own_data',
        ];

        $adminPermissions = array_merge($nasabahPermissions, [
            'approve_deposit',
            'reject_deposit',
            'view_all_deposits',
            'approve_withdrawal',
            'reject_withdrawal',
            'view_all_withdrawals',
            'manual_poin_adjust',
            'poin_adjust_reason_required',
            'approve_redemption',
            'reject_redemption',
            'view_all_redemptions',
            'view_all_users',
            'view_user_details',
            'view_all_badge_progress',
            'send_notification',
            'view_admin_dashboard',
            'export_user_reports',
        ]);

        $superAdminPermissions = array_merge($adminPermissions, [
            'create_admin',
            'edit_admin',
            'delete_admin',
            'assign_admin_role',
            'view_all_admins',
            'audit_admin_actions',
            'reset_user_badge',
            'deactivate_user',
            'reactivate_user',
            'send_bulk_notification',
            'view_financial_reports',
            'export_financial_data',
            'view_system_analytics',
            'view_system_settings',
            'update_system_settings',
            'manage_roles_permissions',
            'system_maintenance',
            'view_system_logs',
        ]);

        // Get roles
        $nasabahRole = Role::where('nama_role', 'nasabah')->first();
        $adminRole = Role::where('nama_role', 'admin')->first();
        $superAdminRole = Role::where('nama_role', 'superadmin')->first();

        // Seed permissions
        foreach ($nasabahPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role_id' => $nasabahRole->id,
                'permission' => $permission,
            ]);
        }

        foreach ($adminPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role_id' => $adminRole->id,
                'permission' => $permission,
            ]);
        }

        foreach ($superAdminPermissions as $permission) {
            RolePermission::firstOrCreate([
                'role_id' => $superAdminRole->id,
                'permission' => $permission,
            ]);
        }
    }
}
```

---

## 6. Implementation Example: Approve Deposit

```php
<?php

namespace App\Http\Controllers\Api;

use App\Models\TabungSampah;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function approveDeposit(Request $request, $depositId)
    {
        $admin = $request->user();
        $deposit = TabungSampah::findOrFail($depositId);

        // 1. Check permission (middleware already did this, but double-check)
        if (!$admin->hasPermission('approve_deposit')) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // 2. Get old values
        $oldValues = [
            'status' => $deposit->status,
            'poin_didapat' => $deposit->poin_didapat,
        ];

        // 3. Update deposit
        $deposit->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => $admin->id,
        ]);

        // 4. Update user poin (based on nasabah type)
        $user = $deposit->user;
        
        if ($user->isNasabahKonvensional()) {
            // Konvensional: poin bisa dipakai
            $user->total_poin += $deposit->poin_didapat;
            $user->poin_tercatat += $deposit->poin_didapat;
        } else {
            // Modern: poin hanya tercatat (tidak usable)
            $user->poin_tercatat += $deposit->poin_didapat;
        }
        $user->save();

        // 5. Record poin transaction
        PoinTransaksis::create([
            'user_id' => $user->id,
            'tabung_sampah_id' => $deposit->id,
            'poin_didapat' => $deposit->poin_didapat,
            'sumber' => 'setor_sampah',
            'is_usable' => $user->isNasabahKonvensional(),
            'reason_not_usable' => $user->isNasabahModern() 
                ? 'nasabah_modern_restricted' 
                : null,
        ]);

        // 6. Record audit log
        AuditLog::logAction(
            $admin->id,
            'approve_deposit',
            'tabung_sampah',
            $depositId,
            $oldValues,
            [
                'status' => 'approved',
                'poin_didapat' => $deposit->poin_didapat,
            ],
            $request->input('reason')
        );

        return response()->json([
            'message' => 'Deposit approved',
            'deposit' => $deposit,
            'user_poin' => [
                'total_poin' => $user->total_poin,
                'poin_tercatat' => $user->poin_tercatat,
                'displayed_poin' => $user->getDisplayedPoin(),
            ],
        ]);
    }
}
```

---

## 7. Feature Access Guard

```php
<?php

namespace App\Services;

use App\Models\User;

class FeatureAccessService
{
    public static function canAccessFeature(User $user, $feature)
    {
        $checklist = [
            'deposit_sampah' => ['nasabah', 'admin', 'superadmin'],
            'request_withdrawal' => [
                'check_nasabah_type' => ['konvensional'],
                'roles' => ['nasabah', 'admin', 'superadmin'],
            ],
            'redeem_product' => [
                'check_nasabah_type' => ['konvensional'],
                'roles' => ['nasabah', 'admin', 'superadmin'],
            ],
            'view_badges' => ['nasabah', 'admin', 'superadmin'],
            'view_leaderboard' => ['nasabah', 'admin', 'superadmin'],
            'view_admin_dashboard' => ['admin', 'superadmin'],
        ];

        if (!isset($checklist[$feature])) {
            return false; // Feature not defined
        }

        $rules = $checklist[$feature];

        // Check role
        if (isset($rules['roles']) && !$user->hasAnyRole(...$rules['roles'])) {
            return false;
        }

        // Check nasabah type
        if (isset($rules['check_nasabah_type'])) {
            if (!in_array($user->tipe_nasabah, $rules['check_nasabah_type'])) {
                return false;
            }
        }

        return true;
    }

    public static function canAccessUserData(User $requester, User $targetUser)
    {
        // Nasabah: hanya bisa akses data sendiri
        if ($requester->hasRole('nasabah')) {
            return $requester->id === $targetUser->id;
        }

        // Admin/Superadmin: bisa akses semua nasabah
        return true;
    }
}
```

---

## 8. Migration File Template

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRbacTables extends Migration
{
    public function up()
    {
        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('nama_role', 50)->unique();
            $table->text('deskripsi')->nullable();
            $table->integer('level_akses')->default(1);
            $table->timestamps();
        });

        // Create role_permissions table
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('role_id');
            $table->string('permission', 100);
            $table->text('deskripsi')->nullable();
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->unique(['role_id', 'permission'], 'unique_role_permission');
            
            $table->index('role_id');
            $table->index('permission');
        });

        // Create audit_logs table
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('admin_id');
            $table->string('action_type', 100);
            $table->string('resource_type', 100)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('admin_id');
            $table->index('created_at');
            $table->index('action_type');
            $table->index(['resource_type', 'resource_id']);
        });

        // Add role_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id')->default(1)->after('updated_at');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
            $table->index('role_id');
            $table->index(['role_id', 'created_at']);
        });

        // Seed initial roles
        DB::table('roles')->insert([
            ['nama_role' => 'nasabah', 'level_akses' => 1, 'deskripsi' => 'Regular user'],
            ['nama_role' => 'admin', 'level_akses' => 2, 'deskripsi' => 'Bank staff'],
            ['nama_role' => 'superadmin', 'level_akses' => 3, 'deskripsi' => 'System manager'],
        ]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['role_id', 'created_at']);
            $table->dropColumn('role_id');
        });

        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('roles');
    }
}
```

---

## 9. Testing

```php
<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Role;
use Tests\TestCase;

class RoleBasedAccessTest extends TestCase
{
    public function test_nasabah_can_deposit_waste()
    {
        $nasabah = User::factory()->create([
            'role_id' => 1,
            'tipe_nasabah' => 'konvensional',
        ]);

        $this->actingAs($nasabah)
            ->post('/api/deposits', ['berat_kg' => 5])
            ->assertOk();
    }

    public function test_nasabah_cannot_approve_deposits()
    {
        $nasabah = User::factory()->create(['role_id' => 1]);

        $this->actingAs($nasabah)
            ->post('/api/admin/deposits/1/approve')
            ->assertForbidden();
    }

    public function test_modern_nasabah_cannot_withdraw()
    {
        $modern = User::factory()->create([
            'role_id' => 1,
            'tipe_nasabah' => 'modern',
        ]);

        $this->actingAs($modern)
            ->post('/api/withdrawals', ['jumlah_poin' => 100])
            ->assertForbidden();
    }

    public function test_admin_can_approve_deposit()
    {
        $admin = User::factory()->create(['role_id' => 2]);
        $deposit = TabungSampah::factory()->create();

        $this->actingAs($admin)
            ->post("/api/admin/deposits/{$deposit->id}/approve")
            ->assertOk();
    }

    public function test_superadmin_can_create_admin()
    {
        $superadmin = User::factory()->create(['role_id' => 3]);

        $this->actingAs($superadmin)
            ->post('/api/superadmin/admins', [
                'nama' => 'New Admin',
                'email' => 'admin@test.com',
                'no_hp' => '081234567890',
            ])
            ->assertOk();
    }
}
```

---

## Summary

✅ **Role-Based Access Control (3 tiers):**
- **Nasabah (Level 1)**: User regular - deposit, redeem poin, lihat badges
- **Admin (Level 2)**: Bank staff - approve transaksi, manage users  
- **Superadmin (Level 3)**: System manager - manage admins, settings

✅ **Dual-Nasabah Model Integration:**
- **Konvensional**: Poin tercatat + usable
- **Modern**: Poin tercatat saja (untuk badges/leaderboard, tidak usable)

✅ **Audit Trail:**
- AUDIT_LOGS table mencatat semua aksi admin
- IP address, user agent, before/after values

✅ **Permission Matrix:**
- Fine-grained permissions (40+ permissions)
- Seeded di database
- Checked di middleware + model methods
