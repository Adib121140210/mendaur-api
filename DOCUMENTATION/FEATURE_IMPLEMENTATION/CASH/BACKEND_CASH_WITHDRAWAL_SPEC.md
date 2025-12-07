# Backend Specification: Cash Withdrawal API (Tarik Tunai)

## 📋 Overview
Implementation guide for the cash withdrawal feature that allows users to convert their points to cash (Rupiah).

**Conversion Rate:** 100 Points = Rp 1,000  
**Minimum Withdrawal:** 2,000 Points (Rp 20,000)  
**Point Increments:** Must be multiples of 100

---

## 🗄️ Database Schema

### Table: `penarikan_tunai`

```sql
CREATE TABLE penarikan_tunai (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    jumlah_poin INT NOT NULL COMMENT 'Points deducted',
    jumlah_rupiah DECIMAL(15,2) NOT NULL COMMENT 'Cash amount in Rupiah',
    nomor_rekening VARCHAR(50) NOT NULL COMMENT 'Bank account number',
    nama_bank VARCHAR(100) NOT NULL COMMENT 'Bank name',
    nama_penerima VARCHAR(255) NOT NULL COMMENT 'Account holder name',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    catatan_admin TEXT NULL COMMENT 'Admin notes for rejection',
    processed_by BIGINT UNSIGNED NULL COMMENT 'Admin user ID who processed',
    processed_at TIMESTAMP NULL COMMENT 'When approved/rejected',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_general_ci;
```

### Migration Command (Laravel)
```bash
php artisan make:migration create_penarikan_tunai_table
```

---

## 🔌 API Endpoints

### 1. Submit Withdrawal Request

**Endpoint:** `POST /api/penarikan-tunai`

**Authentication:** Required (Bearer Token)

**Request Headers:**
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

**Request Body:**
```json
{
  "user_id": 1,
  "jumlah_poin": 5000,
  "nomor_rekening": "1234567890",
  "nama_bank": "BCA",
  "nama_penerima": "John Doe"
}
```

**Validation Rules:**
```php
[
    'user_id' => 'required|exists:users,id',
    'jumlah_poin' => 'required|integer|min:2000|multiple_of:100',
    'nomor_rekening' => 'required|string|max:50',
    'nama_bank' => 'required|string|max:100',
    'nama_penerima' => 'required|string|max:255'
]
```

**Business Logic:**
1. ✅ Verify user has sufficient points (`users.total_poin >= jumlah_poin`)
2. ✅ Validate minimum withdrawal (>= 2000 points)
3. ✅ Validate increment (must be multiple of 100)
4. ✅ Calculate Rupiah: `jumlah_rupiah = (jumlah_poin / 100) * 1000`
5. ✅ **IMMEDIATELY deduct points** from `users.total_poin` (prevent double spending)
6. ✅ Create withdrawal record with status `pending`
7. ✅ Send notification to user (withdrawal submitted)
8. ✅ Notify admin (new withdrawal request)

**Success Response (201):**
```json
{
  "success": true,
  "message": "Permintaan penarikan tunai berhasil diajukan",
  "data": {
    "id": 1,
    "user_id": 1,
    "jumlah_poin": 5000,
    "jumlah_rupiah": 50000,
    "nomor_rekening": "1234567890",
    "nama_bank": "BCA",
    "nama_penerima": "John Doe",
    "status": "pending",
    "created_at": "2025-11-17T10:30:00.000000Z"
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Poin tidak mencukupi",
  "errors": {
    "jumlah_poin": ["Saldo poin Anda hanya 3000"]
  }
}
```

**Error Response (422):**
```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "jumlah_poin": ["Jumlah poin harus minimal 2000 dan kelipatan 100"]
  }
}
```

---

### 2. Get User Withdrawal History

**Endpoint:** `GET /api/penarikan-tunai`

**Authentication:** Required

**Query Parameters:**
- `status` (optional): `pending`, `approved`, `rejected`
- `page` (optional): Page number (default: 1)
- `per_page` (optional): Items per page (default: 10)

**Request Example:**
```http
GET /api/penarikan-tunai?status=pending&page=1&per_page=10
Authorization: Bearer {token}
```

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "jumlah_poin": 5000,
        "jumlah_rupiah": 50000,
        "nomor_rekening": "1234567890",
        "nama_bank": "BCA",
        "nama_penerima": "John Doe",
        "status": "pending",
        "catatan_admin": null,
        "created_at": "2025-11-17T10:30:00.000000Z",
        "processed_at": null
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

---

### 3. Get Single Withdrawal Detail

**Endpoint:** `GET /api/penarikan-tunai/{id}`

**Authentication:** Required

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "jumlah_poin": 5000,
    "jumlah_rupiah": 50000,
    "nomor_rekening": "1234567890",
    "nama_bank": "BCA",
    "nama_penerima": "John Doe",
    "status": "approved",
    "catatan_admin": "Transfer berhasil",
    "processed_by": 2,
    "processed_at": "2025-11-17T11:00:00.000000Z",
    "created_at": "2025-11-17T10:30:00.000000Z",
    "admin": {
      "id": 2,
      "name": "Admin User"
    }
  }
}
```

---

### 4. Admin: Get All Withdrawal Requests

**Endpoint:** `GET /api/admin/penarikan-tunai`

**Authentication:** Required (Admin only)

**Query Parameters:**
- `status`: `pending`, `approved`, `rejected`
- `user_id`: Filter by user
- `date_from`: Start date (YYYY-MM-DD)
- `date_to`: End date (YYYY-MM-DD)
- `page`, `per_page`

**Success Response (200):**
```json
{
  "success": true,
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "user": {
          "id": 1,
          "name": "John Doe",
          "email": "john@example.com"
        },
        "jumlah_poin": 5000,
        "jumlah_rupiah": 50000,
        "nomor_rekening": "1234567890",
        "nama_bank": "BCA",
        "nama_penerima": "John Doe",
        "status": "pending",
        "created_at": "2025-11-17T10:30:00.000000Z"
      }
    ],
    "per_page": 10,
    "total": 25
  }
}
```

---

### 5. Admin: Approve Withdrawal

**Endpoint:** `POST /api/admin/penarikan-tunai/{id}/approve`

**Authentication:** Required (Admin only)

**Request Body (optional):**
```json
{
  "catatan_admin": "Transfer berhasil dilakukan"
}
```

**Business Logic:**
1. ✅ Check withdrawal status is `pending`
2. ✅ Update status to `approved`
3. ✅ Set `processed_by` to admin user ID
4. ✅ Set `processed_at` to current timestamp
5. ✅ Save admin notes
6. ✅ Send notification to user (withdrawal approved)
7. ✅ **Do NOT return points** (already deducted)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Penarikan tunai berhasil disetujui",
  "data": {
    "id": 1,
    "status": "approved",
    "processed_at": "2025-11-17T11:00:00.000000Z"
  }
}
```

---

### 6. Admin: Reject Withdrawal

**Endpoint:** `POST /api/admin/penarikan-tunai/{id}/reject`

**Authentication:** Required (Admin only)

**Request Body:**
```json
{
  "catatan_admin": "Nomor rekening tidak valid"
}
```

**Validation:**
```php
[
    'catatan_admin' => 'required|string|max:500'
]
```

**Business Logic:**
1. ✅ Check withdrawal status is `pending`
2. ✅ Update status to `rejected`
3. ✅ Set `processed_by` to admin user ID
4. ✅ Set `processed_at` to current timestamp
5. ✅ Save rejection reason
6. ✅ **RETURN points** to `users.total_poin` (refund)
7. ✅ Send notification to user (withdrawal rejected with reason)

**Success Response (200):**
```json
{
  "success": true,
  "message": "Penarikan tunai ditolak dan poin dikembalikan",
  "data": {
    "id": 1,
    "status": "rejected",
    "catatan_admin": "Nomor rekening tidak valid",
    "processed_at": "2025-11-17T11:00:00.000000Z"
  }
}
```

---

## 🔒 Security & Validation

### Point Balance Check (CRITICAL)
```php
// Before creating withdrawal request
$user = User::findOrFail($request->user_id);

if ($user->total_poin < $request->jumlah_poin) {
    return response()->json([
        'success' => false,
        'message' => 'Poin tidak mencukupi',
        'errors' => [
            'jumlah_poin' => ["Saldo poin Anda hanya {$user->total_poin}"]
        ]
    ], 400);
}
```

### Validation Helper (Laravel)
```php
// Add custom validation rule
Validator::extend('multiple_of', function ($attribute, $value, $parameters) {
    return $value % $parameters[0] === 0;
});
```

### Prevent Double Spending
```php
DB::beginTransaction();
try {
    // 1. Deduct points immediately
    $user->decrement('total_poin', $request->jumlah_poin);
    
    // 2. Create withdrawal record
    $withdrawal = PenarikanTunai::create([...]);
    
    DB::commit();
} catch (\Exception $e) {
    DB::rollback();
    throw $e;
}
```

### Authorization (Policy)
```php
// Only allow users to view their own withdrawals
public function view(User $user, PenarikanTunai $withdrawal)
{
    return $user->id === $withdrawal->user_id || $user->isAdmin();
}
```

---

## 📊 Statistics Endpoints (Optional)

### User: Get Withdrawal Summary

**Endpoint:** `GET /api/penarikan-tunai/summary`

**Response:**
```json
{
  "success": true,
  "data": {
    "total_withdrawn_points": 15000,
    "total_withdrawn_rupiah": 150000,
    "pending_count": 1,
    "approved_count": 2,
    "rejected_count": 0
  }
}
```

### Admin: Get Dashboard Statistics

**Endpoint:** `GET /api/admin/penarikan-tunai/statistics`

**Response:**
```json
{
  "success": true,
  "data": {
    "pending": {
      "count": 15,
      "total_points": 75000,
      "total_rupiah": 750000
    },
    "approved_today": {
      "count": 8,
      "total_rupiah": 400000
    },
    "approved_this_month": {
      "count": 120,
      "total_rupiah": 6000000
    }
  }
}
```

---

## 🔔 Notification Events

### 1. Withdrawal Submitted
- **Trigger:** User submits withdrawal request
- **Recipient:** User + Admin
- **User Message:** "Permintaan penarikan Rp 50.000 berhasil diajukan"
- **Admin Message:** "Ada permintaan penarikan tunai baru dari {user}"

### 2. Withdrawal Approved
- **Trigger:** Admin approves request
- **Recipient:** User
- **Message:** "Penarikan Rp 50.000 telah disetujui dan sedang diproses"

### 3. Withdrawal Rejected
- **Trigger:** Admin rejects request
- **Recipient:** User
- **Message:** "Penarikan Rp 50.000 ditolak. Alasan: {catatan_admin}"

---

## 🧪 Testing Checklist

### Unit Tests
- [ ] Validate point conversion calculation
- [ ] Validate minimum withdrawal (2000 points)
- [ ] Validate increment validation (multiple of 100)
- [ ] Test insufficient balance rejection
- [ ] Test point deduction on submission
- [ ] Test point refund on rejection

### Integration Tests
- [ ] Submit withdrawal with valid data
- [ ] Submit withdrawal with insufficient points
- [ ] Submit withdrawal below minimum (1500 points)
- [ ] Submit withdrawal with invalid increment (2550 points)
- [ ] Admin approve withdrawal
- [ ] Admin reject withdrawal (verify point refund)
- [ ] Prevent double approval/rejection
- [ ] Test concurrent withdrawal prevention

### API Tests (Postman/Thunder Client)
```javascript
// Test Case 1: Valid Withdrawal
POST /api/penarikan-tunai
{
  "user_id": 1,
  "jumlah_poin": 5000,
  "nomor_rekening": "1234567890",
  "nama_bank": "BCA",
  "nama_penerima": "John Doe"
}
// Expected: 201 Created, points deducted

// Test Case 2: Insufficient Points
POST /api/penarikan-tunai
{
  "user_id": 1,
  "jumlah_poin": 999999,
  ...
}
// Expected: 400 Bad Request

// Test Case 3: Below Minimum
POST /api/penarikan-tunai
{
  "jumlah_poin": 1500,
  ...
}
// Expected: 422 Validation Error

// Test Case 4: Invalid Increment
POST /api/penarikan-tunai
{
  "jumlah_poin": 2550,
  ...
}
// Expected: 422 Validation Error
```

---

## 📝 Laravel Implementation Example

### Model: `PenarikanTunai.php`
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenarikanTunai extends Model
{
    protected $table = 'penarikan_tunai';
    
    protected $fillable = [
        'user_id',
        'jumlah_poin',
        'jumlah_rupiah',
        'nomor_rekening',
        'nama_bank',
        'nama_penerima',
        'status',
        'catatan_admin',
        'processed_by',
        'processed_at'
    ];
    
    protected $casts = [
        'jumlah_poin' => 'integer',
        'jumlah_rupiah' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function processedBy()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
    
    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
    
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
    
    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
```

### Controller: `PenarikanTunaiController.php`
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PenarikanTunai;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PenarikanTunaiController extends Controller
{
    /**
     * Submit withdrawal request
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'jumlah_poin' => 'required|integer|min:2000',
            'nomor_rekening' => 'required|string|max:50',
            'nama_bank' => 'required|string|max:100',
            'nama_penerima' => 'required|string|max:255'
        ]);
        
        // Validate multiple of 100
        if ($validated['jumlah_poin'] % 100 !== 0) {
            return response()->json([
                'success' => false,
                'message' => 'Jumlah poin harus kelipatan 100',
                'errors' => [
                    'jumlah_poin' => ['Jumlah poin harus kelipatan 100']
                ]
            ], 422);
        }
        
        // Check sufficient balance
        $user = User::findOrFail($validated['user_id']);
        
        if ($user->total_poin < $validated['jumlah_poin']) {
            return response()->json([
                'success' => false,
                'message' => 'Poin tidak mencukupi',
                'errors' => [
                    'jumlah_poin' => ["Saldo poin Anda hanya {$user->total_poin}"]
                ]
            ], 400);
        }
        
        // Calculate Rupiah
        $jumlah_rupiah = ($validated['jumlah_poin'] / 100) * 1000;
        
        DB::beginTransaction();
        try {
            // Deduct points immediately
            $user->decrement('total_poin', $validated['jumlah_poin']);
            
            // Create withdrawal record
            $withdrawal = PenarikanTunai::create([
                'user_id' => $validated['user_id'],
                'jumlah_poin' => $validated['jumlah_poin'],
                'jumlah_rupiah' => $jumlah_rupiah,
                'nomor_rekening' => $validated['nomor_rekening'],
                'nama_bank' => $validated['nama_bank'],
                'nama_penerima' => $validated['nama_penerima'],
                'status' => 'pending'
            ]);
            
            DB::commit();
            
            // TODO: Send notifications
            
            return response()->json([
                'success' => true,
                'message' => 'Permintaan penarikan tunai berhasil diajukan',
                'data' => $withdrawal
            ], 201);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses penarikan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get user withdrawal history
     */
    public function index(Request $request)
    {
        $query = PenarikanTunai::where('user_id', $request->user()->id);
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $withdrawals = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);
        
        return response()->json([
            'success' => true,
            'data' => $withdrawals
        ]);
    }
    
    /**
     * Get single withdrawal detail
     */
    public function show($id)
    {
        $withdrawal = PenarikanTunai::with(['user', 'processedBy'])
            ->findOrFail($id);
        
        // Check authorization
        if (auth()->user()->id !== $withdrawal->user_id && !auth()->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        return response()->json([
            'success' => true,
            'data' => $withdrawal
        ]);
    }
}
```

### Admin Controller: `AdminPenarikanTunaiController.php`
```php
<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PenarikanTunai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPenarikanTunaiController extends Controller
{
    /**
     * Approve withdrawal
     */
    public function approve(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_admin' => 'nullable|string|max:500'
        ]);
        
        $withdrawal = PenarikanTunai::findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan sudah diproses sebelumnya'
            ], 400);
        }
        
        $withdrawal->update([
            'status' => 'approved',
            'catatan_admin' => $validated['catatan_admin'] ?? null,
            'processed_by' => auth()->id(),
            'processed_at' => now()
        ]);
        
        // TODO: Send notification to user
        
        return response()->json([
            'success' => true,
            'message' => 'Penarikan tunai berhasil disetujui',
            'data' => $withdrawal
        ]);
    }
    
    /**
     * Reject withdrawal and refund points
     */
    public function reject(Request $request, $id)
    {
        $validated = $request->validate([
            'catatan_admin' => 'required|string|max:500'
        ]);
        
        $withdrawal = PenarikanTunai::with('user')->findOrFail($id);
        
        if ($withdrawal->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Penarikan sudah diproses sebelumnya'
            ], 400);
        }
        
        DB::beginTransaction();
        try {
            // Refund points
            $withdrawal->user->increment('total_poin', $withdrawal->jumlah_poin);
            
            // Update withdrawal status
            $withdrawal->update([
                'status' => 'rejected',
                'catatan_admin' => $validated['catatan_admin'],
                'processed_by' => auth()->id(),
                'processed_at' => now()
            ]);
            
            DB::commit();
            
            // TODO: Send notification to user
            
            return response()->json([
                'success' => true,
                'message' => 'Penarikan tunai ditolak dan poin dikembalikan',
                'data' => $withdrawal
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
```

### Routes: `api.php`
```php
// User routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/penarikan-tunai', [PenarikanTunaiController::class, 'index']);
    Route::post('/penarikan-tunai', [PenarikanTunaiController::class, 'store']);
    Route::get('/penarikan-tunai/{id}', [PenarikanTunaiController::class, 'show']);
    Route::get('/penarikan-tunai/summary', [PenarikanTunaiController::class, 'summary']);
});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/penarikan-tunai', [AdminPenarikanTunaiController::class, 'index']);
    Route::post('/penarikan-tunai/{id}/approve', [AdminPenarikanTunaiController::class, 'approve']);
    Route::post('/penarikan-tunai/{id}/reject', [AdminPenarikanTunaiController::class, 'reject']);
    Route::get('/penarikan-tunai/statistics', [AdminPenarikanTunaiController::class, 'statistics']);
});
```

---

## 🎯 Frontend Integration Status

✅ **Frontend is READY** - The TukarPoin page is fully implemented with:
- Cash withdrawal modal with form validation
- Real-time point to Rupiah conversion calculator
- User point balance from AuthContext
- API call prepared with proper authentication
- Error handling and user feedback

**Frontend makes this exact API call:**
```javascript
const response = await fetch('http://127.0.0.1:8000/api/penarikan-tunai', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${localStorage.getItem('token')}`
  },
  body: JSON.stringify({
    user_id: user?.id,
    jumlah_poin: withdrawPoints,
    nomor_rekening: bankAccount,
    nama_bank: bankName,
    nama_penerima: accountName
  })
});
```

---

## 📌 Priority Implementation Order

1. **Phase 1 - Core Feature (MVP)**
   - [ ] Create migration and run it
   - [ ] Create `PenarikanTunai` model
   - [ ] Implement POST `/api/penarikan-tunai` (user submit)
   - [ ] Implement GET `/api/penarikan-tunai` (user history)
   - [ ] Test with frontend

2. **Phase 2 - Admin Management**
   - [ ] Create admin middleware/policy
   - [ ] Implement GET `/api/admin/penarikan-tunai` (admin list)
   - [ ] Implement POST `/api/admin/penarikan-tunai/{id}/approve`
   - [ ] Implement POST `/api/admin/penarikan-tunai/{id}/reject`
   - [ ] Test point refund on rejection

3. **Phase 3 - Enhancements**
   - [ ] Add notification system
   - [ ] Create admin dashboard UI
   - [ ] Add statistics endpoints
   - [ ] Implement email notifications
   - [ ] Add audit logging

---

## 🚨 Critical Notes

1. **ALWAYS deduct points immediately** when withdrawal is submitted (not when approved)
2. **MUST refund points** when withdrawal is rejected
3. **Validate minimum 2000 points** and multiples of 100
4. **Use database transactions** to prevent inconsistencies
5. **Implement rate limiting** to prevent abuse
6. **Log all admin actions** for audit trail
7. **Send notifications** for status changes

---

## 📞 Questions for Backend Team

Before starting implementation, please confirm:

1. **Authentication:** Are you using Laravel Sanctum or Passport?
2. **Admin Role:** How is admin role defined? (`users.role` or separate table?)
3. **Notifications:** Do you have notification system in place?
4. **Email:** Should we send email notifications for withdrawals?
5. **Bank List:** Do you want predefined bank list or free text input?
6. **Processing Time:** Any auto-approval logic or always manual?

---

## 📚 Additional Resources

- Frontend Documentation: `TUKAR_POIN_COMPLETE.md`
- API Testing: Use Postman collection (to be created)
- Database Seeds: Create test data for development

**Created:** November 17, 2025  
**Last Updated:** November 17, 2025  
**Version:** 1.0
