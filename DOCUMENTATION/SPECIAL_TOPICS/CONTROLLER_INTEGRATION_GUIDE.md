# CONTROLLER INTEGRATION GUIDE - RBAC & DUAL-NASABAH

**Purpose**: Step-by-step guide to integrate RBAC and dual-nasabah system into existing controllers

---

## 📋 INTEGRATION ROADMAP

### Step 1: Identify Target Controllers
Controllers yang perlu diupdate:

**Nasabah-facing Controllers:**
- PenarikanTunaiController - Add feature access check for modern nasabah
- PenukaranProdukController - Add feature access check for modern nasabah
- TabungSampahController - Add dual-poin tracking

**Admin-facing Controllers:**
- AdminDepositApprovalController - Add audit logging
- AdminWithdrawalApprovalController - Add audit logging  
- AdminRedemptionApprovalController - Add audit logging
- AdminUserManagementController - Add audit logging
- AdminPoinAdjustmentController - Add audit logging

---

## 🔧 CONTROLLER UPDATE PATTERNS

### Pattern 1: Feature Access Check (Nasabah Controllers)

**Problem**: Modern nasabah should not be able to access penarikan_tunai or penukaran_produk

**Solution**: Add access check before processing

```php
// In PenarikanTunaiController@store

use App\Services\DualNasabahFeatureAccessService;

public function store(Request $request, DualNasabahFeatureAccessService $service)
{
    $user = auth()->user();
    
    // ✅ Step 1: Check feature access
    $access = $service->canAccessWithdrawal($user);
    if (!$access['allowed']) {
        return response()->json([
            'success' => false,
            'message' => $access['reason'],
            'code' => $access['code'],
        ], 403);
    }
    
    // ✅ Step 2: Validate request
    $validated = $request->validate([
        'jumlah_poin' => 'required|integer|min:1|max:' . $user->total_poin,
        'nama_bank' => 'required|string',
        'nomor_rekening' => 'required|string',
    ]);
    
    // ✅ Step 3: Create withdrawal request
    $penarikan = PenarikanTunai::create([
        'user_id' => $user->id,
        'jumlah_poin' => $validated['jumlah_poin'],
        'status' => 'pending',
        'tanggal_permohonan' => now(),
    ]);
    
    // ✅ Step 4: Deduct poin (hanya untuk konvensional)
    $service->deductPoinForWithdrawal(
        user: $user,
        poin: $validated['jumlah_poin'],
        penarikanTunaiId: $penarikan->id,
        reason: 'Penarikan tunai approved'
    );
    
    // ✅ Step 5: Log activity
    $service->logActivity(
        user: $user,
        tipeAktivitas: 'penarikan_tunai',
        deskripsi: "Pengajuan penarikan tunai sebesar {$validated['jumlah_poin']} poin",
        poinChange: -$validated['jumlah_poin'],
        sourceTipe: 'penarikan_tunai'
    );
    
    return response()->json([
        'success' => true,
        'message' => 'Pengajuan penarikan tunai berhasil',
        'data' => $penarikan,
    ]);
}
```

**Key Points**:
- ✅ Check feature access FIRST
- ✅ Use service methods for poin handling
- ✅ Log activity with dual-poin tracking
- ✅ Return detailed error messages

---

### Pattern 2: Poin Tracking on Deposit

**Problem**: Need to track poin separately for konvensional vs modern nasabah

**Solution**: Use service method when adding poin

```php
// In TabungSampahController@store (or wherever deposits are created)

use App\Services\DualNasabahFeatureAccessService;

public function store(Request $request, DualNasabahFeatureAccessService $service)
{
    $user = auth()->user();
    
    // ✅ Step 1: Check deposit access
    $access = $service->canAccessDeposit($user);
    if (!$access['allowed']) {
        return response()->json([
            'success' => false,
            'message' => $access['reason'],
        ], 403);
    }
    
    // ✅ Step 2: Validate & create deposit
    $validated = $request->validate([
        'jenis_sampah' => 'required|string',
        'berat_kg' => 'required|numeric|min:0.1',
    ]);
    
    $tabung = TabungSampah::create([
        'user_id' => $user->id,
        'jenis_sampah' => $validated['jenis_sampah'],
        'berat_kg' => $validated['berat_kg'],
        'status' => 'pending',
        'tanggal' => now(),
    ]);
    
    // ✅ Step 3: Calculate poin
    $poinPerKg = 100; // atau ambil dari config
    $totalPoin = (int)($validated['berat_kg'] * $poinPerKg);
    
    // ✅ Step 4: Add poin dengan dual-tracking
    $service->addPoinForDeposit(
        user: $user,
        poin: $totalPoin,
        tabungSampahId: $tabung->id,
        jenisSampah: $validated['jenis_sampah'],
        sumber: 'tabung_sampah'
    );
    
    // ✅ Step 5: Log activity
    $service->logActivity(
        user: $user,
        tipeAktivitas: 'deposit_sampah',
        deskripsi: "Penyetoran {$validated['berat_kg']} kg sampah {$validated['jenis_sampah']}",
        poinChange: $totalPoin,
        sourceTipe: 'tabung_sampah'
    );
    
    // ✅ Step 6: Return response with poin info
    return response()->json([
        'success' => true,
        'message' => 'Deposit sampah berhasil',
        'data' => [
            'tabung' => $tabung,
            'poin_info' => $service->getPoinDisplay($user->refresh()),
        ],
    ]);
}
```

**Key Points**:
- ✅ Use `addPoinForDeposit()` service method
- ✅ Both konvensional AND modern get poin_tercatat
- ✅ Only konvensional gets total_poin
- ✅ Return poin display info so frontend knows what to show

---

### Pattern 3: Admin Approval with Audit Logging

**Problem**: Need to track all admin actions with before/after values

**Solution**: Use AuditLog::logAction() for every admin action

```php
// In AdminDepositApprovalController@approve

use App\Models\AuditLog;
use App\Models\TabungSampah;

public function approve(Request $request, $tabungId)
{
    $admin = auth()->user();
    
    // ✅ Step 1: Check permission
    if (!$admin->hasPermission('approve_deposit')) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk action ini',
        ], 403);
    }
    
    // ✅ Step 2: Get resource
    $tabung = TabungSampah::findOrFail($tabungId);
    $oldStatus = $tabung->status;
    
    // ✅ Step 3: Capture old values (BEFORE update)
    $oldValues = [
        'status' => $tabung->status,
        'updated_at' => $tabung->updated_at?->toIso8601String(),
    ];
    
    // ✅ Step 4: Update resource
    $tabung->update([
        'status' => 'approved',
        'approved_at' => now(),
        'approved_by' => $admin->id,
    ]);
    
    // ✅ Step 5: Capture new values (AFTER update)
    $newValues = [
        'status' => $tabung->status,
        'approved_at' => $tabung->approved_at->toIso8601String(),
        'approved_by' => $tabung->approved_by,
    ];
    
    // ✅ Step 6: Log to audit trail
    AuditLog::logAction(
        admin: $admin,
        actionType: 'approve',
        resourceType: 'TabungSampah',
        resourceId: $tabung->id,
        oldValues: $oldValues,
        newValues: $newValues,
        reason: $request->input('reason', 'Persetujuan deposit sampah'),
        success: true
    );
    
    // ✅ Step 7: Return response
    return response()->json([
        'success' => true,
        'message' => 'Deposit approved successfully',
        'data' => $tabung,
    ]);
}

// ✅ Error handling example
public function reject(Request $request, $tabungId)
{
    $admin = auth()->user();
    $tabung = TabungSampah::findOrFail($tabungId);
    
    try {
        // Do something that might fail...
        
        // Log success
        AuditLog::logAction(
            admin: $admin,
            actionType: 'reject',
            resourceType: 'TabungSampah',
            resourceId: $tabung->id,
            oldValues: ['status' => $tabung->status],
            newValues: ['status' => 'rejected'],
            reason: $request->input('reason'),
            success: true
        );
        
    } catch (\Exception $e) {
        // Log failure
        AuditLog::logAction(
            admin: $admin,
            actionType: 'reject',
            resourceType: 'TabungSampah',
            resourceId: $tabung->id,
            oldValues: [],
            newValues: [],
            reason: $request->input('reason'),
            success: false,
            errorMessage: $e->getMessage()
        );
        
        return response()->json([
            'success' => false,
            'message' => 'Gagal menolak deposit',
        ], 500);
    }
}
```

**Key Points**:
- ✅ Check permission middleware atau in controller
- ✅ Capture old_values BEFORE update
- ✅ Capture new_values AFTER update
- ✅ Use AuditLog::logAction() with all details
- ✅ Include reason for the action
- ✅ Log both success and failure

---

### Pattern 4: Manual Poin Adjustment with Audit

**Problem**: Admin needs to manually adjust poin, must track reason and before/after

**Solution**: Audit log manual adjustment

```php
// In AdminPoinAdjustmentController@adjust

use App\Models\AuditLog;
use App\Models\User;
use App\Models\PoinTransaksi;

public function adjust(Request $request, $userId)
{
    $admin = auth()->user();
    
    // ✅ Step 1: Check permission
    if (!$admin->hasPermission('adjust_poin_manual')) {
        return response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin',
        ], 403);
    }
    
    // ✅ Step 2: Validate
    $validated = $request->validate([
        'poin_adjustment' => 'required|integer',
        'reason' => 'required|string|max:500',
        'type' => 'required|in:tercatat,usable,both',
    ]);
    
    // ✅ Step 3: Get user
    $user = User::findOrFail($userId);
    
    // ✅ Step 4: Capture old values
    $oldValues = [
        'total_poin' => $user->total_poin,
        'poin_tercatat' => $user->poin_tercatat,
    ];
    
    // ✅ Step 5: Apply adjustment
    switch ($validated['type']) {
        case 'tercatat':
            $user->increment('poin_tercatat', $validated['poin_adjustment']);
            break;
        case 'usable':
            $user->increment('total_poin', $validated['poin_adjustment']);
            break;
        case 'both':
            $user->increment('poin_tercatat', $validated['poin_adjustment']);
            $user->increment('total_poin', $validated['poin_adjustment']);
            break;
    }
    $user->save();
    
    // ✅ Step 6: Capture new values
    $newValues = [
        'total_poin' => $user->total_poin,
        'poin_tercatat' => $user->poin_tercatat,
    ];
    
    // ✅ Step 7: Log to poin_transaksis
    PoinTransaksi::create([
        'user_id' => $userId,
        'poin_didapat' => $validated['poin_adjustment'],
        'sumber' => 'manual_adjustment',
        'keterangan' => $validated['reason'],
        'referensi_tipe' => 'ManualAdjustment',
        'referensi_id' => $admin->id,
    ]);
    
    // ✅ Step 8: Log to audit trail
    AuditLog::logAction(
        admin: $admin,
        actionType: 'adjust',
        resourceType: 'UserPoin',
        resourceId: $userId,
        oldValues: $oldValues,
        newValues: $newValues,
        reason: $validated['reason'],
        success: true
    );
    
    return response()->json([
        'success' => true,
        'message' => 'Poin adjusted successfully',
        'data' => [
            'user_id' => $userId,
            'adjustment' => $validated['poin_adjustment'],
            'new_balance' => $newValues,
        ],
    ]);
}
```

---

## 🛣️ ROUTE UPDATES

### Add Middleware to Routes

```php
// routes/api.php

// ===== NASABAH ROUTES =====
Route::middleware('auth:sanctum')->group(function () {
    // Deposit
    Route::post('/deposits', [TabungSampahController::class, 'store'])
        ->middleware('permission:deposit_sampah');
    
    // Withdrawal
    Route::post('/withdrawals', [PenarikanTunaiController::class, 'store'])
        ->middleware('permission:request_withdrawal');
    
    // Redemption
    Route::post('/redemptions', [PenukaranProdukController::class, 'store'])
        ->middleware('permission:redeem_poin');
    
    // View profile
    Route::get('/profile', [ProfileController::class, 'show'])
        ->middleware('permission:view_profile');
});

// ===== ADMIN ROUTES =====
Route::middleware(['auth:sanctum', 'role:admin,superadmin'])->prefix('admin')->group(function () {
    // Approve deposits
    Route::post('/deposits/{id}/approve', [AdminDepositApprovalController::class, 'approve'])
        ->middleware('permission:approve_deposit');
    
    // Approve withdrawals
    Route::post('/withdrawals/{id}/approve', [AdminWithdrawalApprovalController::class, 'approve'])
        ->middleware('permission:approve_withdrawal');
    
    // View all users
    Route::get('/users', [AdminUserController::class, 'index'])
        ->middleware('permission:view_all_users');
    
    // Adjust poin
    Route::post('/users/{userId}/adjust-poin', [AdminPoinController::class, 'adjust'])
        ->middleware('permission:adjust_poin_manual');
});

// ===== SUPERADMIN ROUTES =====
Route::middleware(['auth:sanctum', 'role:superadmin'])->prefix('superadmin')->group(function () {
    // Manage admins
    Route::post('/admins', [SuperAdminAdminController::class, 'store'])
        ->middleware('permission:create_admin');
    
    Route::put('/admins/{id}', [SuperAdminAdminController::class, 'update'])
        ->middleware('permission:edit_admin');
    
    Route::delete('/admins/{id}', [SuperAdminAdminController::class, 'destroy'])
        ->middleware('permission:delete_admin');
    
    // View audit logs
    Route::get('/audit-logs', [SuperAdminAuditController::class, 'index'])
        ->middleware('permission:view_audit_logs');
    
    // Manage roles
    Route::post('/roles', [SuperAdminRoleController::class, 'store'])
        ->middleware('permission:create_role');
});
```

---

## 👤 USER SEEDER UPDATE

Update UserSeeder to assign roles:

```php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Get roles
        $nasabahRole = Role::where('nama_role', 'nasabah')->first();
        $adminRole = Role::where('nama_role', 'admin')->first();
        $superadminRole = Role::where('nama_role', 'superadmin')->first();
        
        // Create test nasabah - konvensional
        User::create([
            'nama' => 'Nasabah Konvensional',
            'email' => 'nasabah.konv@test.com',
            'password' => Hash::make('password'),
            'no_hp' => '081234567890',
            'alamat' => 'Jl. Test No. 1',
            'role_id' => $nasabahRole->id,
            'tipe_nasabah' => 'konvensional',
            'total_poin' => 1000,
            'poin_tercatat' => 1000,
            'nama_bank' => 'BRI',
            'nomor_rekening' => '1234567890',
            'atas_nama_rekening' => 'Nasabah Konv',
        ]);
        
        // Create test nasabah - modern
        User::create([
            'nama' => 'Nasabah Modern',
            'email' => 'nasabah.modern@test.com',
            'password' => Hash::make('password'),
            'no_hp' => '081234567891',
            'alamat' => 'Jl. Test No. 2',
            'role_id' => $nasabahRole->id,
            'tipe_nasabah' => 'modern',
            'total_poin' => 0, // Modern always 0
            'poin_tercatat' => 1000, // But tracked
            'nama_bank' => 'BCA',
            'nomor_rekening' => '0987654321',
            'atas_nama_rekening' => 'Nasabah Modern',
        ]);
        
        // Create test admin
        User::create([
            'nama' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'no_hp' => '081234567892',
            'alamat' => 'Jl. Admin No. 1',
            'role_id' => $adminRole->id,
            'tipe_nasabah' => 'konvensional',
            'total_poin' => 5000,
            'poin_tercatat' => 5000,
        ]);
        
        // Create test superadmin
        User::create([
            'nama' => 'Superadmin Test',
            'email' => 'superadmin@test.com',
            'password' => Hash::make('password'),
            'no_hp' => '081234567893',
            'alamat' => 'Jl. Super No. 1',
            'role_id' => $superadminRole->id,
            'tipe_nasabah' => 'konvensional',
            'total_poin' => 10000,
            'poin_tercatat' => 10000,
        ]);
    }
}
```

Then run:
```bash
php artisan db:seed --class=UserSeeder
```

---

## ✅ INTEGRATION CHECKLIST

- [ ] Update PenarikanTunaiController - Add `canAccessWithdrawal()` check
- [ ] Update PenukaranProdukController - Add `canAccessRedemption()` check
- [ ] Update TabungSampahController - Add `addPoinForDeposit()` call
- [ ] Update AdminDepositApprovalController - Add `AuditLog::logAction()`
- [ ] Update AdminWithdrawalApprovalController - Add audit logging
- [ ] Update AdminRedemptionApprovalController - Add audit logging
- [ ] Update AdminUserManagementController - Add audit logging for user actions
- [ ] Update AdminPoinAdjustmentController - Add audit logging
- [ ] Add middleware to routes in `routes/api.php`
- [ ] Update UserSeeder to assign roles and nasabah types
- [ ] Test all flows with konvensional nasabah
- [ ] Test all flows with modern nasabah (should block withdraw/redeem)
- [ ] Test admin approval flows with audit logging
- [ ] Verify audit logs in database
- [ ] Test permission checks in middleware

---

## 🧪 MANUAL TESTING COMMANDS

```bash
# Test as konvensional nasabah
curl -X POST http://localhost:8000/api/deposits \
  -H "Authorization: Bearer TOKEN_KONV" \
  -H "Content-Type: application/json" \
  -d '{"jenis_sampah":"plastik", "berat_kg":5}'

# Should work: 200 OK
# Response: poin_info shows both total_poin and poin_tercatat increased

# Test as modern nasabah
curl -X POST http://localhost:8000/api/withdrawals \
  -H "Authorization: Bearer TOKEN_MODERN" \
  -H "Content-Type: application/json" \
  -d '{"jumlah_poin":100, "nama_bank":"BRI", "nomor_rekening":"123"}'

# Should fail: 403 Forbidden
# Response: "Modern nasabah tidak dapat menggunakan fitur penarikan tunai"

# Test as admin approval
curl -X POST http://localhost:8000/api/admin/deposits/1/approve \
  -H "Authorization: Bearer TOKEN_ADMIN" \
  -H "Content-Type: application/json" \
  -d '{"reason":"Verifikasi berhasil"}'

# Should work: 200 OK
# Check audit_logs table: new entry created with all details
```

---

**Next Step**: Follow this guide to update your controllers one by one!
