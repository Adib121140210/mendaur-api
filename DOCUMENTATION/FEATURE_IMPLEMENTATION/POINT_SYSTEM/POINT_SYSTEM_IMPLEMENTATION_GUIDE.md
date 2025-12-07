# 🚀 POINT SYSTEM IMPLEMENTATION GUIDE
**Date:** November 20, 2025  
**Status:** READY TO IMPLEMENT  
**Estimated Time:** 2-3 hours  

---

## 📋 TABLE OF CONTENTS

1. [Prerequisites](#prerequisites)
2. [Step-by-Step Implementation](#step-by-step-implementation)
3. [File Summary](#file-summary)
4. [API Endpoints Reference](#api-endpoints-reference)
5. [Testing Guide](#testing-guide)
6. [Integration Checklist](#integration-checklist)

---

## ✅ PREREQUISITES

**Ensure you have:**
- ✅ Laravel 11 project running
- ✅ PostgreSQL/MySQL configured
- ✅ Artisan CLI access
- ✅ Postman or similar for API testing
- ✅ Access to terminal

**Already Created Files:**
- ✅ `database/migrations/2025_11_20_100000_create_poin_transaksis_table.php`
- ✅ `app/Models/PoinTransaksi.php`
- ✅ `app/Services/PointService.php`
- ✅ `app/Http/Resources/PoinTransaksiResource.php`
- ✅ `app/Http/Resources/UserPointResource.php`
- ✅ `app/Http/Resources/PenukaranProdukResource.php`
- ✅ Modified: `app/Models/User.php` (added poinTransaksis relationship)

---

## 🛠️ STEP-BY-STEP IMPLEMENTATION

### **PHASE 1: Database Migration (5 minutes)**

#### Step 1.1: Run Migration
```bash
php artisan migrate
```

**Expected Output:**
```
Migrating: 2025_11_20_100000_create_poin_transaksis_table
Migrated:  2025_11_20_100000_create_poin_transaksis_table (0.25s)
```

#### Step 1.2: Verify Table Created
```bash
php artisan db:show poin_transaksis
```

**Expected:** Table with columns:
- `id`, `user_id`, `tabung_sampah_id`, `jenis_sampah`, `berat_kg`, `poin_didapat`, `sumber`, `keterangan`, `referensi_id`, `referensi_tipe`, `created_at`, `updated_at`

---

### **PHASE 2: Update Existing Controllers (20-30 minutes)**

#### Step 2.1: Update TabungSampahController - Approve Method

**File:** `app/Http/Controllers/TabungSampahController.php`

**Current Code (OLD):**
```php
public function approve(Request $request, $id)
{
    $tabungSampah = TabungSampah::findOrFail($id);
    
    // OLD LOGIC - DIRECT UPDATE
    $tabungSampah->update(['status' => 'approved']);
    $poinDidapat = $tabungSampah->poin_didapat;
    $user = $tabungSampah->user;
    $user->increment('total_poin', $poinDidapat);
    
    // ... rest of logic
}
```

**New Code (USING PointService):**
```php
<?php

namespace App\Http\Controllers;

use App\Models\TabungSampah;
use App\Services\PointService;
use App\Services\BadgeService;
use Illuminate\Http\Request;

class TabungSampahController extends Controller
{
    protected $badgeService;

    public function __construct(BadgeService $badgeService)
    {
        $this->badgeService = $badgeService;
    }

    /**
     * Approve waste deposit
     * POST /api/tabung-sampah/{id}/approve
     */
    public function approve(Request $request, $id)
    {
        try {
            $tabungSampah = TabungSampah::findOrFail($id);

            // Update status
            $tabungSampah->update(['status' => 'approved']);

            // Use PointService to handle all point logic
            $pointCalculation = PointService::calculatePointsForDeposit($tabungSampah);
            PointService::applyDepositPoints($tabungSampah);

            // Check and award badges
            $newBadges = $this->badgeService->checkAndAwardBadges($tabungSampah->user_id);

            return response()->json([
                'status' => 'success',
                'message' => 'Setor sampah disetujui',
                'data' => [
                    'id' => $tabungSampah->id,
                    'status' => 'approved',
                    'poin_diberikan' => $pointCalculation['total'],
                    'breakdown' => $pointCalculation['breakdown'],
                    'new_badges' => $newBadges,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reject waste deposit
     * POST /api/tabung-sampah/{id}/reject
     */
    public function reject(Request $request, $id)
    {
        try {
            $tabungSampah = TabungSampah::findOrFail($id);
            
            $tabungSampah->update(['status' => 'rejected']);

            return response()->json([
                'status' => 'success',
                'message' => 'Setor sampah ditolak',
                'data' => ['id' => $tabungSampah->id],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ... rest of controller methods (store, index, show, update, destroy unchanged)
}
```

#### Step 2.2: Update PenukaranProdukController - Store Method

**File:** `app/Http/Controllers/PenukaranProdukController.php`

**Find this method:**
```php
public function store(Request $request)
{
    // OLD CODE - CHECK AROUND LINE 143
    $user = User::find($validated['user_id']);
    if ($user->total_poin < $validated['poin_digunakan']) {
        return response()->json(['status' => 'error', 'message' => 'Insufficient points'], 400);
    }
    
    PenukaranProduk::create([...]);
    $user->decrement('total_poin', $validated['poin_digunakan']);
}
```

**Replace with:**
```php
public function store(Request $request)
{
    try {
        $input = $request->all();
        
        if (isset($input['jumlah_poin'])) {
            $totalPoin = (int) $input['jumlah_poin'];
            $jumlah = isset($input['jumlah']) ? $input['jumlah'] : 1;
        } else {
            $jumlah = $input['jumlah'] ?? 1;
            $totalPoin = null;
        }

        $validated = $request->validate([
            'produk_id' => 'required|exists:produks,id',
            'poin_digunakan' => $totalPoin ? 'nullable' : 'required|integer|min:1',
            'metode_ambil' => 'required|in:pickup,delivery',
        ]);

        $user = User::findOrFail($request->user()->id);
        $produk = Produk::findOrFail($validated['produk_id']);

        // Determine points to use
        $poinDigunakan = $totalPoin ?? $produk->poin;

        // Use PointService to handle deduction with validation
        try {
            PointService::deductPointsForRedemption($user, $poinDigunakan);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 400);
        }

        // Create redemption record
        $penukaran = PenukaranProduk::create([
            'user_id' => $user->id,
            'produk_id' => $produk->id,
            'nama_produk' => $produk->nama,
            'poin_digunakan' => $poinDigunakan,
            'jumlah' => $jumlah,
            'status' => 'pending',
            'metode_ambil' => $validated['metode_ambil'],
            'tanggal_penukaran' => now(),
        ]);

        // Create notification
        Notifikasi::create([
            'user_id' => $user->id,
            'judul' => '🎁 Penukaran Produk',
            'pesan' => "Penukaran {$produk->nama} menunggu persetujuan admin",
            'tipe' => 'redemption',
            'related_id' => $penukaran->id,
            'related_type' => 'penukaran_produk',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Penukaran berhasil',
            'data' => [
                'id' => $penukaran->id,
                'remaining_poin' => $user->fresh()->total_poin,
                'status' => 'pending',
            ],
        ], 201);
    } catch (\Exception $e) {
        \Log::error('Error creating redemption:', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat membuat penukaran',
        ], 500);
    }
}
```

**Add imports at top of file:**
```php
use App\Services\PointService;
use App\Models\Notifikasi;
use App\Models\Produk;
use App\Models\User;
use App\Http\Resources\PenukaranProdukResource;
```

---

### **PHASE 3: Create Point API Endpoints (20-30 minutes)**

#### Step 3.1: Create PointController

**Create new file:** `app/Http/Controllers/PointController.php`

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PoinTransaksi;
use App\Services\PointService;
use App\Http\Resources\PoinTransaksiResource;
use Illuminate\Http\Request;

class PointController extends Controller
{
    /**
     * Get user's point summary + recent history
     * GET /api/user/{id}/poin
     * 
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserPoints($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Get recent transactions
            $recentTransactions = PoinTransaksi::where('user_id', $id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                        'total_poin' => $user->total_poin,
                        'level' => $user->level,
                    ],
                    'recent_transactions' => PoinTransaksiResource::collection($recentTransactions),
                    'statistics' => PointService::getStatistics($id),
                ],
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get point transaction history with pagination
     * GET /api/poin/history?page=1&per_page=20&sumber=setor_sampah
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistory(Request $request)
    {
        try {
            $userId = $request->user()?->id;
            if (!$userId) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $perPage = $request->input('per_page', 20);
            $sumber = $request->input('sumber');

            $query = PoinTransaksi::where('user_id', $userId);

            if ($sumber) {
                $query->where('sumber', $sumber);
            }

            $transactions = $query->orderBy('created_at', 'desc')
                ->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => PoinTransaksiResource::collection($transactions->items()),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's redemption history
     * GET /api/user/{id}/redeem-history
     * 
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRedeemHistory($id)
    {
        try {
            $user = User::findOrFail($id);
            
            $redemptions = PoinTransaksi::where('user_id', $id)
                ->where('sumber', 'redemption')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => PoinTransaksiResource::collection($redemptions),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get point statistics for a user
     * GET /api/user/{id}/poin/statistics
     * 
     * @param int $id User ID
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatistics($id)
    {
        try {
            $user = User::findOrFail($id);
            
            $stats = PointService::getStatistics($id);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'nama' => $user->nama,
                    ],
                    'statistics' => $stats,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Award bonus points to a user (Admin only)
     * POST /api/poin/bonus
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function awardBonus(Request $request)
    {
        try {
            // TODO: Add admin middleware check

            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'points' => 'required|integer|min:1',
                'reason' => 'required|string|max:255',
            ]);

            $transaction = PointService::awardBonusPoints(
                $validated['user_id'],
                $validated['points'],
                'manual',
                $validated['reason']
            );

            $user = User::find($validated['user_id']);

            return response()->json([
                'status' => 'success',
                'message' => 'Bonus poin berhasil diberikan',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'points_awarded' => $validated['points'],
                    'new_balance' => $user->fresh()->total_poin,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get point breakdown by source
     * GET /api/poin/breakdown/{userId}
     * 
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBreakdown($userId)
    {
        try {
            $user = User::findOrFail($userId);
            
            $breakdown = [
                'current_balance' => $user->total_poin,
                'earned_from' => [
                    'deposits' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'setor_sampah')
                        ->sum('poin_didapat'),
                    'bonuses' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'bonus')
                        ->sum('poin_didapat'),
                    'badges' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'badge')
                        ->sum('poin_didapat'),
                    'events' => (int) PoinTransaksi::where('user_id', $userId)
                        ->where('sumber', 'event')
                        ->sum('poin_didapat'),
                ],
                'spent_on' => [
                    'redemptions' => (int) abs(
                        PoinTransaksi::where('user_id', $userId)
                            ->where('sumber', 'redemption')
                            ->sum('poin_didapat')
                    ),
                ],
            ];

            return response()->json([
                'status' => 'success',
                'data' => $breakdown,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
```

---

#### Step 3.2: Add Routes

**File:** `routes/api.php`

**Add these routes in the protected group (after line ~100):**

```php
// ========================================
// 💰 POINT SYSTEM ROUTES
// ========================================
Route::get('user/{id}/poin', [PointController::class, 'getUserPoints']);
Route::get('user/{id}/redeem-history', [PointController::class, 'getRedeemHistory']);
Route::get('user/{id}/poin/statistics', [PointController::class, 'getStatistics']);
Route::get('poin/history', [PointController::class, 'getHistory']);
Route::get('poin/breakdown/{userId}', [PointController::class, 'getBreakdown']);

// Admin only
Route::post('poin/bonus', [PointController::class, 'awardBonus']);
```

**Add import at top:**
```php
use App\Http\Controllers\PointController;
```

---

### **PHASE 4: Testing (20 minutes)**

#### Step 4.1: Test Migration

```bash
php artisan migrate:status
```

Verify `poin_transaksis` shows as "Ran"

#### Step 4.2: Test with Postman

##### Test 1: Get User Points

```
GET http://localhost:8000/api/user/1/poin
Content-Type: application/json
```

**Expected Response:**
```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 1,
      "nama": "Adib",
      "total_poin": 320,
      "level": "Bronze"
    },
    "recent_transactions": [
      {
        "id": 1,
        "tanggal": "2025-11-20",
        "waktu": "10:30:45",
        "sumber": "setor_sampah",
        "sumber_label": "Penyetoran Sampah",
        "jenis_sampah": "Plastik",
        "berat_kg": 3.5,
        "poin_didapat": 35,
        "tipe": "earning",
        "keterangan": "Setor 3.5kg Plastik"
      }
    ],
    "statistics": {
      "current_balance": 320,
      "total_earned": 350,
      "total_spent": 30,
      "transaction_count": 5
    }
  }
}
```

##### Test 2: Get Point History

```
GET http://localhost:8000/api/poin/history?page=1&per_page=10
Authorization: Bearer {token}
Content-Type: application/json
```

##### Test 3: Get Redemption History

```
GET http://localhost:8000/api/user/1/redeem-history
Content-Type: application/json
```

##### Test 4: Get Point Breakdown

```
GET http://localhost:8000/api/poin/breakdown/1
Content-Type: application/json
```

---

## 📦 FILE SUMMARY

### **Files Created**
| File | Purpose | Status |
|------|---------|--------|
| `database/migrations/2025_11_20_100000_create_poin_transaksis_table.php` | Point ledger table | ✅ Created |
| `app/Models/PoinTransaksi.php` | Point transaction model | ✅ Created |
| `app/Services/PointService.php` | Centralized point logic | ✅ Created |
| `app/Http/Resources/PoinTransaksiResource.php` | API resource | ✅ Created |
| `app/Http/Resources/UserPointResource.php` | API resource | ✅ Created |
| `app/Http/Resources/PenukaranProdukResource.php` | API resource | ✅ Created |
| `app/Http/Controllers/PointController.php` | Point endpoints | Ready to create |

### **Files Modified**
| File | Change | Status |
|------|--------|--------|
| `app/Models/User.php` | Added poinTransaksis() relationship | ✅ Done |
| `app/Http/Controllers/TabungSampahController.php` | Use PointService in approve() | Ready to update |
| `app/Http/Controllers/PenukaranProdukController.php` | Use PointService in store() | Ready to update |
| `routes/api.php` | Add point endpoint routes | Ready to update |

---

## 🔌 API ENDPOINTS REFERENCE

### **Public Endpoints**

```
GET  /api/user/{id}/poin                    # Get user points + history
GET  /api/user/{id}/redeem-history          # Get redemption history
GET  /api/poin/breakdown/{userId}           # Get point breakdown by source
```

### **Protected Endpoints**

```
GET  /api/poin/history                      # Authenticated user's full history (paginated)
GET  /api/user/{id}/poin/statistics         # Point statistics
POST /api/poin/bonus                        # Award manual bonus (Admin)
```

### **Updated Endpoints**

```
POST /api/tabung-sampah/{id}/approve        # Uses PointService
POST /api/penukaran-produk                  # Uses PointService
```

---

## 🧪 TESTING GUIDE

### **Unit Test Example**

Create `tests/Unit/PointServiceTest.php`:

```php
<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PointService;
use App\Models\TabungSampah;
use App\Models\User;

class PointServiceTest extends TestCase
{
    public function test_calculate_points_for_deposit()
    {
        $user = User::factory()->create();
        $tabung = TabungSampah::factory()->create([
            'user_id' => $user->id,
            'jenis_sampah' => 'Plastik',
            'berat_kg' => 5,
        ]);

        $calculation = PointService::calculatePointsForDeposit($tabung);

        // Plastik = 10 points per kg
        // 5 kg × 10 = 50 base points
        $this->assertEquals(50, $calculation['base']);
        $this->assertIsArray($calculation['bonuses']);
    }

    public function test_record_point_transaction()
    {
        $user = User::factory()->create(['total_poin' => 100]);

        PointService::recordPointTransaction(
            $user->id,
            50,
            'bonus',
            'Test bonus'
        );

        $user->refresh();
        $this->assertEquals(150, $user->total_poin);
    }
}
```

**Run tests:**
```bash
php artisan test tests/Unit/PointServiceTest.php
```

---

## ✅ INTEGRATION CHECKLIST

Use this checklist to verify implementation:

```
PHASE 1: Database
  ☐ Migration created: poin_transaksis
  ☐ Migration ran successfully: php artisan migrate
  ☐ Table has all required columns
  ☐ Indexes created properly
  ☐ Unique constraint applied

PHASE 2: Models
  ☐ PoinTransaksi model created with fillable fields
  ☐ Relationships defined (user, tabungSampah)
  ☐ Query scopes added (deposits, bonuses, etc)
  ☐ User model has poinTransaksis relationship
  ☐ Casts configured for berat_kg and poin_didapat

PHASE 3: Services
  ☐ PointService created with all methods
  ☐ recordPointTransaction works
  ☐ calculatePointsForDeposit returns correct values
  ☐ applyDepositPoints uses transaction wrapper
  ☐ deductPointsForRedemption validates points

PHASE 4: Resources
  ☐ PoinTransaksiResource created and formats correctly
  ☐ UserPointResource created
  ☐ PenukaranProdukResource created
  ☐ All resources hide sensitive fields

PHASE 5: Controllers
  ☐ PointController created with all methods
  ☐ TabungSampahController::approve uses PointService
  ☐ PenukaranProdukController::store uses PointService
  ☐ Error handling for insufficient points
  ☐ Logging in place

PHASE 6: Routes
  ☐ All point routes added to api.php
  ☐ Routes imported correctly
  ☐ Route parameters match controller methods
  ☐ Auth middleware applied where needed

PHASE 7: Testing
  ☐ GET /api/user/{id}/poin returns correctly
  ☐ GET /api/user/{id}/redeem-history returns correctly
  ☐ GET /api/poin/breakdown/{userId} returns correctly
  ☐ Approve deposit creates point transaction
  ☐ Redeem product deducts points
  ☐ Error cases handled (insufficient points, etc)

PHASE 8: Documentation
  ☐ README updated with new endpoints
  ☐ API documentation updated
  ☐ Frontend integration guide created
```

---

## 🎓 BEST PRACTICES APPLIED

1. ✅ **Centralized Point Logic** - All in PointService
2. ✅ **Database Transactions** - Wrapped all multi-step operations
3. ✅ **Audit Trail** - Every point change in poin_transaksis
4. ✅ **Resource Classes** - Clean API responses
5. ✅ **Error Handling** - Validation before deductions
6. ✅ **Logging** - Important operations logged
7. ✅ **Query Scopes** - Easy filtering of transactions
8. ✅ **Type Casting** - Proper data types

---

## 🚀 NEXT: FRONTEND INTEGRATION

After backend is complete, frontend needs:

1. Point dashboard component
2. History list component
3. Redemption form component
4. Point breakdown chart

**Reference:** `FRONTEND_POINT_INTEGRATION_GUIDE.md` (to be created)

---

**End of Implementation Guide**
