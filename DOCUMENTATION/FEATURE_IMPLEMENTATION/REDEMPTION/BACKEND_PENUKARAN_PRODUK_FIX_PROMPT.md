# 🔧 Backend Fix Prompt - Penukaran Produk API

**Priority:** 🔴 **HIGH**  
**Scope:** Product Redemption Endpoints  
**Status:** Needs Testing & Verification  
**Date:** November 19, 2025

---

## 📋 Executive Summary

The **Penukaran Produk (Product Redemption)** API has been updated with new endpoints. However, there are **2 critical issues** preventing full functionality:

| Issue | Endpoint | Status | Impact |
|-------|----------|--------|--------|
| Issue #1 | `GET /api/penukaran-produk` | ❌ 500 Error | Users can't view redemption history |
| Issue #2 | `POST /api/penukaran-produk` | ⚠️ Partial | Points calculation issue |

---

## 🎯 Issue #1: GET Returns 500 Internal Server Error

### Problem
```
GET /api/penukaran-produk
Response: 500 Internal Server Error
Error: "Terjadi kesalahan saat mengambil data penukaran produk"
```

### Root Cause
The endpoint tries to load the `produk` relationship, but the relationship might not be properly defined or there's a null reference when accessing nested properties.

### Solution Checklist

#### ✅ Step 1: Verify Model Relationship
**File:** `app/Models/PenukaranProduk.php`

```php
// This relationship MUST exist and be correct
public function produk()
{
    return $this->belongsTo(Produk::class);
}
```

**Verify:**
- [ ] Relationship is defined in `PenukaranProduk` model
- [ ] Foreign key column `produk_id` exists in `penukaran_produk` table
- [ ] `Produk` model exists in `app/Models/Produk.php`
- [ ] Primary key in `produks` table is `id`

#### ✅ Step 2: Check Controller Implementation
**File:** `app/Http/Controllers/PenukaranProdukController.php`

Controller should use:
```php
public function index(Request $request)
{
    $query = PenukaranProduk::with('produk')  // ← MUST use with()
        ->where('user_id', $request->user()->id)
        ->orderBy('created_at', 'desc');

    $redemptions = $query->get();

    // Transform data...
    return response()->json([
        'status' => 'success',
        'data' => $transformedData
    ], 200);
}
```

**Verify:**
- [ ] Uses `.with('produk')` to eager load relationship
- [ ] Handles null produk gracefully with `?->` operator
- [ ] Returns proper JSON structure with `'status'` key

#### ✅ Step 3: Debug with Database Query
Run this in Laravel Tinker:

```bash
php artisan tinker
```

```php
// Check if data exists
PenukaranProduk::count();  // Should return number of records

// Check specific record
$redemption = PenukaranProduk::first();
$redemption->produk;  // Should return Produk object, not null

// Check relationship
$redemption->produk->nama;  // Should return product name
```

#### ✅ Step 4: Check Database Integrity
```sql
-- In your MySQL client
USE mendaur_api;

-- Check if penukaran_produk table exists
SHOW TABLES LIKE 'penukaran_produk';

-- Check table structure
DESCRIBE penukaran_produk;

-- Check foreign key
SELECT CONSTRAINT_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE TABLE_NAME = 'penukaran_produk' AND COLUMN_NAME = 'produk_id';

-- Check if produks table exists
SHOW TABLES LIKE 'produks';

-- Verify data
SELECT * FROM penukaran_produk LIMIT 5;
SELECT * FROM produks WHERE id IN (
    SELECT DISTINCT produk_id FROM penukaran_produk
);
```

#### ✅ Step 5: Enable Debug Logging
**File:** `.env`

```
APP_DEBUG=true
LOG_LEVEL=debug
```

Then check logs:
```bash
tail -f storage/logs/laravel.log

# Look for the actual error message that caused 500
```

#### ✅ Step 6: Test with cURL

```bash
# Get token first
TOKEN=$(curl -s -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"user@example.com","password":"password"}' \
  | jq -r '.data.token')

# Test GET endpoint
curl -X GET http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"
```

---

## 🎯 Issue #2: POST Partial Implementation

### Problem
```
POST /api/penukaran-produk
Frontend sends: { "produk_id": 1, "jumlah_poin": 500, "alamat_pengiriman": "..." }
Backend expects: { "produk_id": 1, "jumlah": 1, "alamat_pengiriman": "..." }
```

### Root Cause
Frontend sends `jumlah_poin` (total points to use) but backend expects `jumlah` (quantity).

### Solution Status
✅ **Already Fixed in Controller!**

The controller now handles both:
```php
// Accept both jumlah_poin (from frontend) and jumlah (from API)
if (isset($input['jumlah_poin'])) {
    $totalPoin = (int) $input['jumlah_poin'];
    $jumlah = isset($input['jumlah']) ? $input['jumlah'] : 1;
} else {
    // Legacy: calculate from jumlah
    $jumlah = $input['jumlah'] ?? 1;
    $totalPoin = null; // will calculate below
}
```

### Verification Checklist

#### ✅ Step 1: Check Controller Logic
**File:** `app/Http/Controllers/PenukaranProdukController.php` (store method)

Verify:
- [ ] Accepts `jumlah_poin` from frontend
- [ ] Validates `produk_id` exists
- [ ] Checks user has enough points: `$user->poin >= $totalPoin`
- [ ] Checks product has stock: `$produk->stok >= $jumlah`
- [ ] Deducts points: `$user->decrement('poin', $totalPoin)`
- [ ] Reduces stock: `$produk->decrement('stok', $jumlah)`
- [ ] Uses DB transaction for atomicity

#### ✅ Step 2: Check Model Fillable
**File:** `app/Models/PenukaranProduk.php`

```php
protected $fillable = [
    'user_id',
    'produk_id',
    'nama_produk',
    'poin_digunakan',      // ← MUST be fillable
    'jumlah',              // ← MUST be fillable
    'status',
    'alamat_pengiriman',   // ← MUST be fillable
    'no_resi',
    'catatan',
    'tanggal_penukaran',
    'tanggal_pengiriman',
    'tanggal_diterima',
];
```

Verify:
- [ ] All required fields are in `$fillable` array
- [ ] Model is not using `$guarded = ['*']`

#### ✅ Step 3: Test with cURL

```bash
# Create redemption
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "produk_id": 1,
    "jumlah_poin": 100,
    "alamat_pengiriman": "Jl. Test No. 123"
  }'

# Expected response (201):
# {
#   "status": "success",
#   "message": "Penukaran produk berhasil dibuat",
#   "data": { ... }
# }
```

#### ✅ Step 4: Check User Points Deduction
```php
// In Tinker
$user = User::first();
$initialPoints = $user->poin;

// Create redemption
$redemption = PenukaranProduk::create([
    'user_id' => $user->id,
    'produk_id' => 1,
    'nama_produk' => 'Product Name',
    'poin_digunakan' => 100,
    'jumlah' => 1,
    'status' => 'pending',
    'alamat_pengiriman' => 'Test Address'
]);

// Deduct points
$user->decrement('poin', 100);

// Verify
$user->refresh();
echo $user->poin;  // Should be $initialPoints - 100
```

---

## 📊 Data Flow Diagram

```
Frontend sends:
{
  "produk_id": 1,
  "jumlah_poin": 500,  ← Frontend passes total points
  "alamat_pengiriman": "Jl. Test"
}
    ↓
Backend processes:
1. Extract jumlah_poin → totalPoin = 500
2. Get produk → produk.poin = 100 per unit
3. Calculate jumlah = 500 / 100 = 5 units
4. Check: user.poin (500) >= totalPoin (500) ✓
5. Check: produk.stok (10) >= jumlah (5) ✓
6. Create redemption record
7. user.poin -= 500
8. produk.stok -= 5
    ↓
Returns to Frontend:
{
  "status": "success",
  "message": "Penukaran produk berhasil dibuat",
  "data": {
    "id": 1,
    "produk_id": 1,
    "jumlah_poin": 500,  ← Maps to poin_digunakan
    "status": "pending",
    ...
  }
}
```

---

## 🧪 Complete Testing Guide

### Test 1: Create Account & Get Token
```bash
# Register
curl -X POST http://127.0.0.1:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "nama": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123"
  }'

# Login
curl -X POST http://127.0.0.1:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "test@example.com",
    "password": "password123"
  }' | jq '.data.token'  # Save token
```

### Test 2: Get Products
```bash
TOKEN="your_token_here"

curl -X GET http://127.0.0.1:8000/api/produk \
  -H "Accept: application/json" | jq '.data[] | {id, nama, poin, stok}'
```

### Test 3: Get Redemption History (Before)
```bash
curl -X GET http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Expected: empty array or existing records
```

### Test 4: Create Redemption
```bash
curl -X POST http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "produk_id": 1,
    "jumlah_poin": 50,
    "alamat_pengiriman": "Jl. Test No. 123, Jakarta"
  }'

# Expected response:
# {
#   "status": "success",
#   "message": "Penukaran produk berhasil dibuat",
#   "data": { "id": 1, "produk_id": 1, ... }
# }
```

### Test 5: Get Redemption History (After)
```bash
curl -X GET http://127.0.0.1:8000/api/penukaran-produk \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Expected: array with newly created redemption
```

### Test 6: Get Single Redemption
```bash
curl -X GET http://127.0.0.1:8000/api/penukaran-produk/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json"

# Expected: single redemption object with produk details
```

### Test 7: Verify Points Deduction
```bash
curl -X GET http://127.0.0.1:8000/api/profile \
  -H "Authorization: Bearer $TOKEN" \
  -H "Accept: application/json" | jq '.data.poin'

# Expected: original_poin - 50
```

---

## ✅ Verification Checklist

Use this checklist to verify everything is working:

### Database
- [ ] `penukaran_produk` table exists
- [ ] `produk_id` column exists with foreign key to `produks`
- [ ] `poin_digunakan` column exists
- [ ] `jumlah` column exists
- [ ] `status` enum includes: pending, shipped, delivered, cancelled
- [ ] Sample data exists in both tables

### Models
- [ ] `PenukaranProduk::class` has `produk()` relationship
- [ ] `PenukaranProduk::class` has `user()` relationship
- [ ] `$fillable` array includes all required fields
- [ ] Casts are properly defined

### Controller
- [ ] `index()` method loads `produk` relationship with `.with('produk')`
- [ ] `index()` method filters by `user_id`
- [ ] `index()` method transforms data to match frontend format
- [ ] `store()` method validates all inputs
- [ ] `store()` method checks user points
- [ ] `store()` method checks product stock
- [ ] `store()` method uses DB transaction
- [ ] `store()` method decrements user points
- [ ] `store()` method decrements product stock
- [ ] `show()` method loads `produk` relationship

### Routes
- [ ] `GET /api/penukaran-produk` is protected with `auth:sanctum`
- [ ] `GET /api/penukaran-produk/{id}` is protected with `auth:sanctum`
- [ ] `POST /api/penukaran-produk` is protected with `auth:sanctum`
- [ ] Legacy routes `/tukar-produk` still work for backward compatibility

### Response Format
- [ ] Response includes `"status": "success"` key
- [ ] Response includes `"data"` array
- [ ] Each redemption includes `jumlah_poin` field
- [ ] Each redemption includes `produk` object
- [ ] Error responses include proper error messages

---

## 🆘 Common Issues & Solutions

### Issue: "Produk not found in relationship"
**Solution:** 
- Check `produk_id` foreign key is correct
- Verify product exists in `produks` table
- Check `Produk` model exists

### Issue: "Cannot access property on null"
**Solution:**
- Use safe navigation operator: `$redemption->produk?->nama`
- Always check if relationship exists: `if ($redemption->produk) { ... }`

### Issue: "Mass assignment exception"
**Solution:**
- Add all fields to `$fillable` array in model
- Don't use `$guarded = ['*']`

### Issue: "Insufficient points" when user should have enough
**Solution:**
- Check user has `poin` column
- Verify points are being stored as integer
- Check no other process is deducting points

---

## 📞 Questions?

If you encounter issues:

1. **Check Laravel logs:**
   ```bash
   tail -f storage/logs/laravel.log
   ```

2. **Run database checks:**
   ```bash
   mysql> SHOW TABLES;
   mysql> DESCRIBE penukaran_produk;
   mysql> SELECT * FROM penukaran_produk LIMIT 5;
   ```

3. **Use Tinker to debug:**
   ```bash
   php artisan tinker
   > PenukaranProduk::with('produk')->first()
   ```

4. **Test with Postman/cURL** as shown in testing guide above

---

## 🎯 Expected Outcome

Once all fixes are applied:

✅ `GET /api/penukaran-produk` → Returns 200 with user's redemptions  
✅ `POST /api/penukaran-produk` → Returns 201, deducts points & stock  
✅ `GET /api/penukaran-produk/{id}` → Returns 200 with single redemption details  
✅ Frontend can create redemptions and view history  
✅ Points are properly deducted from user account  
✅ Product stock is properly reduced  

---

## 📝 Sign-off

**When completed, please confirm:**
- [ ] All endpoints return correct status codes
- [ ] All test cases pass
- [ ] No errors in Laravel logs
- [ ] Points deduction working correctly
- [ ] Stock reduction working correctly
- [ ] Ready for frontend integration

---

**Document Version:** 1.0  
**Last Updated:** November 19, 2025  
**Status:** Ready for Backend Implementation 🚀
